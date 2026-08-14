# Contributing to Saucebase

Thank you for improving Saucebase. Contributions may target the framework-neutral
core, one or both frontend stacks, documentation, tests, or module tooling.

## Before you start

Requirements are defined by the manifests:

- PHP `^8.4` and Composer dependencies in `composer.json`
- Node `>=22.12.0` and npm `>=10.5.1` in each stack manifest
- Vue and React dependencies in `stubs/saucebase/stack/vue/package.json` and
  `stubs/saucebase/stack/react/package.json`

The root `package.json` is intentionally minimal before a frontend is selected.
Use the Saucebase CLI to prepare a contributor stack:

```bash
saucebase stack vue --dev
# or
saucebase stack react --dev
```

Contributor mode retains both framework source trees and generates thin root
entry-point passthroughs. Edit `resources/js/vue/` or `resources/js/react/`,
not generated root entry points. Shared frontend changes must work with both
stacks.

Install dependencies and prepare the application:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
```

Docker users may instead run:

```bash
docker compose up -d --wait
docker compose exec app composer install
docker compose exec app php artisan migrate
npm install
```

## Development

```bash
composer dev                         # app, queue, logs, and Vite
npm run dev                          # Vite only
npm run build                        # production client and SSR builds
```

Available npm scripts depend on the selected stack. The Vue and React stack
manifests are the source of truth for frontend commands.

## Modules

Create a module from the maintained recipe:

```bash
php artisan saucebase:recipe example
composer dump-autoload
npm run build
```

Module directories are lowercase, while PHP namespaces are TitleCase. Main
module providers extend `App\Providers\ModuleServiceProvider`; `$name` and
`$nameLower` properties are obsolete because the base provider resolves the
module name through InterNACHI's `ModuleRegistry`.

Do not bypass `module-loader.js`. It discovers module assets, translations, and
Playwright projects. See
`.ai/guidelines/saucebase/saucebase-core.md` for the maintained module patterns.

## Tests and quality checks

Run the smallest relevant checks while developing, then broaden coverage before
submitting:

```bash
php artisan test
php -d memory_limit=2048M artisan test --testsuite=Modules
composer analyse                      # Larastan/PHPStan level 5
composer lint                         # Laravel Pint
npm run lint
npm run format:check
npm run test:e2e
npm run build
```

Some npm commands are unavailable until a frontend stack is selected. User-facing
work should include feature or E2E coverage; complex isolated logic should
include unit coverage. E2E tests must select stable `data-testid` attributes,
not translated text.

## Documentation

Documentation changes must be checked against implementation and manifests:

- `README.md` owns the public overview, installation links, and supported stack
  lines.
- `CONTRIBUTING.md` owns contributor setup and verification workflows.
- `.ai/guidelines/` owns always-loaded agent conventions.
- `.ai/skills/` owns task-specific agent workflows.
- `AGENTS.md`, `CLAUDE.md`, and `boost.json` are local Laravel Boost outputs
  and are not tracked. Run `php artisan boost:install` once after cloning.

Avoid repeating version tables across these files. When a dependency changes,
update its manifest first and adjust only documentation that describes the
affected supported version line.

Change agent instructions in `.ai/`, then regenerate your local outputs:

```bash
composer boost:update
```

Never edit the `<laravel-boost-guidelines>` blocks in `AGENTS.md` or
`CLAUDE.md` directly — they are overwritten on every regeneration, and they
are not tracked, so the edit reaches nobody. `.ai/` is the only source.

To pick up guidelines newly shipped by a package or module, run the Artisan
command directly. Composer sets `COMPOSER_DEV_MODE`, which suppresses the
discovery prompt, so `composer boost:update` cannot add new packages:

```bash
php artisan boost:update
```

## Commits and pull requests

Use a lowercase, single-line Conventional Commit:

```text
type(scope): subject
```

The scope is optional. Allowed types are `feat`, `fix`, `docs`, `style`,
`refactor`, `perf`, `test`, `chore`, `ci`, `build`, and `revert`. Keep the
header at or below 150 characters and do not add a body or footer. See
`.github/COMMIT_CONVENTION.md` for examples.

Pull requests should explain the problem and solution, identify affected stacks
or modules, and list the checks run. Include screenshots for visible UI changes
and keep unrelated changes out of the branch.
