#!/usr/bin/env python3
"""
Build-update reminder hook — Classic City Core (shipped to every child repo
via the parent-theme subtree).

WHY: build status must reach the Chief of Stuff inbox even when nobody says
"log a build update" — including WPE content changes that never hit git. This
hook makes the wrap-up rule in the parent CLAUDE.md ("Build updates → Chief of
Stuff") deterministic instead of memory-dependent.

HOW IT'S WIRED (per client repo, at the SITE ROOT — see
scripts/hooks/install-build-update-hook.py):

  .claude/settings.json:
    PostToolUse (matcher "Bash|Edit|Write") -> this script, argv[1] = posttooluse
    Stop                                    -> this script, argv[1] = stop

BEHAVIOR (markers in $TMPDIR/ccc-build-update-{session_id}/):
  * posttooluse: watches tool calls.
      - "ship" signals arm the session:  `git push` (deploy or code push), or
        an SSH to *.wpengine.net whose command carries a WRITE verb (eval-file,
        media import, post/meta/option update, `cat >` uploads…). Read-only
        ssh (wp post get, option get) does NOT arm.
      - "logged" signals disarm it: any command or file touching
        build-updates.md (the Chief of Stuff inbox).
      - On first arming, injects a one-line reminder into Claude's context.
  * stop: if armed && not logged, blocks the FIRST stop with self-contained
    logging instructions (once per session — never nags twice; also respects
    stop_hook_active to avoid loops). If nothing shipped, stays silent.

The hook only ever observes and nudges — it never writes to the vault itself;
Claude does the logging with real session context.
"""

import json
import os
import re
import sys
import tempfile

SHIP_GIT_PUSH = re.compile(r"\bgit\b[^\n|;&]*\bpush\b")
SSH_WPE = re.compile(r"\bssh\b.*wpengine", re.IGNORECASE)
SSH_WRITE_VERBS = re.compile(
    r"(eval-file|media\s+import|meta\s+(update|add|delete)"
    r"|post\s+(update|create|delete)|option\s+(update|add|delete)"
    r"|term\s+(create|update|delete)|theme\s+activate"
    r"|plugin\s+(install|activate|deactivate|delete)|db\s+import|cat\s*>)",
    re.IGNORECASE,
)
LOGGED_SIGNAL = "build-updates.md"

INBOX = '"$HOME/Chief of Stuff/Brain/Chief of Stuff/build-updates.md"'

STOP_REASON = (
    "This session pushed a deploy or wrote WPE content, and no build update has "
    "been logged to Chief of Stuff yet. If the work set is wrapped, append ONE "
    "block to " + INBOX + " directly under the file header, ABOVE the "
    "'↑ unsynced ↑' divider (do not read anything else in the vault):\n\n"
    "## YYYY-MM-DD — {client}: {one-line headline}\n"
    "client: {best-guess client name}\n"
    "repo: {repo name}\n"
    "milestone: {optional — phase this advances}\n"
    "{1–3 sentences: what shipped / what's blocked / what's next — "
    "include DB-only content changes that never hit git}\n\n"
    "Then commit + push JUST that file:\n"
    '  git -C "$HOME/Chief of Stuff" add "Brain/Chief of Stuff/build-updates.md"\n'
    '  git -C "$HOME/Chief of Stuff" commit -m "build-update: {client} ({repo})"\n'
    '  git -C "$HOME/Chief of Stuff" pull --rebase --autostash && '
    'git -C "$HOME/Chief of Stuff" push\n\n'
    "If the task set is still mid-flight, or nothing status-worthy actually "
    "shipped, say so in one line and finish — this reminder fires only once "
    "per session."
)

NUDGE = (
    "[build-update hook] A WPE deploy/content write just happened. When this "
    "work set wraps up, log a build update to the Chief of Stuff inbox "
    "(" + INBOX + ", block format per parent CLAUDE.md § 'Build updates') "
    "unless one has already been logged this session."
)


def marker_dir(session_id):
    d = os.path.join(
        tempfile.gettempdir(),
        "ccc-build-update-" + re.sub(r"[^A-Za-z0-9_-]", "_", session_id or "unknown"),
    )
    os.makedirs(d, exist_ok=True)
    return d


def has(d, name):
    return os.path.exists(os.path.join(d, name))


def mark(d, name):
    open(os.path.join(d, name), "w").close()


def main():
    event = (sys.argv[1] if len(sys.argv) > 1 else "").lower()
    try:
        data = json.load(sys.stdin)
    except Exception:
        return  # malformed input: never break the session over a reminder

    d = marker_dir(data.get("session_id", ""))

    if event == "posttooluse":
        tool_input = data.get("tool_input") or {}
        blob = " ".join(
            str(tool_input.get(k, "")) for k in ("command", "file_path", "content", "new_string")
        )
        if LOGGED_SIGNAL in blob:
            mark(d, "logged")  # logging flow — never treat as a ship signal
            return
        shipped = bool(SHIP_GIT_PUSH.search(blob)) or (
            bool(SSH_WPE.search(blob)) and bool(SSH_WRITE_VERBS.search(blob))
        )
        if shipped and not has(d, "shipped"):
            mark(d, "shipped")
            print(json.dumps({
                "hookSpecificOutput": {
                    "hookEventName": "PostToolUse",
                    "additionalContext": NUDGE,
                }
            }))
        return

    if event == "stop":
        if data.get("stop_hook_active"):
            return  # already continuing from a stop hook — never loop
        if has(d, "shipped") and not has(d, "logged") and not has(d, "stop-blocked"):
            mark(d, "stop-blocked")  # one nudge per session, maximum
            print(json.dumps({"decision": "block", "reason": STOP_REASON}))
        return


if __name__ == "__main__":
    main()
