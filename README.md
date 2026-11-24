# Yireo CspWhitelistInlineJs

<!-- badges.specs.start -->
<div class="github-specs-badges">

![Magento version](https://img.shields.io/badge/Magento-2.4.6%20%7C%202.4.9-orange)
![PHP version](https://img.shields.io/badge/PHP-8.2%E2%80%938.5-777BB4)
![License](https://img.shields.io/badge/License-OSL--3.0-blue)
![Latest Version](https://img.shields.io/packagist/v/yireo/magento2-csp-whitelist-inline-js)

</div>
<!-- badges.specs.end -->


**When, in Magento 2, the CSP policy to disallow inline scripts is enabled, any script requires either a hash or a nonce (or something similar). This module scans any PHTML template for scripts on the fly and adds a nonce where needed, depending on your configuration.**

## Installation
```bash
composer require yireo/magento2-csp-whitelist-inline-js
bin/magento module:enable Yireo_CspWhitelistInlineJs
```

## Configuration
- **Mode** (global scope only)
  - `Custom whitelisting` (recommended)
  - `Whitelist everything` (dangerous, ignored in production mode unless explicitly allowed, see below)
- **Logging**: See below

## Which scripts are whitelisted
Only **static inline scripts that are literally present in the template file of the whitelisted block itself** receive a nonce (or hash). This means that:

- Scripts rendered by child blocks are not whitelisted by whitelisting their parent. Whitelisting a container like `head.additional` therefore does not whitelist anything that is added to it;
- Scripts containing PHP output (`<?= ... ?>`) in the template are never whitelisted, because dynamic values could be used to inject code;
- Scripts with a `src` attribute, an existing `nonce` attribute or a non-JavaScript `type` are skipped (see `Yireo_CspUtilities`);
- Blocks without a template file are skipped.

Scripts that need dynamic data should get CSP-support in their own template instead (for instance, by using the `\Magento\Framework\View\Helper\SecureHtmlRenderer`).

## Mode: Custom whitelisting
A block can be whitelisted by this module, using one of the following techniques:

### 1. Custom whitelisting via DI (recommended)
Add the name of the block to the `allowedBlocks` argument of `\Yireo\CspWhitelistInlineJs\Util\AllowBlock` in the `etc/frontend/di.xml` file of your own module:
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="Yireo\CspWhitelistInlineJs\Util\AllowBlock">
        <arguments>
            <argument name="allowedBlocks" xsi:type="array">
                <item name="example" xsi:type="string">example.block.name</item>
            </argument>
        </arguments>
    </type>
</config>
```

Because this configuration lives in code, it can not be modified via the database.

### 2. Custom whitelisting by XML layout
You can also whitelist a specific `block` via the XML layout, by adding a boolean argument `csp_whitelist`:
```xml
<?xml version="1.0"?>
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:View/Layout:etc/page_configuration.xsd">
    <body>
        <referenceBlock name="example.block.name">
            <arguments>
                <argument name="csp_whitelist" xsi:type="boolean">true</argument>
            </arguments>
        </referenceBlock>
    </body>
</page>
```

Only a real boolean `true` is accepted, so string values (as used in CMS widgets or `{{block}}` directives) do not work.

**Note that there is still a security issue here, because the layout could be updated via the database as well (which could be hijacked via SQL injection). Prefer whitelisting via DI.**

### 3. Via DI plugin
You could also create a DI plugin `afterAllow` on `\Yireo\CspWhitelistInlineJs\Util\AllowBlock::allow(Template $block)`.

## Mode: Whitelist everything
With this mode, the static inline scripts of any template are allowed. It defeats the purpose of CSP, in general, but gets the job done. Do make sure to log things, so that you can use a different mode after a while. If you keep using this mode in production, you don't get it.

In production mode, this mode is ignored (and `Custom whitelisting` is used instead), unless it is explicitly allowed in the `app/etc/env.php` file:
```php
'csp_whitelist_inline_js' => [
    'allow_all_in_production' => 1,
],
```

### Important note on whitelisting everything & security
Note that automatically fixing inline scripts with this module does **not** take away the security risk that was meant to be fixed with CSP. The best solution is to apply this module to make sure CSP is not causing harm in production. And next, go through all of the inline scripts that don't have CSP-support yet and add CSP-support to it. **And then remove this module again.**

### What about CMS content?
Whenever content is added to the HTML output of Magento, security checks would need to be in place. For instance, the content of CMS pages or CMS blocks might be compromised by hackers and with that, XSS attacks might be initiated by adding inline scripts to that content. By default, this module does **not** do anything specific for that CMS content. However, in a default situation, CMS pages and CMS blocks are not being outputted via templates and therefore an inline script in CMS content is not nonced by this module (which is a good thing). While the default is safe, any modification (custom theming, 3rd party modules, etc) could lead into compromised CMS content being outputted with nonced scripts anyway.

## Full Page Cache
When CSP nonces are used (the default `Inline Script Mode` of `Yireo_CspUtilities`), these nonces become part of the cached HTML when the Full Page Cache is enabled. All visitors would then share the same nonce, which makes the nonce predictable. Either install the module [Yireo_PageCacheCspNonceRegenerator](https://github.com/yireo/Yireo_PageCacheCspNonceRegenerator) (built-in Full Page Cache only) or switch the `Inline Script Mode` to `hash` (required for Varnish). A warning is shown in the Magento Admin Panel when this situation is detected.

## Logging
This module also allows logging of these inline scripts. Just enable logging via **Yireo > Yireo CspWhitelistInlineJs > Settings > Logging** (`csp_whitelist_inline_js/settings/logging`). Next, monitor the logfile `var/log/yireo-csp-whitelist-inline-js.log`. The log contains a listing of all block templates (relative to the Magento root) of which inline scripts have been whitelisted by this module.

The recommendation is to go through this file and make sure that each template is whitelisted by the extension or theme that is offering that template, so that - one day in the future - you are able to remove this `Yireo_CspWhitelistInlineJs` module.

Note that this file is regenerated at the end of every request (in which new templates were found) by combining all existing lines with new lines.

## Compatibility
- Luma theme
- Hyvä Themes
- Hyvä Checkout

## Current status

<!-- badges.test.start -->
<div class="github-pipeline-badges">

![Static Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_CspWhitelistInlineJs/static-tests.yml?label=static-tests)
![Unit Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_CspWhitelistInlineJs/unit-tests.yml?label=unit-tests)
![Integration Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_CspWhitelistInlineJs/integration-tests.yml?label=integration-tests)
![Playwright](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_CspWhitelistInlineJs/playwright.yml?label=playwright)
![DI Compilation](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_CspWhitelistInlineJs/compile.yml?label=compile)

</div>
<!-- badges.test.end -->
