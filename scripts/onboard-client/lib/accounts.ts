import { wpe, type PaginatedResults } from './wpe-api.js';

export interface Account {
  id: string;
  name: string;
}

let cachedAccounts: Account[] | null = null;

export async function listAccounts(): Promise<Account[]> {
  if (cachedAccounts) return cachedAccounts;
  const data = await wpe<PaginatedResults<Account>>('GET', '/accounts');
  cachedAccounts = data.results;
  return cachedAccounts;
}

export async function findAccountByName(name: string): Promise<Account> {
  const accounts = await listAccounts();
  const found = accounts.find((a) => a.name === name);
  if (!found) {
    const visible = accounts.map((a) => a.name).join(', ');
    throw new Error(`Account '${name}' not found. Visible accounts: ${visible}`);
  }
  return found;
}
