/**
 * Font Lab — swap the heading / body / accent role fonts against the shared
 * library, live, without a reload.
 *
 * Loaded only when CCC_Font_Lab::config() passes all three guards; see
 * inc/class-ccc-font-lab.php. `window.cccFontLabConfig` is printed before this.
 *
 * HOW THE SWAP WORKS: the generated global stylesheet resolves every element's
 * family through a role variable — `h1 { font-family:
 * var(--wp--preset--font-family--heading) }`. Setting that property inline on
 * <html> beats the `:root` rule that defines it (same element, inline wins), so
 * one assignment restyles the whole document. Nothing is written to the DB and
 * theme.json is untouched.
 */
( function () {
	'use strict';

	var cfg = window.cccFontLabConfig;
	if ( ! cfg || ! cfg.library ) {
		return;
	}

	var root  = document.getElementById( 'ccc-font-lab' );
	if ( ! root ) {
		return;
	}

	var roles     = Object.keys( cfg.roles );
	var active    = roles[ 0 ];
	var families  = [];
	var selection = load();
	var injected  = {};

	var els = {
		toggle:   root.querySelector( '.ccc-font-lab__toggle' ),
		close:    root.querySelector( '.ccc-font-lab__close' ),
		list:     root.querySelector( '[data-ccc-font-lab-list]' ),
		search:   root.querySelector( '[data-ccc-font-lab-search]' ),
		bodysafe: root.querySelector( '[data-ccc-font-lab-bodysafe]' ),
		accents:  root.querySelector( '[data-ccc-font-lab-accents]' ),
		count:    root.querySelector( '[data-ccc-font-lab-count]' ),
		reset:    root.querySelector( '[data-ccc-font-lab-reset]' ),
		exportBtn:root.querySelector( '[data-ccc-font-lab-export]' ),
		output:   root.querySelector( '[data-ccc-font-lab-output]' )
	};

	function load() {
		try {
			return JSON.parse( localStorage.getItem( cfg.storage ) || '{}' );
		} catch ( e ) {
			return {};
		}
	}

	function save() {
		try {
			localStorage.setItem( cfg.storage, JSON.stringify( selection ) );
		} catch ( e ) {}
	}

	function url( path ) {
		return cfg.library + '/' + path + ( cfg.key ? '?k=' + encodeURIComponent( cfg.key ) : '' );
	}

	/**
	 * A CSS family name we control, rather than the font's own internal name —
	 * which may collide with an installed local font and silently win.
	 */
	function familyName( font ) {
		return 'CCCFL ' + font.slug;
	}

	/**
	 * Inject one @font-face per weight/style, once per family.
	 *
	 * Deduped by weight+italic: several families ship multiple WIDTH variants at
	 * the same weight (Northura has five at 900 — Black, BlackExpanded, …). All
	 * of them declared at `font-weight: 900` would leave the browser picking
	 * whichever parsed last, so we keep the first and preview one coherent width.
	 */
	function inject( font ) {
		if ( injected[ font.slug ] || ! font.faces || ! font.faces.length ) {
			return;
		}
		injected[ font.slug ] = true;

		var seen = {};
		var css  = font.faces.reduce( function ( out, face ) {
			var key = face.weight + ':' + ( face.italic ? 'i' : 'n' );
			if ( seen[ key ] ) {
				return out;
			}
			seen[ key ] = true;
			return out +
				'@font-face{font-family:"' + familyName( font ) + '";' +
				'src:url("' + url( font.slug + '/web/' + encodeURIComponent( face.file ) ) + '") format("woff2");' +
				'font-weight:' + face.weight + ';' +
				'font-style:' + ( face.italic ? 'italic' : 'normal' ) + ';' +
				'font-display:swap}';
		}, '' );

		var style = document.createElement( 'style' );
		style.setAttribute( 'data-ccc-font-lab-face', font.slug );
		style.textContent = css;
		document.head.appendChild( style );
	}

	function stackFor( font ) {
		// Keep a generic fallback so a failed fetch degrades to something
		// readable rather than to the browser's default serif.
		var generic = font.category === 'serif' ? 'serif'
			: ( font.category === 'mono' || font.monospace ) ? 'monospace'
			: 'sans-serif';
		return '"' + familyName( font ) + '", ' + generic;
	}

	function apply( role, font ) {
		inject( font );
		var stack = stackFor( font );
		document.documentElement.style.setProperty( '--wp--preset--font-family--' + role, stack );
		selection[ role ] = { slug: font.slug, name: font.name, stack: stack };
		save();
		render();
	}

	function clearRole( role ) {
		document.documentElement.style.removeProperty( '--wp--preset--font-family--' + role );
		delete selection[ role ];
		save();
		render();
	}

	function filtered() {
		var q  = ( els.search.value || '' ).trim().toLowerCase();
		var bs = els.bodysafe.checked;
		var ac = els.accents.checked;
		return families.filter( function ( f ) {
			if ( bs && f.use !== 'body' ) return false;
			if ( ac && ( f.charset || [] ).indexOf( 'latin_1' ) === -1 ) return false;
			if ( ! q ) return true;
			return ( f.name || '' ).toLowerCase().indexOf( q ) !== -1 ||
				( f.moods || [] ).join( ' ' ).toLowerCase().indexOf( q ) !== -1 ||
				( f.category || '' ).toLowerCase().indexOf( q ) !== -1;
		} );
	}

	function render() {
		roles.forEach( function ( r ) {
			var btn = root.querySelector( '.ccc-font-lab__role[data-role="' + r + '"]' );
			if ( ! btn ) return;
			btn.classList.toggle( 'is-active', r === active );
			btn.setAttribute( 'aria-selected', r === active ? 'true' : 'false' );
			var chosen = selection[ r ];
			btn.setAttribute( 'title', chosen ? chosen.name : 'theme default' );
			btn.classList.toggle( 'is-set', !! chosen );
		} );

		var rows = filtered();
		els.count.textContent = rows.length + ' of ' + families.length;

		var frag = document.createDocumentFragment();
		rows.forEach( function ( font ) {
			var li = document.createElement( 'li' );
			li.className = 'ccc-font-lab__item';
			if ( selection[ active ] && selection[ active ].slug === font.slug ) {
				li.classList.add( 'is-selected' );
			}

			var btn = document.createElement( 'button' );
			btn.type = 'button';
			btn.className = 'ccc-font-lab__pick';

			var name = document.createElement( 'span' );
			name.className = 'ccc-font-lab__name';
			name.textContent = font.name;

			// Preview in the actual face. Injecting on render means the list
			// loads every visible family's woff2 — acceptable because the list
			// is filtered and this is a dev-only tool on a fast connection.
			var sample = document.createElement( 'span' );
			sample.className = 'ccc-font-lab__sample';
			sample.textContent = 'Handgloves 123';
			inject( font );
			sample.style.fontFamily = stackFor( font );

			var meta = document.createElement( 'span' );
			meta.className = 'ccc-font-lab__meta';
			var bits = [ font.category, font.weight_range ];
			if ( font.caps_only ) bits.push( 'CAPS ONLY' );
			if ( ( font.charset || [] ).indexOf( 'latin_1' ) === -1 ) bits.push( 'no accents' );
			meta.textContent = bits.filter( Boolean ).join( ' · ' );
			if ( font.caps_only || ( font.charset || [] ).indexOf( 'latin_1' ) === -1 ) {
				meta.classList.add( 'is-warn' );
			}

			btn.appendChild( name );
			btn.appendChild( sample );
			btn.appendChild( meta );
			btn.addEventListener( 'click', function () { apply( active, font ); } );
			li.appendChild( btn );
			frag.appendChild( li );
		} );

		els.list.textContent = '';
		els.list.appendChild( frag );
	}

	/** theme.json fontFamilies + the @font-face blocks, ready to paste. */
	function exportJson() {
		var out = roles.filter( function ( r ) { return selection[ r ]; } ).map( function ( r ) {
			var sel  = selection[ r ];
			var font = families.filter( function ( f ) { return f.slug === sel.slug; } )[ 0 ] || {};
			var seen = {};
			return {
				name: sel.name,
				slug: r,
				fontFamily: '"' + sel.name + '", ' + stackFor( font ).split( ', ' ).pop(),
				fontFace: ( font.faces || [] ).filter( function ( f ) {
					var k = f.weight + ':' + ( f.italic ? 'i' : 'n' );
					if ( seen[ k ] ) return false;
					seen[ k ] = true;
					return true;
				} ).map( function ( f ) {
					return {
						fontFamily: sel.name,
						fontWeight: String( f.weight ),
						fontStyle: f.italic ? 'italic' : 'normal',
						src: [ 'file:./assets/fonts/' + sel.slug + '/' + f.file ]
					};
				} ),
				_source: sel.slug
			};
		} );

		if ( ! out.length ) {
			return '// Nothing selected yet.';
		}
		return '// theme.json → settings.typography.fontFamilies\n' +
			'// Copy the woff2 from the library into the child theme first:\n' +
			out.map( function ( o ) {
				return '//   ' + o._source + '/web/*.woff2  →  assets/fonts/' + o._source + '/';
			} ).join( '\n' ) +
			'\n// Register each family with Envato against this project before launch.\n\n' +
			JSON.stringify( out.map( function ( o ) { delete o._source; return o; } ), null, '\t' );
	}

	function open( yes ) {
		root.classList.toggle( 'is-open', yes );
		els.toggle.setAttribute( 'aria-expanded', yes ? 'true' : 'false' );
	}

	// ── wiring ──────────────────────────────────────────────────────
	els.toggle.addEventListener( 'click', function () {
		open( ! root.classList.contains( 'is-open' ) );
	} );
	els.close.addEventListener( 'click', function () { open( false ); } );
	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' && root.classList.contains( 'is-open' ) ) open( false );
	} );

	roles.forEach( function ( r ) {
		var btn = root.querySelector( '.ccc-font-lab__role[data-role="' + r + '"]' );
		if ( ! btn ) return;
		btn.addEventListener( 'click', function () { active = r; render(); } );
		// Right-click a role to clear just that one back to the theme default.
		btn.addEventListener( 'contextmenu', function ( e ) {
			e.preventDefault();
			clearRole( r );
		} );
	} );

	[ els.search, els.bodysafe, els.accents ].forEach( function ( el ) {
		el.addEventListener( 'input', render );
	} );

	els.reset.addEventListener( 'click', function () {
		roles.forEach( function ( r ) {
			document.documentElement.style.removeProperty( '--wp--preset--font-family--' + r );
		} );
		selection = {};
		save();
		render();
	} );

	els.exportBtn.addEventListener( 'click', function () {
		var text = exportJson();
		els.output.hidden = false;
		els.output.textContent = text;
		if ( navigator.clipboard ) {
			navigator.clipboard.writeText( text ).then( function () {
				els.exportBtn.textContent = 'Copied';
				setTimeout( function () { els.exportBtn.textContent = 'Copy theme.json'; }, 1500 );
			} ).catch( function () {} );
		}
	} );

	// ── boot ────────────────────────────────────────────────────────
	fetch( url( 'fonts.json' ) )
		.then( function ( r ) {
			if ( ! r.ok ) throw new Error( 'HTTP ' + r.status );
			return r.json();
		} )
		.then( function ( data ) {
			families = data.filter( function ( f ) { return f.faces && f.faces.length; } );
			families.sort( function ( a, b ) { return a.name.localeCompare( b.name ); } );
			// Re-inject faces for anything restored from a previous session —
			// early_restore() set the variables before paint, but the @font-face
			// rules only exist once this script runs.
			roles.forEach( function ( r ) {
				var sel = selection[ r ];
				if ( ! sel ) return;
				var font = families.filter( function ( f ) { return f.slug === sel.slug; } )[ 0 ];
				if ( font ) inject( font );
			} );
			root.hidden = false;
			render();
		} )
		.catch( function ( err ) {
			// Surface it: a silent failure here looks like "the panel is broken"
			// when the real cause is usually a missing FONT_KEY or CORS.
			root.hidden = false;
			els.list.innerHTML = '<li class="ccc-font-lab__error">Could not load the font index from ' +
				cfg.library + '<br><small>' + err.message +
				' — check the library URL, the FONT_KEY, and that the deployment sends ' +
				'Access-Control-Allow-Origin.</small></li>';
			render = function () {};
		} );
} )();
