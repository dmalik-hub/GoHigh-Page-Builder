/**
 * Miscellaneous editor utilities.
 */

export function generateId() {
	return Math.random().toString( 36 ).substr( 2, 8 );
}

export function deepClone( obj ) {
	return JSON.parse( JSON.stringify( obj ) );
}

/**
 * Find an element in the nested tree by id.
 */
export function findElement( elements, id ) {
	for ( const el of elements ) {
		if ( el.id === id ) return el;
		if ( el.elements && el.elements.length ) {
			const found = findElement( el.elements, id );
			if ( found ) return found;
		}
	}
	return null;
}

/**
 * Remove an element from the tree (mutates array).
 */
export function removeElement( elements, id ) {
	for ( let i = 0; i < elements.length; i++ ) {
		if ( elements[ i ].id === id ) {
			return elements.splice( i, 1 )[ 0 ];
		}
		if ( elements[ i ].elements ) {
			const removed = removeElement( elements[ i ].elements, id );
			if ( removed ) return removed;
		}
	}
	return null;
}

/**
 * Create a section with the given column widths (percentages).
 * Default: single 100%-width column.
 */
export function createSectionWithColumns( columnWidths = [ 100 ] ) {
	const sectionId = generateId();
	const columns   = columnWidths.map( width => ( {
		id:       generateId(),
		elType:   'column',
		settings: { _column_size: width },
		elements: [],
	} ) );
	return {
		id:       sectionId,
		elType:   'section',
		settings: {
			layout:  'boxed',
			padding: { top: '40', right: '0', bottom: '40', left: '0', unit: 'px', isLinked: false },
		},
		elements: columns,
	};
}

/** Alias for backward compat */
export function createDefaultSection() {
	return createSectionWithColumns( [ 100 ] );
}

/**
 * Create a new widget element node.
 */
export function createWidget( widgetType, settings = {} ) {
	return {
		id:         generateId(),
		elType:     'widget',
		widgetType,
		settings,
		elements:   [],
	};
}

/**
 * Evaluate control visibility conditions.
 */
export function isControlVisible( control, settings ) {
	if ( control.condition && Object.keys( control.condition ).length ) {
		for ( const [ key, expected ] of Object.entries( control.condition ) ) {
			const isNot   = key.endsWith( '!' );
			const realKey = isNot ? key.slice( 0, -1 ) : key;
			const actual  = settings[ realKey ];
			if ( isNot && actual === expected ) return false;
			if ( ! isNot && actual !== expected ) return false;
		}
	}
	return true;
}

/**
 * Build inline CSS for a set of controls + settings.
 * Used in the editor for live preview injection.
 */
export function generateCSS( elementId, controls, settings, device = 'desktop' ) {
	const wrapper = `.ghpb-element-${ elementId }`;
	let css = '';

	for ( const control of controls ) {
		if ( ! control.selectors || ! Object.keys( control.selectors ).length ) continue;

		let value = settings[ control.name ];

		// Responsive: pick per-device suffix.
		if ( control.responsive && device !== 'desktop' ) {
			const deviceVal = settings[ `${ control.name }_${ device }` ];
			if ( deviceVal !== undefined && deviceVal !== '' ) {
				value = deviceVal;
			}
		}

		if ( value === undefined || value === '' || value === null ) continue;

		for ( const [ selector, ruleTemplate ] of Object.entries( control.selectors ) ) {
			const resolvedSelector = selector.replace( /\{\{WRAPPER\}\}/g, wrapper );
			const rule = parseRule( ruleTemplate, value );
			if ( rule ) {
				if ( device === 'tablet' ) {
					css += `@media (max-width: 1024px) { ${ resolvedSelector } { ${ rule } } }`;
				} else if ( device === 'mobile' ) {
					css += `@media (max-width: 767px) { ${ resolvedSelector } { ${ rule } } }`;
				} else {
					css += `${ resolvedSelector } { ${ rule } }`;
				}
			}
		}
	}

	return css;
}

function parseRule( template, value ) {
	// Typography object: { font_family, font_size: {size, unit}, font_weight, text_transform, ... }
	if ( typeof value === 'object' && value !== null && 'font_family' in value ) {
		const parts = [];
		if ( value.font_family )              parts.push( `font-family: '${ value.font_family }', sans-serif` );
		if ( value.font_size?.size )          parts.push( `font-size: ${ value.font_size.size }${ value.font_size.unit || 'px' }` );
		if ( value.font_weight )              parts.push( `font-weight: ${ value.font_weight }` );
		if ( value.text_transform )           parts.push( `text-transform: ${ value.text_transform }` );
		if ( value.font_style )               parts.push( `font-style: ${ value.font_style }` );
		if ( value.line_height?.size )        parts.push( `line-height: ${ value.line_height.size }${ value.line_height.unit || '' }` );
		if ( value.letter_spacing?.size !== undefined ) parts.push( `letter-spacing: ${ value.letter_spacing.size }${ value.letter_spacing.unit || 'px' }` );
		return parts.length ? parts.join( '; ' ) + ';' : '';
	}

	// Slider: { size, unit }
	if ( typeof value === 'object' && value !== null && 'size' in value ) {
		if ( value.size === '' || value.size === null ) return '';
		return template
			.replace( /\{\{SIZE\}\}/g, value.size )
			.replace( /\{\{UNIT\}\}/g, value.unit || 'px' )
			.replace( /\{\{VALUE\}\}/g, value.size + ( value.unit || 'px' ) );
	}

	// Dimensions: { top, right, bottom, left, unit }
	if ( typeof value === 'object' && value !== null && 'top' in value ) {
		const u = value.unit || 'px';
		const t = ( value.top  || '0' ) + u;
		const r = ( value.right  || '0' ) + u;
		const b = ( value.bottom || '0' ) + u;
		const l = ( value.left   || '0' ) + u;
		return template
			.replace( /\{\{TOP\}\}/g, t )
			.replace( /\{\{RIGHT\}\}/g, r )
			.replace( /\{\{BOTTOM\}\}/g, b )
			.replace( /\{\{LEFT\}\}/g, l )
			.replace( /\{\{UNIT\}\}/g, u );
	}

	// Background object
	if ( typeof value === 'object' && value !== null && 'background' in value ) {
		if ( value.background === 'gradient' && value.gradient_from && value.gradient_to ) {
			return template.replace( /\{\{VALUE\}\}/g,
				`linear-gradient(${value.gradient_angle || 180}deg, ${value.gradient_from}, ${value.gradient_to})` );
		}
		if ( value.color ) {
			return template.replace( /\{\{VALUE\}\}/g, value.color );
		}
		return '';
	}

	// Plain objects (url, media, etc.) — skip
	if ( typeof value === 'object' ) return '';

	if ( ! value ) return '';
	return template.replace( /\{\{VALUE\}\}/g, value );
}
