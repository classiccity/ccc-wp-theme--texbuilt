import { existsSync } from 'node:fs';
import { writeFile } from 'node:fs/promises';
import { join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { exec, execOrThrow } from '../lib/exec.js';
import { loadState, saveState, type OnboardingState } from '../lib/state.js';

function gitignoreContent(slug: string): string {
  return `# ============================================================
# Whitelist-style .gitignore: ignore everything by default,
# then explicitly un-ignore what we version.
#
# Repo root is the WordPress site root. WP Engine's Git Push
# deploys the repo contents to /sites/<install>/src/, so this
# layout must mirror the live site's layout.
#
# Currently versioned:
#   - wp-content/themes/classic-city-core  (git subtree)
#   - wp-content/themes/sg-${slug}          (child theme)
#   - wp-content/mu-plugins/ccc-fix-auth-header.php
#
# Explicitly NOT versioned (live on WPE, managed elsewhere):
#   - WordPress core (wp-admin/, wp-includes/, wp-*.php)
#   - wp-config.php (install-specific secrets)
#   - wp-content/uploads (user media, managed in admin)
#   - wp-content/plugins (managed in wp-admin)
#   - WPE-supplied mu-plugins
#   - Any cache, object-cache, db.php drop-ins
# ============================================================

# Ignore everything at every level.
/*

# Un-ignore repo metadata.
!/.gitignore
!/.cache-bust-timestamp

# Un-ignore the path down to our versioned theme folders.
!/wp-content/
/wp-content/*
!/wp-content/themes/
/wp-content/themes/*
!/wp-content/themes/classic-city-core
!/wp-content/themes/sg-${slug}

# Un-ignore our custom mu-plugins. Don't un-ignore the whole mu-plugins
# directory — WP Engine drops its own infrastructure files in there
# that we don't want to version.
!/wp-content/mu-plugins/
/wp-content/mu-plugins/*
!/wp-content/mu-plugins/ccc-fix-auth-header.php

# macOS cruft should never be tracked, even inside whitelisted folders.
**/.DS_Store
**/._*

# Inside the classic-city-core parent theme: npm artifacts.
# package.json IS tracked (captures the FA Pro kit dependency for
# reproducibility; \`npm install && npm run fa:sync\` rebuilds the
# assets/fontawesome/ folder from the kit). Everything else is
# generated and shouldn't be versioned — including .npmrc which
# carries the FA Pro auth token.
/wp-content/themes/classic-city-core/node_modules
/wp-content/themes/classic-city-core/package-lock.json
/wp-content/themes/classic-city-core/.npmrc
`;
}

export async function runPhase6(slug: string): Promise<OnboardingState> {
  const log = (msg: string) => console.log(`  ${msg}`);
  console.log('\nPhase 6: write whitelist .gitignore + initial commit\n');

  const state = loadState(slug);
  if (!state) throw new Error(`No state for slug '${slug}'.`);
  if (!state.phase4) throw new Error('Phase 4 not complete.');
  if (!state.phase5) throw new Error('Phase 5 not complete (no git repo).');

  const cwd = state.phase4.localSiteRoot;
  const path = join(cwd, '.gitignore');

  // Self-heal: skip only if BOTH the .gitignore exists and a HEAD commit
  // exists. State alone isn't enough — a partial earlier run might have
  // saved state.phase6 without having committed.
  const hasGitignore = existsSync(path);
  const hasHead = (await exec('git', ['rev-parse', 'HEAD'], { cwd })).exitCode === 0;
  if (state.phase6 && hasGitignore && hasHead) {
    log(`Phase 6 already complete (${state.phase6.completedAt}).`);
    return state;
  }

  if (!hasGitignore) {
    log(`Writing ${path}`);
    await writeFile(path, gitignoreContent(slug));
  } else {
    log(`.gitignore already present`);
  }

  if (!hasHead) {
    // Initial commit. `git subtree add` (Phase 7) requires at least one
    // commit to exist — without HEAD it errors out on its prerequisite check.
    log('Staging .gitignore and creating initial commit...');
    await execOrThrow('git', ['add', '.gitignore'], { cwd });
    await execOrThrow('git', ['commit', '-m', 'Initial commit: whitelist .gitignore'], { cwd });
  } else {
    log(`HEAD already exists — skipping initial commit`);
  }

  state.phase6 = {
    completedAt: new Date().toISOString(),
    gitignorePath: path,
  };
  saveState(state);
  console.log('\nPhase 6 complete.');
  return state;
}

const isDirectRun = process.argv[1] === fileURLToPath(import.meta.url);
if (isDirectRun) {
  runPhase6(process.argv[2] || 'georgiaseb').catch((e) => {
    console.error('\nFAILED:', e.message);
    process.exit(1);
  });
}
