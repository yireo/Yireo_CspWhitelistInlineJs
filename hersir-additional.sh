#!/bin/bash
# Install the fixture module Yireo_CspWhitelistInlineJsTestFixture, needed by the Playwright tests (never in production)
set -e
MODULE_DIR=$(cd "$(dirname "$(readlink -f "$0")")" && pwd)

composer config repositories.yireo-csp-whitelist-inline-js-test-fixture path "$MODULE_DIR/Test/Fixture/CspWhitelistInlineJsTestFixture"
composer show yireo/magento2-csp-whitelist-inline-js-test-fixture >/dev/null 2>&1 \
  || composer require -n -- yireo/magento2-csp-whitelist-inline-js-test-fixture:@dev
bin/magento module:enable Yireo_CspWhitelistInlineJsTestFixture
