import { fileURLToPath } from 'node:url';
import { ensureGhAuth, findRepo, createRepo } from '../lib/gh.js';
import { loadState, saveState, type OnboardingState } from '../lib/state.js';

const REPO_OWNER = 'classiccity';

export async function runPhase3(slug: string): Promise<OnboardingState> {
  const log = (msg: string) => console.log(`  ${msg}`);
  console.log('\nPhase 3: Create client GitHub repo\n');

  const state = loadState(slug);
  if (!state) throw new Error(`No state for slug '${slug}'. Run Phase 1 first.`);

  if (state.phase3) {
    log(`Phase 3 already complete (${state.phase3.completedAt}).`);
    log(`Repo: ${state.phase3.repo.url}`);
    return state;
  }

  // Naming convention: `ccc-wp-theme--{slug}` (double hyphen — sorts adjacent
  // to the parent `ccc-wp-theme` repo in alphabetical listings).
  const repoName = `ccc-wp-theme--${slug}`;
  const nameWithOwner = `${REPO_OWNER}/${repoName}`;

  log('Verifying gh CLI auth...');
  await ensureGhAuth();

  log(`Checking if ${nameWithOwner} already exists...`);
  const existing = await findRepo(nameWithOwner);
  let repo;
  let createdNow: boolean;

  if (existing) {
    log(`Repo already exists → ${existing.url} (${existing.isPrivate ? 'private' : 'public'})`);
    repo = existing;
    createdNow = false;
  } else {
    log(`Creating ${nameWithOwner} (private)...`);
    repo = await createRepo({
      nameWithOwner,
      isPrivate: true,
      description: `Classic City client site repo for ${state.clientName}`,
    });
    createdNow = true;
    log(`Created → ${repo.url}`);
  }

  log(`SSH URL (for Phase 5 'git remote add origin'): ${repo.sshUrl}`);

  state.phase3 = {
    completedAt: new Date().toISOString(),
    repo: {
      nameWithOwner: repo.nameWithOwner,
      url: repo.url,
      sshUrl: repo.sshUrl,
      isPrivate: repo.isPrivate,
      created_now: createdNow,
    },
  };
  saveState(state);
  log(`State updated → state/${slug}.json`);
  console.log('\nPhase 3 complete.');
  return state;
}

const isDirectRun = process.argv[1] === fileURLToPath(import.meta.url);
if (isDirectRun) {
  const slug = process.argv[2] || 'georgiaseb';
  runPhase3(slug).catch((e) => {
    console.error('\nFAILED:', e.message);
    process.exit(1);
  });
}
