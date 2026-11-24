import {
    test,
    expect,
    configureCsp,
    openHomepage,
    getEnforcedScriptSrc,
    getFixtureScript,
    getExternalFixtureScriptNonce,
    expectScriptExecuted,
    expectScriptNotExecuted,
    fixtureScripts,
    unsafeFixtureScripts,
    externalFixtureScript,
    sha256,
    WhitelistMode,
} from './csp-whitelist';
import {Page} from '@playwright/test';

async function expectUnsafeScriptsBlocked(page: Page) {
    for (const datasetKey of Object.values(unsafeFixtureScripts)) {
        const script = await getFixtureScript(page, datasetKey);
        expect(script.nonce, `Nonce of ${datasetKey}`).toBe('');
        await expectScriptNotExecuted(page, datasetKey);
    }

    expect(await getExternalFixtureScriptNonce(page, externalFixtureScript), 'Nonce of external script').toBe('');
    await expectScriptNotExecuted(page, externalFixtureScript);
}

/**
 * Shared test cases for Luma and Hyva, so both themes are covered with the very same assertions
 */
export function defineCspWhitelistInlineJsTests(theme: string) {
    test('adds a nonce only to whitelisted blocks in custom mode', async function ({page}) {
        await configureCsp(page, theme, 'custom');
        const response = await openHomepage(page);
        const scriptSrc = await getEnforcedScriptSrc(response);

        for (const datasetKey of [fixtureScripts.whitelisted, fixtureScripts.diWhitelisted]) {
            const script = await getFixtureScript(page, datasetKey);
            expect(script.nonce, `Nonce of ${datasetKey}`).not.toBe('');
            expect(scriptSrc).toContain(`'nonce-${script.nonce}'`);
            await expectScriptExecuted(page, datasetKey);
        }

        for (const datasetKey of [fixtureScripts.notWhitelisted, fixtureScripts.headChild]) {
            const script = await getFixtureScript(page, datasetKey);
            expect(script.nonce, `Nonce of ${datasetKey}`).toBe('');
            await expectScriptNotExecuted(page, datasetKey);
        }
    });

    test('does not add a nonce to children of a whitelisted block in custom mode', async function ({page}) {
        await configureCsp(page, theme, 'custom');
        await openHomepage(page);

        const child = await getFixtureScript(page, fixtureScripts.whitelistedChild);
        expect(child.nonce, 'Nonce of child script').toBe('');
        await expectScriptNotExecuted(page, fixtureScripts.whitelistedChild);
    });

    for (const mode of ['custom', 'all'] as WhitelistMode[]) {
        test(`does not add a nonce to dynamic or external scripts in mode ${mode}`, async function ({page}) {
            await configureCsp(page, theme, mode);
            await openHomepage(page);
            await expectUnsafeScriptsBlocked(page);
        });
    }

    test('adds a nonce to static scripts of all blocks in mode all', async function ({page}) {
        await configureCsp(page, theme, 'all');
        const response = await openHomepage(page);
        const scriptSrc = await getEnforcedScriptSrc(response);

        for (const datasetKey of Object.values(fixtureScripts)) {
            const script = await getFixtureScript(page, datasetKey);
            expect(script.nonce, `Nonce of ${datasetKey}`).not.toBe('');
            expect(scriptSrc).toContain(`'nonce-${script.nonce}'`);
            await expectScriptExecuted(page, datasetKey);
        }
    });

    test('adds a hash only for whitelisted blocks in custom mode with hashes', async function ({page}) {
        await configureCsp(page, theme, 'custom', 'hash');
        const response = await openHomepage(page);
        const scriptSrc = await getEnforcedScriptSrc(response);

        const whitelisted = await getFixtureScript(page, fixtureScripts.whitelisted);
        expect(whitelisted.nonce, 'Nonce of whitelisted script').toBe('');
        expect(scriptSrc, 'Hash of whitelisted script').toContain(`'sha256-${sha256(whitelisted.content)}'`);
        await expectScriptExecuted(page, fixtureScripts.whitelisted);

        for (const datasetKey of [fixtureScripts.notWhitelisted, fixtureScripts.whitelistedChild, unsafeFixtureScripts.dynamic]) {
            const script = await getFixtureScript(page, datasetKey);
            expect(scriptSrc, `Hash of ${datasetKey}`).not.toContain(`'sha256-${sha256(script.content)}'`);
            await expectScriptNotExecuted(page, datasetKey);
        }
    });
}
