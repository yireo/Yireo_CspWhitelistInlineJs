# Yireo CspWhitelistInlineJs

**When, in Magento 2, the CSP policy to disallow inline scripts is enabled, any script requires either a hash or a nonce (or something similar). This module scans any PHTML template for scripts on the fly and adds a nonce where needed.**

### Installation
```bash
composer require yireo/magento2-csp-whitelist-inline-js
bin/magento module:enable Yireo_CspWhitelistInlineJs
```

Note that automatically fixing inline scripts with this module does **not** take away the security risk that was meant to be fixed with CSP. The best solution is to apply this module to make sure CSP is not causing harm in production. And next, go through all of the inline scripts that don't have CSP-support yet and add CSP-support to it.

This module also allows logging of these inline scripts. Just enable logging via **Yireo > Yireo CspWhitelistInlineJs > Settings > Logging** (`csp_whitelist_inline_js/settings/logging`). Next, monitor the logfile `var/log/yireo-csp-whitelist-inline-js.log`. 

### Compatibility
- Luma theme
- Hyvä Themes
- Hyvä Checkout
