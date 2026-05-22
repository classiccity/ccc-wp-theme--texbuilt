import { mkdir, copyFile } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import { join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { loadState, saveState, type OnboardingState } from '../lib/state.js';

// Source: TexBuilt's repo currently holds the canonical ccc-fix-auth-header.php.
// Future cleanup: factor out to a shared `ccc-mu-plugins` repo and pull from there.
const MU_PLUGIN_SOURCES: Record<string, string> = {
  'ccc-fix-auth-header.php':
    '/Users/chris/Local Sites/texbuilt/app/public/wp-content/mu-plugins/ccc-fix-auth-header.php',
};

export async function runPhase7b(slug: string): Promise<OnboardingState> {
  const log = (msg: string) => console.log(`  ${msg}`);
  console.log('\nPhase 7b: copy mu-plugins\n');

  const state = loadState(slug);
  if (!state) throw new Error(`No state for slug '${slug}'.`);
  if (!state.phase4) throw new Error('Phase 4 not complete.');
  if (state.phase7b) {
    log(`Phase 7b already complete (${state.phase7b.completedAt}).`);
    return state;
  }

  const muDir = join(state.phase4.localSiteRoot, 'wp-content', 'mu-plugins');
  await mkdir(muDir, { recursive: true });

  const copied: string[] = [];
  for (const [name, src] of Object.entries(MU_PLUGIN_SOURCES)) {
    if (!existsSync(src)) {
      throw new Error(`mu-plugin source missing: ${src}`);
    }
    const dst = join(muDir, name);
    log(`Copying ${name} from ${src}`);
    await copyFile(src, dst);
    copied.push(name);
  }

  state.phase7b = {
    completedAt: new Date().toISOString(),
    copiedFiles: copied,
  };
  saveState(state);
  console.log('\nPhase 7b complete.');
  return state;
}

const isDirectRun = process.argv[1] === fileURLToPath(import.meta.url);
if (isDirectRun) {
  runPhase7b(process.argv[2] || 'georgiaseb').catch((e) => {
    console.error('\nFAILED:', e.message);
    process.exit(1);
  });
}
