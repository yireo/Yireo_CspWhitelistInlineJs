import {test, expect, Page, Response} from '@playwright/test';
import {createHash} from 'crypto';

export const HYVA_THEME = 'Hyva/default-csp';
export const LUMA_THEME = 'Magento/luma';

export type WhitelistMode = 'custom' | 'all';
export type InlineScriptMode = 'nonce' | 'hash';

export const fixtureScripts = {
    whitelisted: 'cspWhitelistWhitelisted',
    notWhitelisted: 'cspWhitelistNotWhitelisted',
    headChild: 'cspWhitelistHeadChild',
};

const token = () => process.env.TEST_TOKEN;

/**
 * Configure the whitelist mode, the inline script mode and the theme, with CSP enforced on the storefront
 *
 * Inline scripts are disallowed, because Magento leaves out all nonces and hashes when 'unsafe-inline' is allowed.
 *
 * The Loki_FunctionalTests configure endpoint resets the theme to Magento/luma when `theme` is missing,
 * so the theme is passed with every call.
 */
export async function configureCsp(page: Page, theme: string, mode: WhitelistMode, inlineScriptMode: InlineScriptMode = 'nonce') {
    const response = await page.request.post('/loki_functional_tests/index/configure?token=' + token(), {
        form: {
            config: JSON.stringify({
                theme,
                config: {
                    'csp_whitelist_inline_js/settings/mode': mode,
                    'csp_utilities/settings/inline_script_mode': inlineScriptMode,
                    'csp/mode/storefront/report_only': 0,
                    'csp/policies/storefront/scripts/inline': 0,
                },
            }),
        },
    });

    const body = await response.text();
    let data: any;
    try {
        data = JSON.parse(body);
    } catch (error) {
        throw new Error(`Configure endpoint returned no JSON (status ${response.status()}): ${body.slice(0, 500)}`);
    }

    if (data.error) {
        throw new Error('Configure error: ' + data.error);
    }

    const themeErrors: string[] = data.result?.theme?.errors ?? [];
    test.skip(themeErrors.some(error => error.includes('not found')), `Theme "${theme}" is not installed`);
    expect(themeErrors, 'Configure theme errors').toEqual([]);
}

/**
 * Open the homepage, bypassing the full page cache, so the CSP header belongs to this very rendering
 */
export async function openHomepage(page: Page): Promise<Response> {
    const response = await page.goto('/?csp_whitelist_inline_js=' + Date.now());
    expect(response, 'Homepage response').not.toBeNull();
    expect(response!.status()).toBe(200);
    await page.waitForLoadState('load');
    return response!;
}

export async function getEnforcedScriptSrc(response: Response): Promise<string> {
    const headers = await response.headersArray();
    const policies = headers
        .filter(header => header.name.toLowerCase() === 'content-security-policy')
        .map(header => header.value);
    expect(policies.length, 'Enforced Content-Security-Policy header').toBeGreaterThan(0);

    const scriptSrc = policies.join(';')
        .split(';')
        .map(directive => directive.trim())
        .filter(directive => directive.startsWith('script-src '));
    expect(scriptSrc.length, 'script-src directive').toBeGreaterThan(0);

    return scriptSrc.join(' ');
}

/**
 * Return the fixture script that sets the given body dataset key
 */
export async function getFixtureScript(page: Page, datasetKey: string): Promise<{content: string, nonce: string}> {
    const scripts = await page.evaluate(key => Array.from(document.querySelectorAll('script'))
        .filter(script => (script.textContent ?? '').includes(`document.body.dataset.${key} = `))
        .map(script => ({content: script.textContent ?? '', nonce: script.nonce})), datasetKey);
    expect(scripts.length, `Fixture script for ${datasetKey} (is Yireo_CspWhitelistInlineJsTestFixture enabled?)`).toBe(1);

    return scripts[0];
}

export async function expectScriptExecuted(page: Page, datasetKey: string) {
    await expect.poll(
        () => page.evaluate(key => document.body.dataset[key], datasetKey),
        `Inline script ${datasetKey} was executed`
    ).toBe('executed');
}

export async function expectScriptNotExecuted(page: Page, datasetKey: string) {
    expect(
        await page.evaluate(key => document.body.dataset[key], datasetKey),
        `Inline script ${datasetKey} was blocked by CSP`
    ).toBeUndefined();
}

export function sha256(content: string): string {
    return createHash('sha256').update(content, 'utf8').digest('base64');
}

export {test, expect};
