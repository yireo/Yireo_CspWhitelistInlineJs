import {
    test,
    expect,
    configureCsp,
    openHomepage,
    getEnforcedScriptSrc,
    getFixtureScript,
    expectScriptExecuted,
    expectScriptNotExecuted,
    fixtureScripts,
    sha256,
} from './csp-whitelist';

/**
 * Shared test cases for Luma and Hyva, so both themes are covered with the very same assertions
 */
export function defineCspWhitelistInlineJsTests(theme: string) {
    test('adds a nonce only to whitelisted blocks in custom mode', async function ({page}) {
        await configureCsp(page, theme, 'custom');
        const response = await openHomepage(page);
        const scriptSrc = await getEnforcedScriptSrc(response);

        const whitelisted = await getFixtureScript(page, fixtureScripts.whitelisted);
        expect(whitelisted.nonce, 'Nonce of whitelisted script').not.toBe('');
        expect(scriptSrc).toContain(`'nonce-${whitelisted.nonce}'`);
        await expectScriptExecuted(page, fixtureScripts.whitelisted);

        const notWhitelisted = await getFixtureScript(page, fixtureScripts.notWhitelisted);
        expect(notWhitelisted.nonce, 'Nonce of non-whitelisted script').toBe('');
        await expectScriptNotExecuted(page, fixtureScripts.notWhitelisted);
    });

    test('adds a nonce to children of head.additional in custom mode', async function ({page}) {
        await configureCsp(page, theme, 'custom');
        const response = await openHomepage(page);
        const scriptSrc = await getEnforcedScriptSrc(response);

        const headChild = await getFixtureScript(page, fixtureScripts.headChild);
        expect(headChild.nonce, 'Nonce of head.additional child script').not.toBe('');
        expect(scriptSrc).toContain(`'nonce-${headChild.nonce}'`);
        await expectScriptExecuted(page, fixtureScripts.headChild);
    });

    test('adds a nonce to all blocks in mode all', async function ({page}) {
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

        const notWhitelisted = await getFixtureScript(page, fixtureScripts.notWhitelisted);
        expect(scriptSrc, 'Hash of non-whitelisted script').not.toContain(`'sha256-${sha256(notWhitelisted.content)}'`);
        await expectScriptNotExecuted(page, fixtureScripts.notWhitelisted);
    });
}
