import { config as loadEnv } from 'dotenv';
import { homedir } from 'node:os';
import { join } from 'node:path';

loadEnv({ path: join(homedir(), '.config', 'wpe', 'credentials.env') });

const USERNAME = process.env.WPE_API_USERNAME;
const PASSWORD = process.env.WPE_API_PASSWORD;

if (!USERNAME || !PASSWORD) {
  throw new Error(
    'Missing WPE credentials. Expected ~/.config/wpe/credentials.env to define WPE_API_USERNAME and WPE_API_PASSWORD.'
  );
}

const BASE = 'https://api.wpengineapi.com/v1';

function authHeader(): string {
  return 'Basic ' + Buffer.from(`${USERNAME}:${PASSWORD}`).toString('base64');
}

export class WpeApiError extends Error {
  constructor(public method: string, public path: string, public status: number, public body: unknown) {
    super(`WPE API ${method} ${path} → ${status}: ${typeof body === 'string' ? body : JSON.stringify(body)}`);
    this.name = 'WpeApiError';
  }
}

export interface WpeRequestOptions {
  retries?: number;
  retryDelayMs?: number;
}

export async function wpe<T = unknown>(
  method: string,
  path: string,
  body?: unknown,
  opts: WpeRequestOptions = {}
): Promise<T> {
  const url = path.startsWith('http') ? path : `${BASE}${path}`;
  const retries = opts.retries ?? 2;
  const retryDelayMs = opts.retryDelayMs ?? 10000;

  let lastErr: unknown;
  for (let attempt = 0; attempt <= retries; attempt++) {
    if (attempt > 0) {
      console.error(`  (retry ${attempt}/${retries} after ${retryDelayMs}ms — last error: ${lastErr instanceof Error ? lastErr.message : lastErr})`);
      await new Promise((r) => setTimeout(r, retryDelayMs));
    }

    const res = await fetch(url, {
      method,
      headers: {
        Authorization: authHeader(),
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: body !== undefined ? JSON.stringify(body) : undefined,
    });

    const text = await res.text();
    let parsed: unknown = null;
    if (text) {
      try {
        parsed = JSON.parse(text);
      } catch {
        parsed = text;
      }
    }

    if (res.ok) {
      return parsed as T;
    }

    const err = new WpeApiError(method, path, res.status, parsed);

    // Retry only on transient gateway/server errors. Client errors (4xx) are
    // never retried — the caller's payload is wrong, not WPE's mood.
    const isTransient = res.status >= 500 && res.status <= 599;
    if (!isTransient || attempt === retries) {
      throw err;
    }
    lastErr = err;
  }

  // Unreachable.
  throw lastErr instanceof Error ? lastErr : new Error('wpe(): exhausted retries');
}

export interface PaginatedResults<T> {
  count: number;
  next: string | null;
  previous: string | null;
  results: T[];
}
