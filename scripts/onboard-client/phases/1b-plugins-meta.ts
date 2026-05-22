import { readFileSync, existsSync, readdirSync } from 'node:fs';
import { homedir } from 'node:os';
import { join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { runScript, uploadFile, bashSingleQuote } from '../lib/ssh.js';
import { loadState, saveState, type OnboardingState } from '../lib/state.js';

const PLUGINS_DIR = join(homedir(), 'Downloads', 'default-plugins');

interface PluginPaths {
  acf: string;
  gravityForms: string;
}

interface LicenseKeys {
  acf: string;
  gravityForms: string;
}

function findPluginPaths(): PluginPaths {
  if (!existsSync(PLUGINS_DIR)) {
    throw new Error(`Plugin stash directory not found: ${PLUGINS_DIR}`);
  }
  const files = readdirSync(PLUGINS_DIR);
  const acf = files.find((f) => /^advanced-custom-fields-pro.*\.zip$/i.test(f));
  const gravity = files.find((f) => /^gravityforms.*\.zip$/i.test(f));
  if (!acf) throw new Error(`No ACF Pro zip in ${PLUGINS_DIR} (expected advanced-custom-fields-pro*.zip)`);
  if (!gravity) throw new Error(`No Gravity Forms zip in ${PLUGINS_DIR} (expected gravityforms*.zip)`);
  return {
    acf: join(PLUGINS_DIR, acf),
    gravityForms: join(PLUGINS_DIR, gravity),
  };
}

function readLicenseKeys(): LicenseKeys {
  const path = join(PLUGINS_DIR, 'keys.txt');
  if (!existsSync(path)) throw new Error(`Missing keys.txt at ${path}`);
  const text = readFileSync(path, 'utf8');

  const acfMatch = text.match(/^ACF:\s*(\S.*?)\s*$/m);
  const gfMatch = text.match(/^Gravity Forms:\s*(\S.*?)\s*$/m);

  if (!acfMatch) throw new Error('keys.txt missing ACF line (expected "ACF: <key>")');
  if (!gfMatch) throw new Error('keys.txt missing Gravity Forms line (expected "Gravity Forms: <key>")');

  return { acf: acfMatch[1], gravityForms: gfMatch[1] };
}

export async function runPhase1b(slug: string): Promise<OnboardingState> {
  const log = (msg: string) => console.log(`  ${msg}`);
  console.log('\nPhase 1b: Default plugins + site meta\n');

  const state = loadState(slug);
  if (!state) throw new Error(`No state for slug '${slug}'. Run Phase 1 first.`);
  if (!state.phase1) throw new Error(`Phase 1 not complete for '${slug}'. Run Phase 1 first.`);

  if (state.phase1b) {
    log(`Phase 1b already complete (${state.phase1b.completedAt}). Skipping.`);
    log(`To re-run, edit state/${slug}.json and remove the "phase1b" key.`);
    return state;
  }

  const install = state.installName;
  const remoteSiteDir = `/home/wpe-user/sites/${install}`;
  const clientName = state.clientName;

  log(`Reading plugin zips from ${PLUGINS_DIR}...`);
  const plugins = findPluginPaths();
  log(`  ACF Pro: ${plugins.acf.split('/').pop()}`);
  log(`  Gravity Forms: ${plugins.gravityForms.split('/').pop()}`);

  log('Reading license keys...');
  const keys = readLicenseKeys();
  log(`  ACF: ${keys.acf.length} chars, Gravity Forms: ${keys.gravityForms.length} chars`);

  log(`Uploading ACF Pro zip → ${install}:${remoteSiteDir}/acf-pro.zip ...`);
  await uploadFile(install, plugins.acf, `${remoteSiteDir}/acf-pro.zip`);
  log(`Uploading Gravity Forms zip → ${install}:${remoteSiteDir}/gravityforms.zip ...`);
  await uploadFile(install, plugins.gravityForms, `${remoteSiteDir}/gravityforms.zip`);

  log('Running wp-cli installs + meta over SSH...');
  const description = `A website for ${clientName}`;
  const script = `
set -eo pipefail
cd ${bashSingleQuote(remoteSiteDir)}

echo "[install] ACF Pro"
wp plugin install ./acf-pro.zip --activate --force

echo "[install] Gravity Forms"
wp plugin install ./gravityforms.zip --activate --force

echo "[install] Yoast SEO (from wp.org)"
wp plugin install wordpress-seo --activate

echo "[license] ACF Pro"
wp option update acf_pro_license ${bashSingleQuote(keys.acf)} >/dev/null

echo "[license] Gravity Forms"
wp option update rg_gforms_key ${bashSingleQuote(keys.gravityForms)} >/dev/null

echo "[meta] blogname"
wp option update blogname ${bashSingleQuote(clientName)} >/dev/null

echo "[meta] blogdescription"
wp option update blogdescription ${bashSingleQuote(description)} >/dev/null

echo "[meta] permalink structure"
wp rewrite structure '/%postname%/' >/dev/null
wp rewrite flush >/dev/null

echo "[cleanup] genesis-blocks (if present)"
if wp plugin is-installed genesis-blocks 2>/dev/null; then
  wp plugin deactivate genesis-blocks 2>/dev/null || true
  wp plugin delete genesis-blocks
fi

echo "[cleanup] akismet (if present)"
if wp plugin is-installed akismet 2>/dev/null; then
  wp plugin deactivate akismet 2>/dev/null || true
  wp plugin delete akismet
fi

echo "[cleanup] uploaded zips"
rm -f acf-pro.zip gravityforms.zip

echo "DONE_PHASE_1B"
`.trim();

  const result = await runScript(install, script);
  if (result.exitCode !== 0 || !result.stdout.includes('DONE_PHASE_1B')) {
    throw new Error(
      `Phase 1b script failed (exit ${result.exitCode}).\n` +
        `--- stdout ---\n${result.stdout}\n` +
        `--- stderr ---\n${result.stderr}`
    );
  }
  // Echo the script's labelled steps for visibility (drop wp-cli's verbose noise)
  result.stdout
    .split('\n')
    .filter((l) => l.trim().startsWith('[') || l.includes('DONE_PHASE_1B'))
    .forEach((l) => log(`  ${l.trim()}`));

  log('Verifying...');
  const verifyScript = `
cd ${bashSingleQuote(remoteSiteDir)}
echo "=== ACTIVE PLUGINS ==="
wp plugin list --status=active --fields=name,version --format=table
echo ""
echo "blogname: $(wp option get blogname)"
echo "blogdescription: $(wp option get blogdescription)"
echo "permalink_structure: $(wp option get permalink_structure)"
echo ""
echo "=== INSTALLED PLUGINS ALL ==="
wp plugin list --fields=name,status,version --format=csv
`.trim();

  const verify = await runScript(install, verifyScript);
  if (verify.exitCode !== 0) {
    throw new Error(`Verify failed (exit ${verify.exitCode}): ${verify.stderr}`);
  }
  console.log('');
  verify.stdout.split('\n').forEach((l) => console.log(`    ${l}`));

  // Sanity-check the three target plugins are active and the dropped defaults are gone.
  const expectedActive = ['advanced-custom-fields-pro', 'gravityforms', 'wordpress-seo'];
  const missing = expectedActive.filter((p) => !verify.stdout.includes(p));
  if (missing.length) {
    throw new Error(`Expected active plugins not found in verification output: ${missing.join(', ')}`);
  }
  const removedShouldBeGone = ['genesis-blocks', 'akismet'];
  const stillPresent = removedShouldBeGone.filter((p) =>
    new RegExp(`^${p},`, 'm').test(verify.stdout)
  );
  if (stillPresent.length) {
    log(`Note: ${stillPresent.join(', ')} still present (probably never installed on this WPE template).`);
  }

  state.phase1b = {
    completedAt: new Date().toISOString(),
    plugins: {
      acf_pro: { installed: true, licensed: true },
      gravity_forms: { installed: true, licensed: true },
      wordpress_seo: { installed: true },
    },
    siteMeta: {
      blogname: clientName,
      blogdescription: description,
      permalinkStructure: '/%postname%/',
    },
    removedDefaults: removedShouldBeGone.filter((p) => !stillPresent.includes(p)),
  };
  saveState(state);
  log(`State updated → state/${slug}.json`);
  console.log('\nPhase 1b complete.');
  return state;
}

// Compare resolved filesystem paths (not URL strings) so this works on paths
// containing spaces — `import.meta.url` URL-encodes spaces, `process.argv[1]`
// does not.
const isDirectRun = process.argv[1] === fileURLToPath(import.meta.url);
if (isDirectRun) {
  const slug = process.argv[2] || 'georgiaseb';
  runPhase1b(slug).catch((e) => {
    console.error('\nFAILED:', e.message);
    process.exit(1);
  });
}
