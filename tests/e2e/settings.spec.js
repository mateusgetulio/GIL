const { test, expect } = require( '@playwright/test' );
const { setOptions, resetQueue, loginAsAdmin } = require( './helpers' );

const SETTINGS_URL = '/wp-admin/admin.php?page=get-in-line';

test.describe( 'Settings page', () => {
	test.beforeEach( async ( { page } ) => {
		setOptions( { limit: 100, expiration: 20 } );
		resetQueue();
		await loginAsAdmin( page );
	} );

	test( 'shows current values, status line, and live queue counts', async ( { page } ) => {
		await page.goto( SETTINGS_URL );

		await expect( page.locator( '.gil-status-line' ) ).toContainText( 'Waiting room is active' );
		await expect( page.locator( '#gil_enabled' ) ).toBeChecked();
		await expect( page.locator( '#gil_limit' ) ).toHaveValue( '100' );
		await expect( page.locator( '#gil_expiration' ) ).toHaveValue( '20' );
		await expect( page.locator( '#gil-admitted-count' ) ).toHaveText( '0' );
		await expect( page.locator( '#gil-waiting-count' ) ).toHaveText( '0' );
	} );

	test( 'preview link opens the lobby with sample data without enqueuing anyone', async ( { page, context } ) => {
		await page.goto( SETTINGS_URL );

		const [ previewPage ] = await Promise.all( [
			context.waitForEvent( 'page' ),
			page.click( '.gil-preview-link' ),
		] );

		await expect( previewPage.locator( '.gil-card' ) ).toBeVisible();
		await expect( previewPage.locator( '#gil-position' ) ).toHaveText( '47' );
		await expect( previewPage.locator( '#gil-wait' ) ).toHaveText( '12' );
		await previewPage.close();

		await page.reload();
		await expect( page.locator( '#gil-admitted-count' ) ).toHaveText( '0' );
		await expect( page.locator( '#gil-waiting-count' ) ).toHaveText( '0' );
	} );

	test( 'saves valid settings and confirms', async ( { page } ) => {
		await page.goto( SETTINGS_URL );

		await page.fill( '#gil_limit', '7' );
		await page.fill( '#gil_expiration', '12' );
		await page.click( '#submit' );

		await expect( page.locator( '.notice-success' ) ).toContainText( 'Settings saved.' );
		await expect( page.locator( '#gil_limit' ) ).toHaveValue( '7' );
		await expect( page.locator( '#gil_expiration' ) ).toHaveValue( '12' );
	} );

	test( 'rejects a zero limit server-side and keeps the previous value', async ( { page } ) => {
		await page.goto( SETTINGS_URL );

		await page.evaluate( () => document.getElementById( 'gil_limit' ).removeAttribute( 'min' ) );
		await page.fill( '#gil_limit', '0' );
		await page.click( '#submit' );

		await expect( page.locator( '.notice-error' ) ).toContainText( 'The visitor limit must be a whole number greater than zero.' );
		await expect( page.locator( '#gil_limit' ) ).toHaveValue( '100' );
	} );
} );
