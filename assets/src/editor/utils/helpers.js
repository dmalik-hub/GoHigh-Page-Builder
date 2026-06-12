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
 * Returns the element object or null.
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
 * Returns the removed element or null.
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
 * Create a default section with one 100%-width column.
 */
export function createDefaultSection() {
	const sectionId = generateId();
	const columnId  = generateId();
	return {
		id: sectionId,
		elType: 'section',
		settings: {
			layout: 'boxed',
			_column_size: 100,
			padding: { top: '40', right: '0', bottom: '40', left: '0', unit: 'px', isLinked: false },
		},
		elements: [
			{
				id: columnId,
				elType: 'column',
				settings: { _column_size: 100 },
				elements: [],
			},
		],
	};
}

/**
 * Create a new widget element node.
 */
export function createWidget( widgetType, settings = {} ) {
	return {
		id: generateId(),
		elType: 'widget',
		widgetType,
		settings,
		elements: [],
	};
}

/**
 * Evaluate control visibility conditions.
 * control.condition = { key: value } — shows if ALL match.
 * control.conditions = { relation: 'and'|'or', terms: [{name, operator, value}] }
 */
export function isControlVisible( control, settings ) {
	if ( control.condition && Object.keys( control.condition ).length ) {
		for ( const [ key, expected ] of Object.entries( control.condition ) ) {
			const isNot = key.endsWith( '!' );
			const realKey = isNot ? key.slice( 0, -1 ) : key;
			const actual = settings[ realKey ];
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
			const resolvedSelector = selector.replace( '{{WRAPPER}}', wrapper );
			const rule = parseRule( ruleTemplate, value );
			if ( rule ) {
				css += `${ resolvedSelector } { ${ rule } }`;
			}
		}
	}

	return css;
}

function parseRule( template, value ) {
	if ( typeof value === 'object' && value !== null && 'size' in value ) {
		if ( value.size === '' ) return '';
		return template
			.replace( '{{SIZE}}', value.size )
			.replace( '{{UNIT}}', value.unit || 'px' )
			.replace( '{{VALUE}}', value.size + ( value.unit || 'px' ) );
	}
	if ( typeof value === 'object' && value !== null && 'top' in value ) {
		const u = value.unit || 'px';
		return template
			.replace( '{{TOP}}', value.top + u )
			.replace( '{{RIGHT}}', value.right + u )
			.replace( '{{BOTTOM}}', value.bottom + u )
			.replace( '{{LEFT}}', value.left + u )
			.replace( '{{UNIT}}', u );
	}
	if ( typeof value === 'object' ) return '';
	if ( ! value ) return '';
	return template.replace( /\{\{VALUE\}\}/g, value );
}
