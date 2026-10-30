import {test, LUMA_THEME} from './lib/csp-whitelist';
import {defineCspWhitelistInlineJsTests} from './lib/csp-whitelist-tests';

test.describe('CSP whitelist inline JS (Luma)', function () {
    defineCspWhitelistInlineJsTests(LUMA_THEME);
});
