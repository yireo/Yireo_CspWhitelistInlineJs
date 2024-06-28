# Yireo CspWhitelistInlineJs

**When, in Magento 2, the CSP policy to disallow inline scripts is enabled, any script requires either a hash or a nonce (or something similar). This module scans any PHTML template for scripts on the fly and adds a nonce where needed.**

### Installation
```bash
composer require yireo/magento2-csp-whitelist-inline-js
bin/magento module:enable Yireo_CspWhitelistInlineJs
```

### Compatibility
- Luma theme
- Hyvä Themes
- Hyvä Checkout
