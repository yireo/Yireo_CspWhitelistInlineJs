import {test, HYVA_THEME} from './lib/csp-whitelist';
import {defineCspWhitelistInlineJsTests} from './lib/csp-whitelist-tests';

test.describe('CSP whitelist inline JS (Hyva)', function () {
    defineCspWhitelistInlineJsTests(HYVA_THEME);
});
