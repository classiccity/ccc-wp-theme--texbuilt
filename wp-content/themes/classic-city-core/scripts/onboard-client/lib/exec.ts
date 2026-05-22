import { spawn } from 'node:child_process';

export interface ExecResult {
  stdout: string;
  stderr: string;
  exitCode: number;
}

export class ExecError extends Error {
  constructor(public cmd: string, public args: string[], public result: ExecResult) {
    super(
      `\`${cmd} ${args.join(' ')}\` failed with exit ${result.exitCode}.\n` +
        (result.stderr.trim() ? `stderr: ${result.stderr.trim()}\n` : '') +
        (result.stdout.trim() ? `stdout: ${result.stdout.trim()}` : '')
    );
    this.name = 'ExecError';
  }
}

export interface ExecOptions {
  cwd?: string;
  env?: Record<string, string>;
  input?: string;
}

export async function exec(cmd: string, args: string[], opts: ExecOptions = {}): Promise<ExecResult> {
  return new Promise((resolve, reject) => {
    const child = spawn(cmd, args, {
      cwd: opts.cwd,
      env: opts.env ? { ...process.env, ...opts.env } : process.env,
      stdio: ['pipe', 'pipe', 'pipe'],
    });
    let stdout = '';
    let stderr = '';
    child.stdout.on('data', (chunk) => (stdout += chunk.toString()));
    child.stderr.on('data', (chunk) => (stderr += chunk.toString()));
    child.on('error', reject);
    child.on('close', (code) => resolve({ stdout, stderr, exitCode: code ?? 0 }));
    if (opts.input !== undefined) {
      child.stdin.write(opts.input);
    }
    child.stdin.end();
  });
}

export async function execOrThrow(cmd: string, args: string[], opts: ExecOptions = {}): Promise<ExecResult> {
  const result = await exec(cmd, args, opts);
  if (result.exitCode !== 0) {
    throw new ExecError(cmd, args, result);
  }
  return result;
}
