import { existsSync } from 'node:fs';
import { rm } from 'node:fs/promises';
import { join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { execOrThrow } from '../lib/exec.js';
import { loadState, saveState, type OnboardingState } from '../lib/state.js';

export async function runPhase5(slug: string): Promise<OnboardingState> {
  const log = (msg: string) => console.log(`  ${msg}`);
  console.log('\nPhase 5: git init at site root\n');

  const state = loadState(slug);
  if (!state) throw new Error(`No state for slug '${slug}'.`);
  if (!state.phase3) throw new Error('Phase 3 not complete (no client GitHub repo).');
  if (!state.phase4) throw new Error('Phase 4 not complete (no local site root).');
  if (state.phase5) {
    log(`Phase 5 already complete (${state.phase5.completedAt}).`);
    return state;
  }

  const cwd = state.phase4.localSiteRoot;
  const sshUrl = state.phase3.repo.sshUrl;

  // Defensive: wipe any stale .git that might be present from a previous run.
  // Local doesn't create one, but a re-attempt of this phase might.
  const dotGit = join(cwd, '.git');
  if (existsSync(dotGit)) {
    log(`Removing stale .git at ${dotGit}`);
    await rm(dotGit, { recursive: true, force: true });
  }

  log(`git init -b main`);
  await execOrThrow('git', ['init', '-b', 'main'], { cwd });

  log(`git remote add origin ${sshUrl}`);
  await execOrThrow('git', ['remote', 'add', 'origin', sshUrl], { cwd });

  state.phase5 = {
    completedAt: new Date().toISOString(),
    gitRoot: cwd,
    originUrl: sshUrl,
  };
  saveState(state);
  console.log('\nPhase 5 complete.');
  return state;
}

const isDirectRun = process.argv[1] === fileURLToPath(import.meta.url);
if (isDirectRun) {
  runPhase5(process.argv[2] || 'georgiaseb').catch((e) => {
    console.error('\nFAILED:', e.message);
    process.exit(1);
  });
}
