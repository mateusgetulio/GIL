const { test, expect } = require( '@playwright/test' );
const { setOptions, resetQueue, expireAdmittedSessions } = require( './helpers' );

test.describe( 'Waiting room gate', () => {
	test.beforeEach( () => {
		setOptions( { limit: 1 } );
		resetQueue();
	} );

	test( 'admits visitors under the limit without lobby markup', async ( { browser } ) => {
		setOptions( { limit: 100 } );
		const context = await browser.newContext();
		const page = await context.newPage();

		const response = await page.goto( '/' );

		expect( response.status() ).toBe( 200 );
		await expect( page.locator( '.gil-card' ) ).toHaveCount( 0 );
		await context.close();
	} );

	test( 'queues visitors over the limit with 503, position, and stable identity', async ( { browser } ) => {
		const contextA = await browser.newContext();
		const pageA = await contextA.newPage();
		const responseA = await pageA.goto( '/' );
		expect( responseA.status() ).toBe( 200 );

		const contextB = await browser.newContext();
		const pageB = await contextB.newPage();
		const responseB = await pageB.goto( '/' );

		expect( responseB.status() ).toBe( 503 );
		expect( responseB.headers()[ 'retry-after' ] ).toBe( '30' );
		expect( responseB.headers()[ 'x-robots-tag' ] ).toContain( 'noindex' );
		await expect( pageB.locator( '.gil-card' ) ).toBeVisible();
		await expect( pageB.locator( '#gil-position' ) ).toHaveText( '1' );

		const contextC = await browser.newContext();
		const pageC = await contextC.newPage();
		const responseC = await pageC.goto( '/' );
		expect( responseC.status() ).toBe( 503 );
		await expect( pageC.locator( '#gil-position' ) ).toHaveText( '2' );

		const responseBReload = await pageB.reload();
		expect( responseBReload.status() ).toBe( 503 );
		await expect( pageB.locator( '#gil-position' ) ).toHaveText( '1' );

		const responseAReload = await pageA.goto( '/' );
		expect( responseAReload.status() ).toBe( 200 );

		await contextA.close();
		await contextB.close();
		await contextC.close();
	} );

	test( 'promotes the first waiting visitor automatically when a session expires', async ( { browser } ) => {
		const contextA = await browser.newContext();
		const pageA = await contextA.newPage();
		await pageA.goto( '/' );

		const contextB = await browser.newContext();
		const pageB = await contextB.newPage();
		const responseB = await pageB.goto( '/' );
		expect( responseB.status() ).toBe( 503 );

		expireAdmittedSessions();

		await pageB.waitForFunction( () => ! document.querySelector( '.gil-card' ), null, { timeout: 30000 } );

		const admittedNow = await pageB.reload();
		expect( admittedNow.status() ).toBe( 200 );

		await contextA.close();
		await contextB.close();
	} );

	test( 'status endpoint reports unknown for visitors without a queue entry', async ( { request } ) => {
		const response = await request.get( '/?rest_route=/get-in-line/v1/status' );
		expect( response.status() ).toBe( 200 );
		expect( await response.json() ).toEqual( { status: 'unknown' } );
	} );
} );
