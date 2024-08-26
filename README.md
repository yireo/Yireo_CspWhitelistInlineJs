# Yireo CspWhitelistInlineJs

**When, in Magento 2, the CSP policy to disallow inline scripts is enabled, any script requires either a hash or a nonce (or something similar). This module scans any PHTML template for scripts on the fly and adds a nonce where needed.**

### Installation
```bash
composer require yireo/magento2-csp-whitelist-inline-js
bin/magento module:enable Yireo_CspWhitelistInlineJs
```

### Important note on security
Note that automatically fixing inline scripts with this module does **not** take away the security risk that was meant to be fixed with CSP. The best solution is to apply this module to make sure CSP is not causing harm in production. And next, go through all of the inline scripts that don't have CSP-support yet and add CSP-support to it.

### What about CMS content?
Whenever content is added to the HTML output of Magento, security checks would need to be in place. For instance, the content of CMS pages or CMS blocks might be compromised by hackers and with that, XSS attacks might be initiated by adding inline scripts to that content. By default, this module does **not** do anything specific for that CMS content. However, in a default situation, CMS pages and CMS blocks are not being outputted via templates and therefore an inline script in CMS content is not nonced by this module (which is a good thing). While the default is safe, any modification (custom theming, 3rd party modules, etc) could lead into compromised CMS content being outputted with nonced scripts anyway.

### Logging
This module also allows logging of these inline scripts. Just enable logging via **Yireo > Yireo CspWhitelistInlineJs > Settings > Logging** (`csp_whitelist_inline_js/settings/logging`). Next, monitor the logfile `var/log/yireo-csp-whitelist-inline-js.log`. 

### Compatibility
- Luma theme
- Hyvä Themes
- Hyvä Checkout
