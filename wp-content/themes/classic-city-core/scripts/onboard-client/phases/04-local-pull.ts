import { existsSync } from 'node:fs';
import { homedir } from 'node:os';
import { join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { loadState, saveState, type OnboardingState } from '../lib/state.js';

/**
 * Lowercase, dash-separated, no leading/trailing dashes. Mirrors how Local by
 * Flywheel turns a WPE Site name into a local folder name.
 */
export function kebabCase(s: string): string {
  return s
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

/**
 * Phase 4 is "manual" — the user has to click "Pull from WP Engine" in
 * the Local desktop app. This module verifies the result and records the
 * resolved path so downstream phases know where to cd. If the path doesn't
 * exist yet, it errors with a clear message.
 *
 * The expected folder is `~/Local Sites/{kebab(clientName)}`. If your Local
 * site name differs from the default, pass `localFolderName` as the second
 * arg.
 */
export async function runPhase4(slug: string, localFolderName?: string): Promise<OnboardingState> {
  const log = (msg: string) => console.log(`  ${msg}`);
  console.log('\nPhase 4: Verify Local pull from WPE\n');

  const state = loadState(slug);
  if (!state) throw new Error(`No state for slug '${slug}'. Run Phase 1 first.`);

  if (state.phase4) {
    log(`Phase 4 already recorded → ${state.phase4.localSiteRoot}`);
    return state;
  }

  const folder = localFolderName ?? kebabCase(state.clientName);
  const localPath = join(homedir(), 'Local Sites', folder);
  const localSiteRoot = join(localPath, 'app', 'public');

  log(`Expected local folder: ${folder}`);
  log(`Looking for: ${localSiteRoot}`);

  if (!existsSync(localPath)) {
    throw new Error(
      `Local site directory not found at ${localPath}.\n` +
        `In the Local desktop app: Connect to WP Engine → Pull from WP Engine → ` +
        `pick install '${state.installName}' → wait for pull to finish.\n` +
        `If your Local site folder name isn't '${folder}', re-run with the actual folder name as the second arg: ` +
        `\`tsx phases/04-local-pull.ts ${slug} <folder-name>\`.`
    );
  }
  if (!existsSync(localSiteRoot)) {
    throw new Error(
      `Local folder ${localPath} exists but ${localSiteRoot} does not. ` +
        `The pull may have failed mid-way — check Local for errors.`
    );
  }
  if (!existsSync(join(localSiteRoot, 'wp-config.php'))) {
    throw new Error(
      `Site root ${localSiteRoot} exists but doesn't look like a WordPress install ` +
        `(no wp-config.php). Did Local pull the right install?`
    );
  }

  state.phase4 = {
    completedAt: new Date().toISOString(),
    localPath,
    localSiteRoot,
    localFolderName: folder,
  };
  saveState(state);
  log(`Local site verified at ${localSiteRoot}`);
  console.log('\nPhase 4 complete.');
  return state;
}

const isDirectRun = process.argv[1] === fileURLToPath(import.meta.url);
if (isDirectRun) {
  const slug = process.argv[2] || 'georgiaseb';
  const folder = process.argv[3]; // optional override
  runPhase4(slug, folder).catch((e) => {
    console.error('\nFAILED:', e.message);
    process.exit(1);
  });
}
