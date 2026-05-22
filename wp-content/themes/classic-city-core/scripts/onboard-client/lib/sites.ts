import { wpe, type PaginatedResults } from './wpe-api.js';

export interface Site {
  id: string;
  name: string;
  account: { id: string };
  group_name: string | null;
  tags: string[];
  created_at: string;
  sandbox: boolean;
  transferable: boolean;
  installs?: Array<{
    id: string;
    name: string;
    environment: string;
    cname: string;
    php_version: string;
    is_multisite: boolean;
  }>;
}

export async function listSites(): Promise<Site[]> {
  // WPE returns up to 100 sites per page. We page until exhausted.
  const all: Site[] = [];
  let next: string | null = '/sites?limit=100';
  while (next) {
    const data: PaginatedResults<Site> = await wpe<PaginatedResults<Site>>('GET', next);
    all.push(...data.results);
    next = data.next;
  }
  return all;
}

export async function findSiteByName(name: string, accountId?: string): Promise<Site | null> {
  const sites = await listSites();
  return (
    sites.find(
      (s) => s.name === name && (!accountId || s.account.id === accountId)
    ) ?? null
  );
}

export async function createSite(opts: { name: string; accountId: string }): Promise<Site> {
  return wpe<Site>('POST', '/sites', {
    name: opts.name,
    account_id: opts.accountId,
  });
}

export async function findOrCreateSite(opts: {
  name: string;
  accountId: string;
}): Promise<{ site: Site; created: boolean }> {
  const existing = await findSiteByName(opts.name, opts.accountId);
  if (existing) return { site: existing, created: false };
  const site = await createSite(opts);
  return { site, created: true };
}
