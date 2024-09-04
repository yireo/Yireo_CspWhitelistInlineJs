# Yireo CspWhitelistInlineJs

**When, in Magento 2, the CSP policy to disallow inline scripts is enabled, any script requires either a hash or a nonce (or something similar). This module scans any PHTML template for scripts on the fly and adds a nonce where needed, depending on your configuration.**

## Installation
```bash
composer require yireo/magento2-csp-whitelist-inline-js
bin/magento module:enable Yireo_CspWhitelistInlineJs
```

## Configuration
- **Mode**
  - `Custom whitelisting` (recommended)
  - `Whitelist everything` (dangerous)
- **Logging**: See below

## Mode: Custom whitelisting
A block can be whitelisted automatically by this module, using the following techniques:

### Custom whitelisting by XML layout
You can whitelist a specific `block` via the XML layout, by adding an argument `csp_whitelist`:
```xml
<?xml version="1.0"?>
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:View/Layout:etc/page_configuration.xsd">
    <body>
        <referenceBlock name="head.additional">
            <arguments>
                <argument name="csp_whitelist" xsi:type="boolean">true</argument>
            </arguments>
        </referenceBlock>
    </body>
</page>
```

You could also create a DI plugin `afterAllow` on `\Yireo\CspWhitelistInlineJs\Util\AllowBlock::allow(Template $block)`.

## Mode: Whitelist everything
With this mode, any inline script is simply allowed. It defeats the purpose of CSP, in general, but gets the job done. Do make sure to log things, so that you can use a different mode after a while. If you keep using this mode in production, you don't get it.

### Important note on whitelisting everything & security
Note that automatically fixing inline scripts with this module does **not** take away the security risk that was meant to be fixed with CSP. The best solution is to apply this module to make sure CSP is not causing harm in production. And next, go through all of the inline scripts that don't have CSP-support yet and add CSP-support to it. **And then remove this module again.**

### What about CMS content?
Whenever content is added to the HTML output of Magento, security checks would need to be in place. For instance, the content of CMS pages or CMS blocks might be compromised by hackers and with that, XSS attacks might be initiated by adding inline scripts to that content. By default, this module does **not** do anything specific for that CMS content. However, in a default situation, CMS pages and CMS blocks are not being outputted via templates and therefore an inline script in CMS content is not nonced by this module (which is a good thing). While the default is safe, any modification (custom theming, 3rd party modules, etc) could lead into compromised CMS content being outputted with nonced scripts anyway.

## Logging
This module also allows logging of these inline scripts. Just enable logging via **Yireo > Yireo CspWhitelistInlineJs > Settings > Logging** (`csp_whitelist_inline_js/settings/logging`). Next, monitor the logfile `var/log/yireo-csp-whitelist-inline-js.log`. The log contains a listing of all block templates that have been picked up upon by this module.

The recommendation is to go through this file and make sure that each template is whitelisted by the extension or theme that is offering that template, so that - one day in the future - you are able to remove this `Yireo_CspWhitelistInlineJs` module.

Note that this file is regenerated at the end of every request by combining all existing lines with new lines.

## Compatibility
- Luma theme
- Hyvä Themes
- Hyvä Checkout
