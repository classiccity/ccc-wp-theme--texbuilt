import { fileURLToPath } from 'node:url';
import { wpe, WpeApiError } from '../lib/wpe-api.js';
import { findAccountByName } from '../lib/accounts.js';
import { findOrCreateSite } from '../lib/sites.js';
import { loadState, saveState, type OnboardingState } from '../lib/state.js';

export interface Install {
  id: string;
  name: string;
  account: { id: string };
  site: { id: string };
  status?: string;
  environment: 'production' | 'staging' | 'development';
  primary_domain?: string;
  cname?: string;
  php_version?: string;
}

export interface Phase1Input {
  slug: string;
  clientName: string;
  installName: string;
  accountName: string;
  environment: 'production' | 'staging' | 'development';
}

export async function createInstall(opts: {
  name: string;
  siteId: string;
  accountId: string;
  environment: 'production' | 'staging' | 'development';
}): Promise<Install> {
  // WPE requires BOTH site_id and account_id even though site_id implies the
  // account. Sending only one returns 400 with the missing-field error.
  return wpe<Install>('POST', '/installs', {
    name: opts.name,
    site_id: opts.siteId,
    account_id: opts.accountId,
    environment: opts.environment,
  });
}

export async function getInstall(id: string): Promise<Install | null> {
  try {
    return await wpe<Install>('GET', `/installs/${id}`);
  } catch (e) {
    if (e instanceof WpeApiError && e.status === 404) return null;
    throw e;
  }
}

export async function pollInstallReady(
  id: string,
  opts: {
    intervalMs?: number;
    maxAttempts?: number;
    onPoll?: (attempt: number, install: Install | null) => void;
  } = {}
): Promise<Install> {
  const intervalMs = opts.intervalMs ?? 15000;
  const maxAttempts = opts.maxAttempts ?? 40; // 40 × 15s = 10 min

  for (let attempt = 1; attempt <= maxAttempts; attempt++) {
    const install = await getInstall(id);
    opts.onPoll?.(attempt, install);

    // Ready when status === 'active' (the canonical signal seen on existing
    // installs like texbuilt1).
    if (install && install.status === 'active') {
      return install;
    }

    if (attempt < maxAttempts) {
      await new Promise((r) => setTimeout(r, intervalMs));
    }
  }

  throw new Error(`Install ${id} not ready after ${maxAttempts} polls (~${(maxAttempts * intervalMs) / 60000}min)`);
}

export async function verifyTempUrl(install: Install): Promise<{ url: string; status: number }> {
  const host = install.primary_domain || `${install.name}.wpengine.com`;
  const url = `https://${host}`;
  try {
    const res = await fetch(url, { method: 'GET', redirect: 'follow' });
    return { url, status: res.status };
  } catch (e) {
    return { url, status: 0 };
  }
}

export async function runPhase1(input: Phase1Input): Promise<OnboardingState> {
  const log = (msg: string) => console.log(`  ${msg}`);
  console.log('\nPhase 1: WP Engine site + install\n');

  // Idempotency: if state file already records phase1, refuse to re-run.
  const existing = loadState(input.slug);
  if (existing?.phase1) {
    log(`State for '${input.slug}' already records Phase 1 complete → ${existing.phase1.tempUrl}`);
    log(`Delete state/${input.slug}.json to re-run.`);
    return existing;
  }

  log(`Resolving account '${input.accountName}'...`);
  const account = await findAccountByName(input.accountName);
  log(`Account ${account.name} → ${account.id}`);

  log(`Finding/creating site '${input.clientName}'...`);
  const { site, created: siteCreated } = await findOrCreateSite({
    name: input.clientName,
    accountId: account.id,
  });
  log(`${siteCreated ? 'Created' : 'Reusing existing'} site → ${site.id}`);

  // If site has an install with our target name already, error — caller should
  // either delete it or pick a different name.
  const collision = site.installs?.find((i) => i.name === input.installName);
  if (collision) {
    throw new Error(
      `Site '${site.name}' already has an install named '${input.installName}' (id=${collision.id}, env=${collision.environment}). ` +
        `Either delete it via the WPE portal or choose a different install name.`
    );
  }

  log(`Creating install '${input.installName}' (${input.environment})...`);
  const created = await createInstall({
    name: input.installName,
    siteId: site.id,
    accountId: account.id,
    environment: input.environment,
  });
  log(`Created. id=${created.id}, status=${created.status ?? '(provisioning)'}`);

  log('Polling for ready (max 10 min)...');
  const ready = await pollInstallReady(created.id, {
    onPoll: (attempt, inst) => {
      const status = inst?.status ?? 'pending';
      const cname = inst?.cname ?? '-';
      log(`  [${attempt}] status=${status}, cname=${cname}`);
    },
  });
  log(`Install ready. cname=${ready.cname}, primary_domain=${ready.primary_domain}, php=${ready.php_version}`);

  log('Verifying temp URL...');
  const verify = await verifyTempUrl(ready);
  log(`${verify.url} → HTTP ${verify.status}`);

  const state: OnboardingState = {
    slug: input.slug,
    clientName: input.clientName,
    installName: input.installName,
    environment: input.environment,
    accountName: input.accountName,
    startedAt: existing?.startedAt ?? new Date().toISOString(),
    phase1: {
      completedAt: new Date().toISOString(),
      site: {
        id: site.id,
        name: site.name,
        account_id: site.account.id,
        created_now: siteCreated,
      },
      install: {
        id: ready.id,
        name: ready.name,
        site_id: ready.site.id,
        account_id: ready.account.id,
        environment: ready.environment,
        status: ready.status,
        primary_domain: ready.primary_domain,
        cname: ready.cname,
        php_version: ready.php_version,
      },
      tempUrl: verify.url,
      tempUrlStatus: verify.status,
    },
  };
  saveState(state);
  log(`State saved → state/${input.slug}.json`);
  console.log('\nPhase 1 complete.');
  return state;
}

// Allow direct invocation: `tsx phases/01-wpe-install.ts`
// (Compare resolved paths, not URL strings — `import.meta.url` URL-encodes
//  spaces while `process.argv[1]` does not.)
const isDirectRun = process.argv[1] === fileURLToPath(import.meta.url);
if (isDirectRun) {
  const config: Phase1Input = {
    clientName: 'Georgia State Election Board',
    slug: 'georgiaseb',
    installName: 'georgiaseb',
    accountName: 'classiccity',
    environment: 'production',
  };
  runPhase1(config).catch((e) => {
    console.error('\nFAILED:', e.message);
    if (e instanceof WpeApiError) {
      console.error('Body:', JSON.stringify(e.body, null, 2));
    }
    process.exit(1);
  });
}
