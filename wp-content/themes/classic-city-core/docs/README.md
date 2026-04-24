# Classic City Core — Docs

Documentation for the shared parent theme and the client-onboarding
workflow that depends on it.

## Contents

- **[ARCHITECTURE.md](./ARCHITECTURE.md)** — why the multi-repo +
  parent/child + subtree setup looks the way it does. Read this first
  if you're new to the project.

- **[CLIENT_ONBOARDING.md](./CLIENT_ONBOARDING.md)** — step-by-step
  runbook for spinning up a new client site end-to-end: WP Engine
  install, GitHub repo, Local mirror, parent-theme subtree wiring,
  child-theme scaffolding, deploy setup, content workflow. Includes a
  pitfalls table and the automation roadmap.

## Keeping docs current

When you run an onboarding and something doesn't match the runbook,
update `CLIENT_ONBOARDING.md` in the same PR / commit that captures the
fix. The runbook has a `## Change log` section at the bottom — append a
short note dated entry there whenever content shifts.
