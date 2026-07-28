const { test, expect } = require( '@playwright/test' );
const { setOptions, resetQueue, loginAsAdmin } = require( './helpers' );

test.describe( 'Administrator bypass', () => {
	test.beforeEach( () => {
		setOptions( { limit: 1 } );
		resetQueue();
	} );

	test( 'a logged-in administrator is never gated, even at capacity', async ( { browser, page } ) => {
		const visitorContext = await browser.newContext();
		const visitorPage = await visitorContext.newPage();
		const visitorResponse = await visitorPage.goto( '/' );
		expect( visitorResponse.status() ).toBe( 200 );

		const secondContext = await browser.newContext();
		const secondPage = await secondContext.newPage();
		const secondResponse = await secondPage.goto( '/' );
		expect( secondResponse.status() ).toBe( 503 );

		await loginAsAdmin( page );
		const adminResponse = await page.goto( '/' );

		expect( adminResponse.status() ).toBe( 200 );
		await expect( page.locator( '.gil-card' ) ).toHaveCount( 0 );

		await visitorContext.close();
		await secondContext.close();
	} );
} );
