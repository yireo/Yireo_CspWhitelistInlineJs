# The Playwright tests need the fixture module Yireo_CspWhitelistInlineJsTestFixture, which renders whitelisted and non-whitelisted inline scripts
case "${GITHUB_JOB:-$CI_JOB_NAME}" in playwright*)
  composer config repositories.local-source path /tmp/magento/package-source
  composer config repositories.local-source-fixture path /tmp/magento/package-source/Test/Fixture/CspWhitelistInlineJsTestFixture
  composer require -n -- yireo/magento2-csp-whitelist-inline-js:@dev yireo/magento2-csp-whitelist-inline-js-test-fixture:@dev
  php bin/magento module:enable Yireo_CspUtilities Yireo_CspWhitelistInlineJs Yireo_CspWhitelistInlineJsTestFixture || true
  ;;
esac
