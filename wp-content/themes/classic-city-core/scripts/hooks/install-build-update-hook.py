#!/usr/bin/env python3
"""
Install the build-update reminder hook into a client repo's Claude Code
project settings (.claude/settings.json at the SITE ROOT).

Run from anywhere inside the client repo:

    python3 wp-content/themes/classic-city-core/scripts/hooks/install-build-update-hook.py

Idempotent: creates .claude/settings.json if missing, MERGES if present
(existing settings and hooks are preserved; our entries are added only if a
build-update-reminder hook isn't already wired). Commit the resulting file —
project settings travel with the repo, so both machines (and any clone) get
the hook automatically. Claude Code prompts once to approve project hooks.

Companion: build-update-reminder.py (same directory) — the actual hook.
Parent CLAUDE.md § "Build updates → Chief of Stuff" is the human-readable rule.
"""

import json
import os
import subprocess
import sys

SCRIPT_REL = "wp-content/themes/classic-city-core/scripts/hooks/build-update-reminder.py"
SENTINEL = "build-update-reminder.py"


def site_root():
    try:
        top = subprocess.run(
            ["git", "rev-parse", "--show-toplevel"],
            capture_output=True, text=True, check=True,
        ).stdout.strip()
    except Exception:
        sys.exit("ERROR: run this from inside a client repo (git not found or not a repo).")
    if not os.path.isfile(os.path.join(top, SCRIPT_REL)):
        sys.exit(
            "ERROR: %s not found under %s — pull the classic-city-core subtree first."
            % (SCRIPT_REL, top)
        )
    return top


def hook_entry(event_arg, matcher=None):
    entry = {
        "hooks": [
            {
                "type": "command",
                "command": 'python3 "$CLAUDE_PROJECT_DIR/%s" %s' % (SCRIPT_REL, event_arg),
                "timeout": 10,
            }
        ]
    }
    if matcher is not None:
        entry["matcher"] = matcher
    return entry


def already_wired(entries):
    for group in entries or []:
        for h in group.get("hooks", []):
            if SENTINEL in h.get("command", ""):
                return True
    return False


def main():
    root = site_root()
    settings_path = os.path.join(root, ".claude", "settings.json")
    os.makedirs(os.path.dirname(settings_path), exist_ok=True)

    settings = {}
    if os.path.exists(settings_path):
        with open(settings_path) as f:
            try:
                settings = json.load(f)
            except ValueError:
                sys.exit("ERROR: %s is not valid JSON — fix it before installing." % settings_path)

    hooks = settings.setdefault("hooks", {})
    changed = []

    if not already_wired(hooks.get("PostToolUse")):
        hooks.setdefault("PostToolUse", []).append(hook_entry("posttooluse", "Bash|Edit|Write"))
        changed.append("PostToolUse")
    if not already_wired(hooks.get("Stop")):
        hooks.setdefault("Stop", []).append(hook_entry("stop"))
        changed.append("Stop")

    if not changed:
        print("Already installed — %s untouched." % settings_path)
        return

    with open(settings_path, "w") as f:
        json.dump(settings, f, indent=2)
        f.write("\n")
    print("Wired %s in %s" % (" + ".join(changed), settings_path))
    print("Next: commit .claude/settings.json (whitelist it in .gitignore if needed).")


if __name__ == "__main__":
    main()
