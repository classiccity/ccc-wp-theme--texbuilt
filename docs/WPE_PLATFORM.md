# WP Engine Platform Behavior — Canonical Reference

Every WPE gotcha we have hit, in one place. **This file is the single
source of truth for WPE platform behavior.** The runbooks
([`CLIENT_ONBOARDING.md`](./CLIENT_ONBOARDING.md),
[`CLIENT_HOMEPAGE.md`](./CLIENT_HOMEPAGE.md),
[`BULK_IMPORT.md`](./BULK_IMPORT.md)) repeat some of these inline where
the context helps — **if a runbook ever disagrees with this file, this
file wins; fix the runbook.**

Last verified: 2026-07-09.

---

## 1. The three access systems (they don't share keys)

| System | Scope | Key registration | API-manageable? |
|---|---|---|---|
| **SSH Gateway** (`{install}@{install}.ssh.wpengine.net`) | shell + wp-cli | **User-level, once**: User Portal → User Profile → SSH Keys. Propagates to every install in every account you own. | Yes (`/ssh_keys`, account-level) |
| **Git Push** (`git@git.wpengine.com:production/{install}.git`) | deploys | **Per-install, manual**: the install's Git Push tab. No API path (`/installs/{id}/ssh_keys` and `/git_push_keys` both 404). | **No** — genuinely manual |
| **REST API / wp-admin** | content + settings | Application Passwords (per user, per install) | n/a |

Adding a key to one system grants nothing in the others.

**Git Push symptom if key is missing/wrong-install:**
`FATAL: W any production/{install} {other-install}-{user} DENIED by fallthru`.
Verify with `ssh -T git@git.wpengine.com` → should print `R W {install}`.

**WPE Public API model:** Account → Site → Install. `POST /installs`
requires **both** `site_id` and `account_id` (undocumented; omitting one
returns a misleading 400). Install names ≤14 chars. API-gateway 504s are
common and transient — retry 2× with backoff.

## 2. SSH Gateway realities

- **`scp`/`sftp` are blocked** ("Connection closed", exit 255). Move files
  by piping through ssh instead:
  ```bash
  cat local.file | ssh {i}@{i}.ssh.wpengine.net "cat > /home/wpe-user/sites/{i}/remote.file"
  # or equivalently:  ssh {i}@... "cat > path" < local.file
  ```
- **Argument quoting is mangled** — `ssh host 'wp term create TAX "TWO WORDS"'`
  silently splits `"TWO WORDS"` into two args. Pipe commands via **stdin**:
  `echo '…' | ssh host bash` (or `ssh host bash -s <<'EOF' … EOF`). In Python:
  `subprocess.run(["ssh", host, "bash"], input=cmd, text=True)`.
- **Long sessions get killed.** Don't loop wp-cli inside one ssh session
  (each `wp` call boots WP: 5–10 s; `wp media import` also generates every
  thumbnail synchronously). Upload a PHP script + data, run it via a
  **single-boot** `wp eval-file`, in **batched, resumable** passes
  (a map/state file lets you re-invoke until done).
- **Concurrency is throttled per install.** Two parallel SSH-heavy tasks
  starve each other (observed: a trivial command taking 6m47s under
  contention). **Serialize all WPE-SSH work**; parallelize only local work.
- **Requests are load-balanced across containers** — `/tmp` and uploaded
  files are per-container. Any "write a file, then use it" sequence must
  stay inside **one** `ssh` invocation.
- **wp-cli can't see `/home/wpe-user/` directly.** Upload into
  `/home/wpe-user/sites/{install}/` (or `wp-content/uploads/…`, always
  writable) and reference relatively after `cd`.
- **`wp eval` with complex PHP breaks** in shells/heredocs (`->`, nested
  quotes, `$` expansion). Use `wp eval-file` with a piped-up `.php` file.
- **`wp plugin delete` does not auto-deactivate** and `--deactivate` is no
  longer a valid flag. Deactivate first (`|| true`), then delete.
- **Connection multiplexing** makes many-small-ssh workflows tolerable
  (~2 s handshake → ~50 ms):
  ```
  Host *.ssh.wpengine.net
      ControlMaster auto
      ControlPath ~/.ssh/cm/%C
      ControlPersist 5m
  ```

## 3. REST API on WPE

- **The WAF blocks some REST writes on some installs — probe before you
  build** (one curl per endpoint; expect 201, clean up after):
  `POST /wp/v2/media`, `POST /wp/v2/pages|posts`, and any URL with an image
  extension in the query string commonly return **403 from nginx**.
  Fallback: wp-cli over SSH Gateway for just those operations, or a WPE
  support ticket to tune the WAF.
- **The default `python-requests` UA is on the WAF blocklist** — silent
  403s. Set any custom `User-Agent`.
- **`Authorization` header is stripped** before PHP sees it → Basic Auth
  fails as `rest_not_logged_in` 401. Ship the
  `wp-content/mu-plugins/ccc-fix-auth-header.php` mu-plugin (every client
  repo whitelists it).
- **Fresh installs default to Plain permalinks**, which break `/wp-json/`
  routing (you get homepage HTML). Either set
  `wp rewrite structure '/%postname%/'` (Phase 1b does) or use the
  always-works form: `/?rest_route=/wp/v2/...`.
- **Admin `user_login` is often the email address** (WPE provisions it
  that way). Verify with `GET /wp/v2/users/me` before baking a username
  into scripts — the wrong one returns a generic `rest_not_logged_in`.
- Use **Application Passwords**, minted per job:
  `wp user application-password create EMAIL 'job-name' --porcelain`.
- **What works via REST:** all GETs, `POST /wp/v2/settings`, custom
  plugin/theme endpoints. Theme activation has **no** REST path
  (`/wp/v2/themes` is read-only) — use `wp theme activate` over SSH.

## 4. Deploys and caching

- **Git Push deploys to the site root** — no subpath targeting. Client
  repos must be scoped to the WP site root with a whitelist `.gitignore`
  (see `CLIENT_ONBOARDING.md` Phases 5–6).
- **Git Push is additive** — it never deletes files removed between
  pushes. Stale server files need manual cleanup (SFTP/File Manager).
- **Git Push does NOT materialize submodules** — its "checking submodules"
  step doesn't clone content; a submoduled parent theme deploys as an
  empty directory. This is why the parent theme is a **subtree**.
- **Varnish vs object cache:** git pushes auto-purge Varnish at the end of
  the deploy; **wp-cli content changes do not**, and `wp cache flush` only
  clears the PHP object cache. After a wp-cli content run, either push a
  trivial commit (e.g. touch `.cache-bust-timestamp`), click "Purge all
  caches" in the portal, or wait out the TTL. Verify origin correctness
  meanwhile with cache-busted URLs (`?_cb=<ts>`).
- **New installs sit behind portal Password Protection (HTTP 401)** —
  disable it (Utilities → Password Protection) or share creds before
  sending a review URL; render server-side via
  `wp eval-file` + `do_blocks()` when you just need to verify output.

## 5. Quick symptom index

| Symptom | See |
|---|---|
| `DENIED by fallthru` on `git push wpe` | §1 — Git Push key is per-install, manual |
| `Connection closed` on scp | §2 — pipe over ssh |
| Quoted args split on the remote side | §2 — pipe command via stdin |
| Long import dies partway | §2 — single-boot `wp eval-file`, batched |
| Everything slow / "hung" during imports | §2 — serialize WPE-SSH work |
| Uploaded file "vanishes" between ssh calls | §2 — per-container storage, one session |
| REST write → 403 from nginx | §3 — WAF; probe, fall back to wp-cli |
| 403 only from Python | §3 — set a custom User-Agent |
| `rest_not_logged_in` 401 with correct creds | §3 — mu-plugin, then email-as-login |
| `/wp-json/` returns HTML | §3 — permalinks / `?rest_route=` |
| Parent theme empty after deploy | §4 — submodule; must be subtree |
| Content changes not visible (but origin correct) | §4 — Varnish; push or purge |
| Review URL prompts for password / 401 | §4 — portal password protection |
