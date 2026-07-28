const { defineConfig } = require( '@playwright/test' );

module.exports = defineConfig( {
	testDir: './tests/screenshots',
	workers: 1,
	timeout: 60000,
	reporter: [ [ 'list' ] ],
	use: {
		baseURL: process.env.WP_BASE_URL || 'http://localhost:8889',
		headless: true,
		deviceScaleFactor: 2,
	},
} );
