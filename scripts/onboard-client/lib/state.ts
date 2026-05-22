import { readFileSync, writeFileSync, existsSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const STATE_DIR = join(__dirname, '..', 'state');

export interface OnboardingState {
  slug: string;
  clientName: string;
  installName: string;
  environment: 'production' | 'staging' | 'development';
  accountName: string;
  startedAt: string;
  phase1?: {
    completedAt: string;
    site: {
      id: string;
      name: string;
      account_id: string;
      created_now: boolean;
    };
    install: {
      id: string;
      name: string;
      site_id: string;
      account_id: string;
      environment: string;
      status?: string;
      primary_domain?: string;
      cname?: string;
      php_version?: string;
    };
    tempUrl: string;
    tempUrlStatus: number;
  };
  phase1b?: {
    completedAt: string;
    plugins: {
      acf_pro: { installed: boolean; licensed: boolean };
      gravity_forms: { installed: boolean; licensed: boolean };
      wordpress_seo: { installed: boolean };
    };
    siteMeta: {
      blogname: string;
      blogdescription: string;
      permalinkStructure: string;
    };
    removedDefaults: string[];
  };
  phase3?: {
    completedAt: string;
    repo: {
      nameWithOwner: string;
      url: string;
      sshUrl: string;
      isPrivate: boolean;
      created_now: boolean;
    };
  };
  phase4?: {
    completedAt: string;
    localPath: string;          // /Users/chris/Local Sites/{folder}
    localSiteRoot: string;      // {localPath}/app/public
    localFolderName: string;    // {folder}
  };
  phase5?: {
    completedAt: string;
    gitRoot: string;
    originUrl: string;
  };
  phase6?: {
    completedAt: string;
    gitignorePath: string;
  };
  phase7?: {
    completedAt: string;
    parentRemote: string;
    parentUrl: string;
    parentBranch: string;
  };
  phase7b?: {
    completedAt: string;
    copiedFiles: string[];
  };
  phase8?: {
    completedAt: string;
    childTheme: string;
    sourceTemplate: string;
    filesSubstituted: number;
    initialCommit: string;
  };
  phase9?: {
    completedAt: string;
    wpeRemoteUrl: string;
    originUrl: string;
    themeUrl: string;
    themeUrlStatus: number;
  };
}

export function statePath(slug: string): string {
  return join(STATE_DIR, `${slug}.json`);
}

export function loadState(slug: string): OnboardingState | null {
  const path = statePath(slug);
  if (!existsSync(path)) return null;
  return JSON.parse(readFileSync(path, 'utf8'));
}

export function saveState(state: OnboardingState): void {
  writeFileSync(statePath(state.slug), JSON.stringify(state, null, 2) + '\n');
}
