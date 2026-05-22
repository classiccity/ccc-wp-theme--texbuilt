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

- **[CHIEF_OF_STUFF_HANDOFF.md](./CHIEF_OF_STUFF_HANDOFF.md)** — the
  agent contract between Chris's Chief of Stuff system and this repo.
  Defines the client-config YAML schema CoS produces when a deal closes,
  the two-track execution model, stop conditions, and the canonical
  command surface. Worked example handoff at
  [`client-config.example.yaml`](./client-config.example.yaml).

## Keeping docs current

When you run an onboarding and something doesn't match the runbook,
update `CLIENT_ONBOARDING.md` in the same PR / commit that captures the
fix. The runbook has a `## Change log` section at the bottom — append a
short note dated entry there whenever content shifts.
