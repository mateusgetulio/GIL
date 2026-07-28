const { test, expect, request } = require( '@playwright/test' );
const { setOptions, resetQueue, queueCounts } = require( './helpers' );

const BASE_URL = process.env.WP_BASE_URL || 'http://localhost:8889';
const LIMIT = 5;
const BURST = 20;

test.describe( 'Concurrency', () => {
	test.beforeEach( () => {
		setOptions( { limit: LIMIT } );
		resetQueue();
	} );

	test( `a burst of ${ BURST } parallel fresh visitors never exceeds the limit of ${ LIMIT }`, async () => {
		test.setTimeout( 120000 );

		const contexts = await Promise.all(
			Array.from( { length: BURST }, () => request.newContext( { baseURL: BASE_URL } ) )
		);

		const responses = await Promise.all( contexts.map( ( context ) => context.get( '/' ) ) );

		const statuses = responses.map( ( response ) => response.status() );
		const admittedResponses = statuses.filter( ( status ) => 200 === status ).length;
		const queuedResponses = statuses.filter( ( status ) => 503 === status ).length;

		expect( admittedResponses ).toBe( LIMIT );
		expect( queuedResponses ).toBe( BURST - LIMIT );

		const counts = queueCounts();
		expect( counts.admitted ).toBe( LIMIT );
		expect( counts.waiting ).toBe( BURST - LIMIT );

		await Promise.all( contexts.map( ( context ) => context.dispose() ) );
	} );
} );
