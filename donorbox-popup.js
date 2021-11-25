/**
 * Donorbox Popup Modal Controller
 *
 * @package  donorbox-popup-for-wp
 */

(function (w, d) {
	const frameID              = 'donorbox_widget_frame';
	const url                  = w.donorboxPopup.url
	const daysUntilNextShowing = w.donorboxPopup.daysUntilNextShowing

	if (url === undefined || url === "") {
		console.error( 'donorbox popup missing url' )
	}

	if (daysUntilNextShowing === undefined) {
		console.error( 'donorbox popup missing daysUntilNextShowing' )
	}

	let setCookie       = (cName, expDays) => {
		let date        = new Date();
		date.setTime( date.getTime() + (expDays * 24 * 60 * 60 * 1000) );
		const expires   = "expires=" + date.toUTCString();
		document.cookie = cName + "=seen; secure; " + expires + "; path=/";
	}

	let getCookie      = (cName) => {
		const name     = cName + "=";
		const cDecoded = decodeURIComponent( document.cookie );
		const cArr     = cDecoded .split( '; ' );
		let res;
		cArr.forEach(
			val => {
				if (val.indexOf( name ) === 0) {
					res = val.substring( name.length );
				}
			}
		)
		return res;
	}

	let openTheModal = (e, url, daysUntilNextShowing) => {
		e.preventDefault();
		var frame    = d.createElement( 'iframe' );

		d.body.style.overflow = 'hidden'
		frame.id              = frameID;
		frame.frameborder     = 0;
		frame.setAttribute( 'allowpaymentrequest', true );
		frame.src                   = url;
		frame.style.position        = 'fixed';
		frame.style.display         = 'block';
		frame.style.left            = '0px';
		frame.style.top             = '0px';
		frame.style.width           = '100%';
		frame.style.height          = '100%';
		frame.style.margin          = '0px';
		frame.style.padding         = '0px';
		frame.style.border          = 'none';
		frame.style.overflowX       = 'hidden';
		frame.style.overflowY       = 'auto';
		frame.style.visibility      = 'visible';
		frame.style.backgroundColor = 'transparent';
		frame.style.zIndex          = 2147483647;
		d.body.appendChild( frame );
		frame.focus();

		setCookie( frameID, daysUntilNextShowing );
	}

	// Listen for modal close event
	w.addEventListener(
		'message',
		(event) => {
			if (typeof event.data == 'object' && event.data.from == 'dbox' && event.data.close === true) {
				d.getElementById( frameID ).parentNode.removeChild( d.getElementById( frameID ) );
				d.body.style.overflow = 'auto'
			}
		}
	)

	w.onload = (event) => {
		if (getCookie( frameID ) === undefined) {
			openTheModal( event, url, daysUntilNextShowing )
		}
	};
}(window, document));
