const { defineConfig } = require( '@playwright/test' );

module.exports = defineConfig( {
	testDir: './tests/e2e',
	workers: 1,
	fullyParallel: false,
	timeout: 60000,
	retries: 0,
	reporter: [ [ 'list' ] ],
	use: {
		baseURL: process.env.WP_BASE_URL || 'http://localhost:8889',
		headless: true,
	},
} );
