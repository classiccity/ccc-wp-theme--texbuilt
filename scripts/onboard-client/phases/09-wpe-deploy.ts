import { fileURLToPath } from 'node:url';
import { exec, execOrThrow, ExecError } from '../lib/exec.js';
import { loadState, saveState, type OnboardingState } from '../lib/state.js';

export async function runPhase9(slug: string): Promise<OnboardingState> {
  const log = (msg: string) => console.log(`  ${msg}`);
  console.log('\nPhase 9: WPE Git Push deploy\n');

  const state = loadState(slug);
  if (!state) throw new Error(`No state for slug '${slug}'.`);
  if (!state.phase8) throw new Error('Phase 8 not complete (need an initial commit to push).');
  if (state.phase9) {
    log(`Phase 9 already complete (${state.phase9.completedAt}).`);
    return state;
  }

  const cwd = state.phase4!.localSiteRoot;
  const install = state.installName;
  const wpeRemoteUrl = `git@git.wpengine.com:production/${install}.git`;

  // Idempotent: a previous failed Phase 9 may have already added the remote.
  const remotes = (await execOrThrow('git', ['remote'], { cwd })).stdout.split('\n').filter(Boolean);
  if (remotes.includes('wpe')) {
    log(`Remote wpe already configured — skipping add`);
  } else {
    log(`git remote add wpe ${wpeRemoteUrl}`);
    await execOrThrow('git', ['remote', 'add', 'wpe', wpeRemoteUrl], { cwd });
  }

  log('git push -u origin main');
  await execOrThrow('git', ['push', '-u', 'origin', 'main'], { cwd });

  log('git push wpe main (1-2 min for first push)...');
  try {
    await execOrThrow('git', ['push', 'wpe', 'main'], { cwd });
  } catch (e) {
    // Match a wide net of Git-Push-key-not-registered failure modes:
    //   - "Permission denied (publickey)" — no key matched at all
    //   - "DENIED by fallthru" — gitolite recognized the key but it's not
    //     authorized for this install (registered under a different one)
    //   - "Could not read from remote repository" — generic gitolite refusal
    if (
      e instanceof ExecError &&
      /Permission denied|publickey|DENIED by fallthru|Could not read from remote/i.test(e.result.stderr)
    ) {
      throw new Error(
        `Push to WPE failed — your SSH key isn't authorized to push to '${install}'.\n` +
          `\n` +
          `Git Push uses PER-INSTALL key registration (separate from SSH Gateway, separate from the WPE API).\n` +
          `Fix:\n` +
          `  1. WPE User Portal → Sites → ${install} → Git Push tab\n` +
          `  2. Paste your public key (~/.ssh/id_ed25519.pub) and fill in dev name + email\n` +
          `  3. Save\n` +
          `  4. Re-run Phase 9: \`npm run phase9\`\n` +
          `\n` +
          `(There is no WPE API endpoint for Git Push key registration — confirmed empirically. Account-level /ssh_keys manages SSH Gateway only.)\n` +
          `\n` +
          `Original error from server:\n${e.result.stderr.trim()}`
      );
    }
    throw e;
  }

  // Verify: the child theme's style.css should be reachable on the WPE temp URL.
  const themeUrl = `https://${install}.wpengine.com/wp-content/themes/sg-${slug}/style.css`;
  log(`Verifying ${themeUrl}...`);
  let status = 0;
  try {
    const res = await fetch(themeUrl);
    status = res.status;
  } catch (e) {
    log(`fetch errored: ${(e as Error).message}`);
  }
  log(`HTTP ${status}`);

  if (status !== 200) {
    throw new Error(
      `Theme style.css not reachable at ${themeUrl} (HTTP ${status}). ` +
        `Push reported success but the file isn't on the server. ` +
        `Possible cause: repo scope is wrong (themes folder vs site root) — see runbook's "Site-root restructure" appendix.`
    );
  }

  state.phase9 = {
    completedAt: new Date().toISOString(),
    wpeRemoteUrl,
    originUrl: state.phase3!.repo.sshUrl,
    themeUrl,
    themeUrlStatus: status,
  };
  saveState(state);
  console.log('\nPhase 9 complete.');
  return state;
}

const isDirectRun = process.argv[1] === fileURLToPath(import.meta.url);
if (isDirectRun) {
  runPhase9(process.argv[2] || 'georgiaseb').catch((e) => {
    console.error('\nFAILED:', e.message);
    process.exit(1);
  });
}
