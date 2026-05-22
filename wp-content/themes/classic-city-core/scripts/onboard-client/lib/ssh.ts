import { spawn } from 'node:child_process';
import { createReadStream } from 'node:fs';
import { stat } from 'node:fs/promises';

export interface SSHResult {
  stdout: string;
  stderr: string;
  exitCode: number;
}

const SSH_OPTS = [
  '-o', 'BatchMode=yes',
  '-o', 'ConnectTimeout=30',
  '-o', 'StrictHostKeyChecking=accept-new',
];

export function sshHost(install: string): string {
  return `${install}@${install}.ssh.wpengine.net`;
}

/**
 * Wrap a string in single quotes for safe inclusion as a bash literal.
 * Inner single quotes are escaped via the standard `'"'"'` trick. Use this
 * for ANY string that gets interpolated into a remote script — license keys,
 * client names, paths, anything from outside the script source.
 */
export function bashSingleQuote(s: string): string {
  return `'${s.replace(/'/g, "'\"'\"'")}'`;
}

/**
 * Run a command on the WPE install. For multi-line scripts use runScript()
 * which sends the body via stdin to `bash -s`.
 */
export async function runSSH(
  install: string,
  command: string,
  opts: { input?: string } = {}
): Promise<SSHResult> {
  return new Promise((resolve, reject) => {
    const child = spawn('ssh', [...SSH_OPTS, sshHost(install), command], {
      stdio: ['pipe', 'pipe', 'pipe'],
    });
    let stdout = '';
    let stderr = '';
    child.stdout.on('data', (chunk) => (stdout += chunk.toString()));
    child.stderr.on('data', (chunk) => (stderr += chunk.toString()));
    child.on('error', reject);
    child.on('close', (code) =>
      resolve({ stdout, stderr, exitCode: code ?? 0 })
    );
    if (opts.input !== undefined) {
      child.stdin.write(opts.input);
    }
    child.stdin.end();
  });
}

/**
 * Run a multi-line bash script on the install via `bash -s`. The script body
 * is delivered over stdin so quoting is straightforward.
 */
export async function runScript(install: string, script: string): Promise<SSHResult> {
  return runSSH(install, 'bash -s', { input: script });
}

/**
 * Upload a local file to the WPE install via pipe-over-SSH. WPE's SSH Gateway
 * blocks SCP's SFTP subsystem, so streaming via stdin is the supported path.
 */
export async function uploadFile(install: string, localPath: string, remotePath: string): Promise<void> {
  const stats = await stat(localPath);
  if (!stats.isFile()) {
    throw new Error(`uploadFile: ${localPath} is not a file`);
  }

  return new Promise((resolve, reject) => {
    const child = spawn(
      'ssh',
      [...SSH_OPTS, sshHost(install), `cat > ${bashSingleQuote(remotePath)}`],
      { stdio: ['pipe', 'pipe', 'pipe'] }
    );
    let stderr = '';
    child.stderr.on('data', (chunk) => (stderr += chunk.toString()));
    child.on('error', reject);
    child.on('close', (code) => {
      if (code === 0) resolve();
      else reject(new Error(`uploadFile failed (ssh exit ${code}): ${stderr.trim()}`));
    });
    createReadStream(localPath).pipe(child.stdin);
  });
}
