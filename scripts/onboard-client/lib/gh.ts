import { spawn } from 'node:child_process';

export interface GhResult {
  stdout: string;
  stderr: string;
  exitCode: number;
}

export interface RepoInfo {
  name: string;
  nameWithOwner: string;
  url: string;
  sshUrl: string;
  isPrivate: boolean;
}

export async function runGh(args: string[]): Promise<GhResult> {
  return new Promise((resolve, reject) => {
    const child = spawn('gh', args, { stdio: ['ignore', 'pipe', 'pipe'] });
    let stdout = '';
    let stderr = '';
    child.stdout.on('data', (chunk) => (stdout += chunk.toString()));
    child.stderr.on('data', (chunk) => (stderr += chunk.toString()));
    child.on('error', reject);
    child.on('close', (code) =>
      resolve({ stdout, stderr, exitCode: code ?? 0 })
    );
  });
}

export async function ensureGhAuth(): Promise<void> {
  const res = await runGh(['auth', 'status']);
  if (res.exitCode !== 0) {
    throw new Error(
      `gh CLI is not authenticated. Run \`gh auth login\` (HTTPS via web browser) first.\n${res.stderr}`
    );
  }
}

export async function findRepo(nameWithOwner: string): Promise<RepoInfo | null> {
  const res = await runGh([
    'repo', 'view', nameWithOwner,
    '--json', 'name,nameWithOwner,url,sshUrl,isPrivate',
  ]);
  if (res.exitCode !== 0) return null;
  return JSON.parse(res.stdout);
}

export async function createRepo(opts: {
  nameWithOwner: string;
  description?: string;
  isPrivate: boolean;
}): Promise<RepoInfo> {
  const args = [
    'repo', 'create', opts.nameWithOwner,
    opts.isPrivate ? '--private' : '--public',
    '--clone=false',
  ];
  if (opts.description) args.push('--description', opts.description);

  const res = await runGh(args);
  if (res.exitCode !== 0) {
    throw new Error(`gh repo create failed: ${res.stderr.trim()}`);
  }
  // gh's create stdout is just the URL — query the repo for the full record.
  const repo = await findRepo(opts.nameWithOwner);
  if (!repo) {
    throw new Error(`Repo ${opts.nameWithOwner} created but not visible via gh repo view`);
  }
  return repo;
}
