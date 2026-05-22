import { runPhase1, type Phase1Input } from './phases/01-wpe-install.js';
import { runPhase1b } from './phases/1b-plugins-meta.js';
import { runPhase3 } from './phases/03-github-repo.js';
import { runPhase4 } from './phases/04-local-pull.js';
import { runPhase5 } from './phases/05-git-init.js';
import { runPhase6 } from './phases/06-gitignore.js';
import { runPhase7 } from './phases/07-parent-subtree.js';
import { runPhase7b } from './phases/07b-mu-plugins.js';
import { runPhase8 } from './phases/08-child-theme.js';
import { runPhase9 } from './phases/09-wpe-deploy.js';
import { WpeApiError } from './lib/wpe-api.js';

// For now, the script's "config" is a hardcoded constant. Future iteration:
// move to a per-client JSON file (e.g. clients/{slug}.json) or inquirer prompts.
const CONFIG: Phase1Input = {
  clientName: 'Publicom Inc.',
  slug: 'publicom',
  installName: 'publicom',
  accountName: 'classiccity',
  environment: 'production',
};

async function main() {
  console.log('\n══════════════════════════════════════════════════════════════');
  console.log(` New client onboarding: ${CONFIG.clientName}`);
  console.log(`   slug=${CONFIG.slug}  install=${CONFIG.installName}  env=${CONFIG.environment}`);
  console.log('══════════════════════════════════════════════════════════════');

  await runPhase1(CONFIG);
  await runPhase1b(CONFIG.slug);
  await runPhase3(CONFIG.slug);
  await runPhase4(CONFIG.slug);
  await runPhase5(CONFIG.slug);
  await runPhase6(CONFIG.slug);
  await runPhase7(CONFIG.slug);
  await runPhase7b(CONFIG.slug);
  await runPhase8(CONFIG.slug);
  await runPhase9(CONFIG.slug);

  console.log('\nNext: Phase 10 (activate child theme via wp-cli) — not yet implemented.');
}

main().catch((e) => {
  console.error('\nFAILED:', e.message);
  if (e instanceof WpeApiError) {
    console.error('Body:', JSON.stringify(e.body, null, 2));
  }
  process.exit(1);
});
