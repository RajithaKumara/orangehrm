@AGENTS.md

# MCP plugin work (branch `mcp`)

The import above is upstream's agent guide for OrangeHRM Starter as a whole; it
applies here too. This section is branch-local and covers the **Model Context
Protocol (MCP) server plugin** being built on this branch. It is not upstream and
must not be pushed to `main`.

## Read these first, in order

1. `src/plugins/orangehrmMcpPlugin/README.md` — the MCP development handoff: current
   state, locked decisions, corrections to the original plan, the ordered development
   plan, and code recipes. **Start every MCP session here.**
2. `_bmad-output/project-context.md` — 57 non-obvious codebase rules (PHP 7.4 floor,
   DI via traits, validation declared not inline, GPL header, php-cs-fix, test
   conventions). Follow them.
3. Only when depth is needed: `_bmad-output/planning-artifacts/architecture.md`,
   `prd.md`, `epics.md`.

The `_bmad/` workflow folders are an empty skeleton and the original planning session
is gone. Do not try to re-run the BMAD workflows; work story by story from the
README's plan and the acceptance criteria in `epics.md`.

## Non-negotiables

- Every PHP file starts with the 17-line GPL header copied from an existing file.
- No PHP 8.1+ syntax. No Symfony 6 APIs. Doctrine docblock annotations only.
- Run `php devTools/core/console.php php-cs-fix` inside the PHP container before committing.
- Commit messages: `OHRM5X-<ticket>: <imperative summary>` (use `OHRM5X-MCP` until a ticket exists).
- Tests live in `src/plugins/orangehrmMcpPlugin/test/`, `@group Mcp`, PHPUnit 9 docblock annotations.
- No MCP code path may reach data without the same permission check and validation the REST endpoint enforces.

## Note on skills (since the 5.9 merge)

Upstream 5.9 introduced `.agents/` with 28 project skills plus a bridge that
populates `.claude/skills/`. This checkout's `.claude/skills/` currently holds the
BMAD skill set instead, so the upstream bridge has **not** been run here. Read
`.agents/skills/README.md` directly when you need an upstream skill, or run
`.agents/SETUP.md` if you want the bridge — check first whether it would disturb the
existing BMAD skills.
