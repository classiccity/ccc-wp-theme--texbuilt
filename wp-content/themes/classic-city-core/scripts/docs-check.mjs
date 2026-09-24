#!/usr/bin/env node
/**
 * docs-check — guards against documentation drift.
 *
 * Run: npm run docs:check   (plain node, no dependencies)
 *
 * Checks:
 *  1. Every relative markdown link in docs/*.md, CLAUDE.md, and README-ish
 *     files points at a file that exists.
 *  2. Every "*.md" filename mentioned in PHP/TS source comments exists in
 *     the repo (this is how BLOCK_MARKUP_CONTRACT.md went stale unnoticed).
 *  3. docs/BLOCKS.md is in sync with blocks/ (same block count) — reminds
 *     you to `npm run docs:blocks` after adding a block.
 *  4. If CLAUDE.md claims a block count ("N custom blocks"), it matches
 *     the real count.
 *
 * Exit 0 = clean; exit 1 = drift found (printed per finding).
 */

import { readFileSync, readdirSync, existsSync, statSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

// fileURLToPath, NOT new URL(...).pathname — the latter URL-encodes the
// spaces in "Local Sites" and every path lookup silently misses.
const ROOT = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const problems = [];

function rel(p) {
  return p.startsWith(ROOT) ? p.slice(ROOT.length + 1) : p;
}

function listFiles(dir, exts, out = []) {
  for (const entry of readdirSync(dir)) {
    if (entry === 'node_modules' || entry === '.git' || entry === 'assets') continue;
    const full = join(dir, entry);
    const st = statSync(full);
    if (st.isDirectory()) listFiles(full, exts, out);
    else if (exts.some((e) => entry.endsWith(e))) out.push(full);
  }
  return out;
}

// ---------- 1. Relative markdown links in docs ----------
const docFiles = [
  join(ROOT, 'CLAUDE.md'),
  ...listFiles(join(ROOT, 'docs'), ['.md']),
].filter(existsSync);

const LINK_RE = /\[[^\]]*\]\(([^)\s]+)\)/g;

for (const file of docFiles) {
  const text = readFileSync(file, 'utf8');
  for (const m of text.matchAll(LINK_RE)) {
    let target = m[1];
    if (/^(https?:|mailto:|tel:)/.test(target)) continue;   // external
    target = target.split('#')[0];
    if (target === '') continue;                             // pure anchor
    if (/[{}<>*]/.test(target)) continue;                    // placeholder path
    const candidate = resolve(dirname(file), target);
    if (!existsSync(candidate)) {
      problems.push(`${rel(file)}: broken link → ${m[1]}`);
    }
  }
}

// ---------- 2. .md filenames referenced from source code ----------
const srcFiles = [
  ...listFiles(join(ROOT, 'inc'), ['.php']),
  ...listFiles(join(ROOT, 'blocks'), ['.php']),
  ...listFiles(join(ROOT, 'partials'), ['.php']),
  ...(existsSync(join(ROOT, 'scripts')) ? listFiles(join(ROOT, 'scripts'), ['.ts', '.mjs', '.php']) : []),
];

const MD_RE = /[A-Za-z0-9_./-]+\.md\b/g;

for (const file of srcFiles) {
  if (file === resolve(ROOT, 'scripts/docs-check.mjs')) continue; // self
  const text = readFileSync(file, 'utf8');
  for (const m of text.matchAll(MD_RE)) {
    const ref = m[0].replace(/^\.\//, '');
    if (/[{}<>*]/.test(ref)) continue;
    const candidates = [
      resolve(dirname(file), ref),
      resolve(ROOT, ref),
      resolve(ROOT, 'docs', ref.split('/').pop()),
      resolve(ROOT, ref.split('/').pop()),
    ];
    if (!candidates.some(existsSync)) {
      problems.push(`${rel(file)}: references missing doc → ${m[0]}`);
    }
  }
}

// ---------- 3. BLOCKS.md in sync with blocks/ ----------
const blockDirs = readdirSync(join(ROOT, 'blocks')).filter((d) =>
  existsSync(join(ROOT, 'blocks', d, 'block.json'))
);
const blocksMdPath = join(ROOT, 'docs', 'BLOCKS.md');
if (!existsSync(blocksMdPath)) {
  problems.push('docs/BLOCKS.md is missing — run `npm run docs:blocks`');
} else {
  const sections = [...readFileSync(blocksMdPath, 'utf8').matchAll(/^### /gm)].length;
  if (sections !== blockDirs.length) {
    problems.push(
      `docs/BLOCKS.md has ${sections} block sections but blocks/ has ${blockDirs.length} — run \`npm run docs:blocks\``
    );
  }
}

// ---------- 4. CLAUDE.md block-count claim ----------
const claudeMd = readFileSync(join(ROOT, 'CLAUDE.md'), 'utf8');
const countClaim = claudeMd.match(/~?(\d+)\s+custom(?:\s+ACF)?\s+blocks/i);
if (countClaim && Number(countClaim[1]) !== blockDirs.length) {
  problems.push(
    `CLAUDE.md claims ${countClaim[1]} custom blocks; blocks/ has ${blockDirs.length}`
  );
}

// ---------- report ----------
if (problems.length) {
  console.error(`docs-check: ${problems.length} problem(s)\n`);
  for (const p of problems) console.error(`  ✗ ${p}`);
  process.exit(1);
}
console.log(
  `docs-check: OK (${docFiles.length} docs, ${srcFiles.length} source files, ${blockDirs.length} blocks)`
);
