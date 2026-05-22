import { fileURLToPath } from 'node:url';
import { exec, execOrThrow } from '../lib/exec.js';
import { loadState, saveState, type OnboardingState } from '../lib/state.js';

const PARENT_REPO_URL = 'git@github.com:classiccity/ccc-wp-theme.git';
const PARENT_REMOTE_NAME = 'upstream-parent';
const PARENT_BRANCH = 'main';
const PARENT_PREFIX = 'wp-content/themes/classic-city-core';

export async function runPhase7(slug: string): Promise<OnboardingState> {
  const log = (msg: string) => console.log(`  ${msg}`);
  console.log('\nPhase 7: parent theme subtree\n');

  const state = loadState(slug);
  if (!state) throw new Error(`No state for slug '${slug}'.`);
  if (!state.phase5) throw new Error('Phase 5 not complete (no git repo).');
  if (!state.phase6) throw new Error('Phase 6 not complete (no .gitignore — required so subtree files don\'t pull in WP cruft).');
  if (state.phase7) {
    log(`Phase 7 already complete (${state.phase7.completedAt}).`);
    return state;
  }

  const cwd = state.phase4!.localSiteRoot;

  // Idempotency: a previous failed Phase 7 might have already added the
  // remote. Check before adding.
  const remotes = (await execOrThrow('git', ['remote'], { cwd })).stdout.split('\n').filter(Boolean);
  if (remotes.includes(PARENT_REMOTE_NAME)) {
    log(`Remote ${PARENT_REMOTE_NAME} already configured — skipping add`);
  } else {
    log(`git remote add ${PARENT_REMOTE_NAME} ${PARENT_REPO_URL}`);
    await execOrThrow('git', ['remote', 'add', PARENT_REMOTE_NAME, PARENT_REPO_URL], { cwd });
  }

  // git subtree add fails on its prerequisite check if HEAD doesn't exist.
  // Phase 6 should have created the initial commit, but verify defensively.
  const hasHead = (await exec('git', ['rev-parse', 'HEAD'], { cwd })).exitCode === 0;
  if (!hasHead) {
    throw new Error(
      'No HEAD commit — `git subtree add` requires at least one commit. ' +
        'Phase 6 was supposed to create one. Re-run Phase 6 first.'
    );
  }

  log(`git subtree add --prefix=${PARENT_PREFIX} ${PARENT_REMOTE_NAME} ${PARENT_BRANCH} --squash`);
  // git subtree fetches first then adds; first run takes a few seconds.
  await execOrThrow('git', [
    'subtree', 'add',
    `--prefix=${PARENT_PREFIX}`,
    PARENT_REMOTE_NAME,
    PARENT_BRANCH,
    '--squash',
  ], { cwd });

  state.phase7 = {
    completedAt: new Date().toISOString(),
    parentRemote: PARENT_REMOTE_NAME,
    parentUrl: PARENT_REPO_URL,
    parentBranch: PARENT_BRANCH,
  };
  saveState(state);
  console.log('\nPhase 7 complete.');
  return state;
}

const isDirectRun = process.argv[1] === fileURLToPath(import.meta.url);
if (isDirectRun) {
  runPhase7(process.argv[2] || 'georgiaseb').catch((e) => {
    console.error('\nFAILED:', e.message);
    process.exit(1);
  });
}
