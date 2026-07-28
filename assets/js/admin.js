( function () {
	'use strict';

	var config = window.getInLineAdmin || {};
	var admittedEl = document.getElementById( 'gil-admitted-count' );
	var waitingEl = document.getElementById( 'gil-waiting-count' );
	var updatedEl = document.getElementById( 'gil-updated' );
	var liveEl = document.getElementById( 'gil-admin-live-status' );

	if ( ! admittedEl || ! waitingEl || ! config.endpoint ) {
		return;
	}

	var lastUpdated = Date.now();
	var lastAnnounced = '';

	function renderUpdatedAgo() {
		if ( ! updatedEl ) {
			return;
		}
		var seconds = Math.round( ( Date.now() - lastUpdated ) / 1000 );
		updatedEl.textContent = seconds < 5 ? config.strings.justNow : config.strings.secondsAgo.replace( '%d', seconds );
	}

	function render( counts ) {
		admittedEl.textContent = counts.admitted;
		waitingEl.textContent = counts.waiting;
		waitingEl.classList.toggle( 'gil-waiting-hot', counts.waiting > 0 );
		lastUpdated = Date.now();

		if ( liveEl ) {
			var announcement = config.strings.announcement
				.replace( '%1$s', counts.admitted )
				.replace( '%2$s', counts.waiting );
			if ( announcement !== lastAnnounced ) {
				liveEl.textContent = announcement;
				lastAnnounced = announcement;
			}
		}
	}

	function poll() {
		if ( 'hidden' === document.visibilityState ) {
			return;
		}
		window
			.fetch( config.endpoint, {
				credentials: 'same-origin',
				cache: 'no-store',
				headers: { 'X-WP-Nonce': config.nonce },
			} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( render )
			.catch( function () {} );
	}

	document.addEventListener( 'visibilitychange', function () {
		if ( 'visible' === document.visibilityState ) {
			poll();
		}
	} );

	window.setInterval( poll, 10000 );
	window.setInterval( renderUpdatedAgo, 1000 );
	poll();
} )();
