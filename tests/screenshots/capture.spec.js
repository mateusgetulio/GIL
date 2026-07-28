const { test } = require( '@playwright/test' );
const { setOptions, resetQueue, loginAsAdmin } = require( '../e2e/helpers' );

const OUTPUT_DIR = 'docs/screenshots';

test.describe( 'Screenshot capture', () => {
	test.beforeEach( () => {
		setOptions( { limit: 1 } );
		resetQueue();
	} );

	test( 'lobby, desktop and mobile', async ( { browser } ) => {
		const admittedContext = await browser.newContext();
		const admittedPage = await admittedContext.newPage();
		await admittedPage.goto( '/' );

		const desktopContext = await browser.newContext( { viewport: { width: 1280, height: 800 } } );
		const desktopPage = await desktopContext.newPage();
		await desktopPage.goto( '/' );
		await desktopPage.screenshot( { path: `${ OUTPUT_DIR }/lobby.png` } );
		await desktopContext.close();

		const mobileContext = await browser.newContext( { viewport: { width: 390, height: 844 } } );
		const mobilePage = await mobileContext.newPage();
		await mobilePage.goto( '/' );
		await mobilePage.screenshot( { path: `${ OUTPUT_DIR }/lobby-mobile.png` } );
		await mobileContext.close();

		await admittedContext.close();
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
