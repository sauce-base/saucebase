# Commit Message Convention

Saucebase uses a restricted form of Conventional Commits:

```text
type(scope): subject
```

The scope is optional. The type, scope, and subject must be lowercase, the
commit must be a single line with no body or footer, and the complete header
must not exceed 150 characters.

Allowed types:

- `feat`
- `fix`
- `docs`
- `style`
- `refactor`
- `perf`
- `test`
- `chore`
- `ci`
- `build`
- `revert`

Examples:

```text
feat(auth): add magic link login
fix: prevent duplicate module registration
docs: align supported dependency versions
test(e2e): cover login validation
```

Create commits directly:

```bash
git commit -m "docs: align supported dependency versions"
```

Do not use breaking-change footers or multi-line messages; this repository's
commit policy deliberately disallows them.
