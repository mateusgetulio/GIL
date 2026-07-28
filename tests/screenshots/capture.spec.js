const { test } = require( '@playwright/test' );
const { wpCli, setOptions, resetQueue, loginAsAdmin } = require( '../e2e/helpers' );

const OUTPUT_DIR = 'docs/screenshots';

test.describe( 'Screenshot capture', () => {
	test.beforeEach( () => {
		setOptions( { limit: 1 } );
		resetQueue();
	} );

	test( 'lobby via preview, desktop and mobile', async ( { page } ) => {
		wpCli( [ 'option', 'update', 'blogname', 'Northwind Records' ] );
		try {
			await loginAsAdmin( page );
			await page.goto( '/wp-admin/admin.php?page=get-in-line' );
			const previewUrl = await page.locator( '.gil-preview-link' ).getAttribute( 'href' );

			await page.setViewportSize( { width: 1280, height: 800 } );
			await page.emulateMedia( { colorScheme: 'dark' } );
			await page.goto( previewUrl );
			await page.screenshot( { path: `${ OUTPUT_DIR }/lobby.png` } );

			await page.emulateMedia( { colorScheme: 'light' } );
			await page.screenshot( { path: `${ OUTPUT_DIR }/lobby-light.png` } );

			await page.emulateMedia( { colorScheme: 'dark' } );
			await page.setViewportSize( { width: 390, height: 844 } );
			await page.screenshot( { path: `${ OUTPUT_DIR }/lobby-mobile.png` } );
		} finally {
			wpCli( [ 'option', 'update', 'blogname', 'GIL' ] );
		}
	} );

	test( 'settings page with a live queue', async ( { browser, page } ) => {
		const admittedContext = await browser.newContext();
		const admittedPage = await admittedContext.newPage();
		await admittedPage.goto( '/' );

		for ( let i = 0; i < 3; i++ ) {
			const waitingContext = await browser.newContext();
			const waitingPage = await waitingContext.newPage();
			await waitingPage.goto( '/' );
			await waitingContext.close();
		}

		await loginAsAdmin( page );
		await page.setViewportSize( { width: 1280, height: 900 } );
		await page.goto( '/wp-admin/admin.php?page=get-in-line' );
		await page.screenshot( { path: `${ OUTPUT_DIR }/settings.png` } );

		await admittedContext.close();
	} );
} );
