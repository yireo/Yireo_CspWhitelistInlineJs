# MEMORY

- CI Playwright job: `.github/workflows/composer-additional.sh` runs inside the PHP service container via `docker exec` and only sees env vars passed explicitly with `-e`. It relies on `GITHUB_JOB` to install the fixture module `Yireo_CspWhitelistInlineJsTestFixture`, so `playwright.yml` must pass `-e GITHUB_JOB="$GITHUB_JOB"` in the "Configure composer" step.
- Local CI check: `PATH=$HOME/bin:$PATH act -j playwright -P ubuntu-latest=catthehacker/ubuntu:act-latest --container-daemon-socket - --secret-file ~/bin/env-loki-functional-tests --insecure-secrets` (verified working).
