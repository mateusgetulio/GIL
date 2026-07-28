const { execFileSync } = require( 'child_process' );

const QUEUE_TABLE = 'wp_get_in_line_queue';

function wpCli( args ) {
	return execFileSync( 'npx', [ 'wp-env', 'run', 'tests-cli', '--', 'wp', ...args ], {
		encoding: 'utf8',
		stdio: [ 'ignore', 'pipe', 'pipe' ],
	} );
}

function setOptions( { enabled = 1, limit = 100, expiration = 20 } = {} ) {
	wpCli( [
		'option',
		'update',
		'get_in_line_options',
		JSON.stringify( { gil_enabled: enabled, gil_limit: limit, gil_expiration: expiration } ),
		'--format=json',
	] );
}

function resetQueue() {
	wpCli( [ 'db', 'query', `DELETE FROM ${ QUEUE_TABLE }` ] );
}

function expireAdmittedSessions() {
	wpCli( [ 'db', 'query', `UPDATE ${ QUEUE_TABLE } SET expires_at = '2000-01-01 00:00:00' WHERE status = 'admitted'` ] );
}

function queueCounts() {
	const output = wpCli( [
		'db',
		'query',
		`SELECT status, COUNT(*) FROM ${ QUEUE_TABLE } GROUP BY status`,
		'--skip-column-names',
	] );

	const counts = { admitted: 0, waiting: 0 };
	for ( const line of output.split( '\n' ) ) {
		const match = line.match( /^(admitted|waiting)\t(\d+)/ );
		if ( match ) {
			counts[ match[ 1 ] ] = parseInt( match[ 2 ], 10 );
		}
	}
	return counts;
}

async function loginAsAdmin( page ) {
	await page.goto( '/wp-login.php' );
	await page.fill( '#user_login', 'admin' );
	await page.fill( '#user_pass', 'password' );
	await page.click( '#wp-submit' );
	await page.waitForURL( /wp-admin/ );
}

module.exports = { wpCli, setOptions, resetQueue, expireAdmittedSessions, queueCounts, loginAsAdmin };
