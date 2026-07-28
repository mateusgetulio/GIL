const { test, expect } = require( '@playwright/test' );
const { setOptions, resetQueue, loginAsAdmin, queueCounts } = require( './helpers' );

const SETTINGS_URL = '/wp-admin/admin.php?page=gil-waiting-room';

test.describe( 'Clear queue', () => {
	test.beforeEach( () => {
		setOptions( { limit: 1 } );
		resetQueue();
	} );

	test( 'empties admitted sessions and the waiting line from the settings page', async ( { browser, page } ) => {
		const contextA = await browser.newContext();
		const pageA = await contextA.newPage();
		await pageA.goto( '/' );

		const contextB = await browser.newContext();
		const pageB = await contextB.newPage();
		await pageB.goto( '/' );

		await loginAsAdmin( page );
		await page.goto( SETTINGS_URL );

		await expect( page.locator( '#gil-admitted-count' ) ).toHaveText( '1' );
		await expect( page.locator( '#gil-waiting-count' ) ).toHaveText( '1' );

		page.on( 'dialog', ( dialog ) => dialog.accept() );
		await page.click( '#gil-clear-queue' );

		await expect( page.locator( '.notice-success' ) ).toContainText( 'The queue has been cleared.' );
		await expect( page.locator( '#gil-admitted-count' ) ).toHaveText( '0' );
		await expect( page.locator( '#gil-waiting-count' ) ).toHaveText( '0' );
		expect( queueCounts() ).toEqual( { admitted: 0, waiting: 0 } );

		await contextA.close();
		await contextB.close();
	} );
} );
