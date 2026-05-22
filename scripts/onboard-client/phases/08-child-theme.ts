import { existsSync } from 'node:fs';
import { readdir, readFile, writeFile } from 'node:fs/promises';
import { extname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { execOrThrow } from '../lib/exec.js';
import { loadState, saveState, type OnboardingState } from '../lib/state.js';

const TEMPLATE_THEME_PATH =
  '/Users/chris/Local Sites/texbuilt/app/public/wp-content/themes/sg-texbuilt';
const TEMPLATE_SLUG = 'texbuilt';
const TEMPLATE_INSTALL = 'texbuilt1';
const TEMPLATE_CLIENT_NAME = 'TexBuilt';
const TEMPLATE_LOCAL_FOLDER = 'TexBuilt';

const TEXT_EXTENSIONS = new Set([
  '.css', '.scss', '.json', '.php', '.md', '.html', '.js',
  '.txt', '.xml', '.svg', '.yml', '.yaml',
]);

const SKIP_DIRS = new Set(['node_modules', '.git']);

async function walkTextFiles(dir: string): Promise<string[]> {
  const out: string[] = [];
  for (const entry of await readdir(dir, { withFileTypes: true })) {
    const full = join(dir, entry.name);
    if (entry.isDirectory()) {
      if (SKIP_DIRS.has(entry.name)) continue;
      out.push(...(await walkTextFiles(full)));
    } else if (entry.isFile()) {
      const ext = extname(entry.name).toLowerCase();
      if (TEXT_EXTENSIONS.has(ext)) out.push(full);
    }
  }
  return out;
}

export async function runPhase8(slug: string): Promise<OnboardingState> {
  const log = (msg: string) => console.log(`  ${msg}`);
  console.log('\nPhase 8: scaffold child theme\n');

  const state = loadState(slug);
  if (!state) throw new Error(`No state for slug '${slug}'.`);
  if (!state.phase4 || !state.phase5 || !state.phase6 || !state.phase7 || !state.phase7b) {
    throw new Error('Earlier phases not complete (need 4 + 5 + 6 + 7 + 7b).');
  }
  if (state.phase8) {
    log(`Phase 8 already complete (${state.phase8.completedAt}).`);
    return state;
  }

  const cwd = state.phase4.localSiteRoot;
  const childThemeName = `sg-${slug}`;
  const dst = join(cwd, 'wp-content', 'themes', childThemeName);

  if (existsSync(dst)) {
    throw new Error(
      `Child theme dir already exists at ${dst}. Either remove it or restore the phase8 entry in state.`
    );
  }

  log(`Copying ${TEMPLATE_THEME_PATH} → wp-content/themes/${childThemeName}`);
  await execOrThrow('cp', ['-R', TEMPLATE_THEME_PATH, dst]);

  log('Cleaning .DS_Store cruft from child theme...');
  await execOrThrow('find', [dst, '-name', '.DS_Store', '-delete']);

  // Substitute slug + install + brand references. Order matters — most
  // specific patterns first so they don't get clobbered by broader ones.
  const repls: Array<[RegExp, string]> = [
    [new RegExp(`sg-${TEMPLATE_SLUG}`, 'g'), childThemeName],
    [new RegExp(`ccc-wp-theme--${TEMPLATE_SLUG}`, 'g'), `ccc-wp-theme--${slug}`],
    [new RegExp(`${TEMPLATE_INSTALL}\\.wpenginepowered\\.com`, 'g'), `${state.installName}.wpenginepowered.com`],
    [new RegExp(`${TEMPLATE_INSTALL}\\.wpengine\\.com`, 'g'), `${state.installName}.wpengine.com`],
    [new RegExp(`${TEMPLATE_INSTALL}\\.ssh\\.wpengine\\.net`, 'g'), `${state.installName}.ssh.wpengine.net`],
    [new RegExp(`\\b${TEMPLATE_INSTALL}\\b`, 'g'), state.installName],
    [new RegExp(`Local Sites/${TEMPLATE_LOCAL_FOLDER}\\b`, 'g'), `Local Sites/${state.phase4.localFolderName}`],
    [new RegExp(`\\b${TEMPLATE_CLIENT_NAME}\\b`, 'g'), state.clientName],
  ];

  log(`Substituting in text files (${repls.length} patterns)...`);
  const files = await walkTextFiles(dst);
  let changedCount = 0;
  for (const file of files) {
    const content = await readFile(file, 'utf8');
    let newContent = content;
    for (const [pattern, replacement] of repls) {
      newContent = newContent.replace(pattern, replacement);
    }
    if (newContent !== content) {
      await writeFile(file, newContent);
      changedCount++;
    }
  }
  log(`Substituted in ${changedCount} of ${files.length} files`);

  // Stage and commit: .gitignore, mu-plugin, child theme together as the
  // "initial scaffold" commit. The parent-theme subtree was already
  // committed by Phase 7's `git subtree add`.
  log('Staging and committing initial scaffold...');
  await execOrThrow(
    'git',
    [
      'add',
      '.gitignore',
      'wp-content/mu-plugins/ccc-fix-auth-header.php',
      `wp-content/themes/${childThemeName}`,
    ],
    { cwd }
  );

  const message = `Initial scaffold: ${childThemeName} child + .gitignore + mu-plugin`;
  await execOrThrow('git', ['commit', '-m', message], { cwd });

  // Capture the commit SHA we just made.
  const sha = (await execOrThrow('git', ['rev-parse', 'HEAD'], { cwd })).stdout.trim();

  state.phase8 = {
    completedAt: new Date().toISOString(),
    childTheme: childThemeName,
    sourceTemplate: `sg-${TEMPLATE_SLUG}`,
    filesSubstituted: changedCount,
    initialCommit: sha,
  };
  saveState(state);
  log(`Initial commit: ${sha.slice(0, 12)}`);
  console.log('\nPhase 8 complete.');
  return state;
}

const isDirectRun = process.argv[1] === fileURLToPath(import.meta.url);
if (isDirectRun) {
  runPhase8(process.argv[2] || 'georgiaseb').catch((e) => {
    console.error('\nFAILED:', e.message);
    process.exit(1);
  });
}
