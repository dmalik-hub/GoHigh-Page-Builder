import Backbone from 'backbone';
import { isControlVisible } from '../../../utils/helpers.js';

/**
 * Element edit panel — renders control tabs and control rows.
 */
const EditPanelView = Backbone.View.extend( {
	className: 'ghpb-edit-panel',

	initialize( options ) {
		this.element     = options.element;
		this.widgetConfig = options.widgetConfig;
		this.onChange    = options.onChange;
		this.activeTab   = 'content';
	},

	render() {
		this.el.innerHTML = this._buildHTML();
		this._bindControls();
		return this;
	},

	_buildHTML() {
		const controls = this.widgetConfig.controls || [];
		const settings = this.element.settings || {};

		// Build tab navigation.
		const tabs = [ 'content', 'style', 'advanced' ];
		const tabsHTML = tabs.map( tab => `
			<button class="ghpb-tab-btn ${ this.activeTab === tab ? 'active' : '' }" data-tab="${ tab }">
				${ tab.charAt( 0 ).toUpperCase() + tab.slice( 1 ) }
			</button>
		` ).join( '' );

		// Group controls by section, filtered to activeTab.
		const sections = this._groupBySection( controls, settings );

		const sectionsHTML = sections.map( section => {
			if ( section.tab !== this.activeTab ) return '';
			const controlsHTML = section.controls.map( c => this._renderControl( c, settings[ c.name ] ) ).join( '' );
			return `
				<div class="ghpb-control-section" data-section="${ section.id }">
					<div class="ghpb-section-header" data-collapse="${ section.id }">
						<span class="ghpb-section-label">${ section.label }</span>
						<span class="dashicons dashicons-arrow-down-alt2 ghpb-section-arrow"></span>
					</div>
					<div class="ghpb-section-controls">
						${ controlsHTML }
					</div>
				</div>
			`;
		} ).join( '' );

		return `
			<div class="ghpb-panel-tabs">
				${ tabsHTML }
			</div>
			<div class="ghpb-panel-sections">
				${ sectionsHTML }
			</div>
		`;
	},

	_groupBySection( controls, settings ) {
		const sections = [];
		let currentSection = null;

		controls.forEach( control => {
			if ( control.type === 'section' ) {
				currentSection = {
					id:       control.name,
					label:    control.label || '',
					tab:      control.tab || 'content',
					controls: [],
				};
				sections.push( currentSection );
				return;
			}
			// Skip UI-only types for now.
			if ( [ 'tabs', 'tab' ].includes( control.type ) ) return;
			if ( ! currentSection ) return;
			if ( ! isControlVisible( control, settings ) ) return;
			currentSection.controls.push( control );
		} );

		return sections;
	},

	_renderControl( control, value ) {
		const { type, name, label, show_label = true, separator } = control;

		if ( [ 'section', 'heading', 'tabs', 'tab' ].includes( type ) ) return '';

		const val = value !== undefined ? value : ( control.default || '' );
		const controlHTML = this._renderControlInput( control, val );

		return `
			<div class="ghpb-control ghpb-control-type-${ type }" data-control="${ name }">
				${ show_label && label ? `<label class="ghpb-control-label">${ label }</label>` : '' }
				${ controlHTML }
				${ control.description ? `<div class="ghpb-control-desc">${ control.description }</div>` : '' }
			</div>
		`;
	},

	_renderControlInput( control, value ) {
		const { type, name } = control;
		const id = `ghpb-ctrl-${ name }`;

		switch ( type ) {
			case 'text':
				return `<input type="text" id="${ id }" data-key="${ name }" class="ghpb-input ghpb-text-input" value="${ this._esc( value || '' ) }" placeholder="${ this._esc( control.placeholder || '' ) }">`;

			case 'textarea':
				return `<textarea id="${ id }" data-key="${ name }" class="ghpb-input ghpb-textarea" rows="${ control.rows || 4 }" placeholder="${ this._esc( control.placeholder || '' ) }">${ this._esc( value || '' ) }</textarea>`;

			case 'number':
				return `<input type="number" id="${ id }" data-key="${ name }" class="ghpb-input ghpb-number-input" value="${ this._esc( value || '' ) }" min="${ control.min || '' }" max="${ control.max || '' }" step="${ control.step || 1 }">`;

			case 'url': {
				const urlVal = typeof value === 'object' ? value : { url: '', is_external: '', nofollow: '' };
				return `
					<div class="ghpb-url-control" data-key="${ name }">
						<input type="url" class="ghpb-input ghpb-url-input" data-sub="url" value="${ this._esc( urlVal.url || '' ) }" placeholder="https://">
						<label><input type="checkbox" data-sub="is_external" ${ urlVal.is_external ? 'checked' : '' }> Open in new tab</label>
						<label><input type="checkbox" data-sub="nofollow" ${ urlVal.nofollow ? 'checked' : '' }> Nofollow</label>
					</div>
				`;
			}

			case 'select': {
				const opts = Object.entries( control.options || {} ).map( ( [ v, l ] ) => `<option value="${ this._esc( v ) }" ${ v === value ? 'selected' : '' }>${ l }</option>` ).join( '' );
				return `<select id="${ id }" data-key="${ name }" class="ghpb-select">${ opts }</select>`;
			}

			case 'choose': {
				const opts = Object.entries( control.options || {} ).map( ( [ v, opt ] ) => `
					<button type="button" class="ghpb-choose-btn ${ v === value ? 'active' : '' }" data-value="${ this._esc( v ) }" title="${ this._esc( opt.title || v ) }">
						<span class="${ opt.icon || '' }"></span>
					</button>
				` ).join( '' );
				return `<div class="ghpb-choose" data-key="${ name }">${ opts }</div>`;
			}

			case 'switcher': {
				const checked = value === 'yes' || value === true ? 'checked' : '';
				return `
					<div class="ghpb-switcher" data-key="${ name }">
						<label class="ghpb-switcher-label">
							<input type="checkbox" class="ghpb-switcher-input" ${ checked }>
							<span class="ghpb-switcher-slider"></span>
						</label>
					</div>
				`;
			}

			case 'color':
				return `
					<div class="ghpb-color-control" data-key="${ name }">
						<div class="ghpb-color-preview" style="background:${ this._esc( value || 'transparent' ) }"></div>
						<input type="color" class="ghpb-color-native" value="${ value || '#000000' }">
						<input type="text" class="ghpb-color-text ghpb-input" value="${ this._esc( value || '' ) }" placeholder="#000000 or rgba()">
					</div>
				`;

			case 'slider': {
				const sv = typeof value === 'object' ? value : { size: '', unit: 'px' };
				const units = ( control.size_units || [ 'px' ] );
				const unitsHTML = units.map( u => `<option value="${ u }" ${ u === sv.unit ? 'selected' : '' }>${ u }</option>` ).join( '' );
				return `
					<div class="ghpb-slider-control" data-key="${ name }">
						<input type="range"
							class="ghpb-slider"
							min="${ ( control.range?.[ sv.unit ]?.min ?? 0 ) }"
							max="${ ( control.range?.[ sv.unit ]?.max ?? 100 ) }"
							step="${ ( control.range?.[ sv.unit ]?.step ?? 1 ) }"
							value="${ sv.size || 0 }">
						<input type="number" class="ghpb-slider-number ghpb-input" value="${ sv.size || '' }">
						${ units.length > 1 ? `<select class="ghpb-slider-unit">${ unitsHTML }</select>` : '' }
					</div>
				`;
			}

			case 'dimensions': {
				const dv = typeof value === 'object' ? value : { top: '', right: '', bottom: '', left: '', unit: 'px', isLinked: true };
				const units = ( control.size_units || [ 'px', 'em', '%' ] );
				const unitsHTML = units.map( u => `<option value="${ u }" ${ u === dv.unit ? 'selected' : '' }>${ u }</option>` ).join( '' );
				return `
					<div class="ghpb-dimensions" data-key="${ name }">
						<div class="ghpb-dimensions-inputs">
							${ [ 'top', 'right', 'bottom', 'left' ].map( side => `
								<div class="ghpb-dim-field">
									<input type="number" class="ghpb-input ghpb-dim-input" data-side="${ side }" value="${ dv[ side ] || '' }">
									<span class="ghpb-dim-label">${ side.charAt(0).toUpperCase() }</span>
								</div>
							` ).join( '' ) }
							<button type="button" class="ghpb-dim-link ${ dv.isLinked ? 'active' : '' }" title="Link all">
								<span class="dashicons ${ dv.isLinked ? 'dashicons-admin-links' : 'dashicons-editor-unlink' }"></span>
							</button>
						</div>
						<select class="ghpb-dim-unit">${ unitsHTML }</select>
					</div>
				`;
			}

			case 'media': {
				const mv = typeof value === 'object' ? value : { url: '', id: 0 };
				return `
					<div class="ghpb-media-control" data-key="${ name }">
						${ mv.url ? `<div class="ghpb-media-preview"><img src="${ this._esc( mv.url ) }" alt=""></div>` : '' }
						<div class="ghpb-media-buttons">
							<button type="button" class="ghpb-btn ghpb-media-select">
								${ mv.url ? 'Change' : 'Choose' } Image
							</button>
							${ mv.url ? '<button type="button" class="ghpb-btn ghpb-media-remove">Remove</button>' : '' }
						</div>
						<input type="hidden" class="ghpb-media-url" data-sub="url" value="${ this._esc( mv.url || '' ) }">
						<input type="hidden" class="ghpb-media-id" data-sub="id" value="${ mv.id || 0 }">
					</div>
				`;
			}

			case 'icon': {
				const iv = typeof value === 'object' ? value : { value: '', library: 'solid' };
				return `
					<div class="ghpb-icon-control" data-key="${ name }">
						<div class="ghpb-icon-preview">
							${ iv.value ? `<i class="${ this._esc( iv.value ) }"></i>` : '<span>No icon</span>' }
						</div>
						<input type="text" class="ghpb-input ghpb-icon-input" data-sub="value" value="${ this._esc( iv.value || '' ) }" placeholder="dashicons dashicons-star-filled">
					</div>
				`;
			}

			case 'code':
				return `<textarea id="${ id }" data-key="${ name }" class="ghpb-input ghpb-code-input" rows="${ control.rows || 10 }" spellcheck="false">${ this._esc( value || '' ) }</textarea>`;

			case 'background': {
				const bv = typeof value === 'object' ? value : { background: 'classic', color: '' };
				return `
					<div class="ghpb-background-control" data-key="${ name }">
						<div class="ghpb-bg-type-select">
							<select class="ghpb-select" data-sub="background">
								${ [ 'classic', 'gradient' ].map( t => `<option value="${ t }" ${ bv.background === t ? 'selected' : '' }>${ t }</option>` ).join( '' ) }
							</select>
						</div>
						<div class="ghpb-bg-color">
							<input type="color" data-sub="color" value="${ bv.color || '#ffffff' }">
							<input type="text" class="ghpb-input" data-sub="color-text" value="${ this._esc( bv.color || '' ) }">
						</div>
					</div>
				`;
			}

			case 'repeater':
				return `<div class="ghpb-repeater" data-key="${ name }"><p class="ghpb-repeater-info">Repeater (configure in Phase 2)</p></div>`;

			default:
				return `<div class="ghpb-control-unsupported">Control type: ${ type }</div>`;
		}
	},

	_esc( val ) {
		return String( val ).replace( /&/g, '&amp;' ).replace( /"/g, '&quot;' ).replace( /</g, '&lt;' );
	},

	_bindControls() {
		const self = this;

		// Simple text/textarea/number.
		this.el.querySelectorAll( '.ghpb-text-input, .ghpb-textarea, .ghpb-number-input, .ghpb-code-input' ).forEach( el => {
			el.addEventListener( 'input', function() {
				self.onChange( this.dataset.key, this.value );
			} );
		} );

		// Select.
		this.el.querySelectorAll( '.ghpb-select' ).forEach( el => {
			el.addEventListener( 'change', function() {
				if ( this.dataset.key ) self.onChange( this.dataset.key, this.value );
			} );
		} );

		// Choose buttons.
		this.el.querySelectorAll( '.ghpb-choose' ).forEach( group => {
			group.querySelectorAll( '.ghpb-choose-btn' ).forEach( btn => {
				btn.addEventListener( 'click', function() {
					group.querySelectorAll( '.ghpb-choose-btn' ).forEach( b => b.classList.remove( 'active' ) );
					this.classList.add( 'active' );
					self.onChange( group.dataset.key, this.dataset.value );
				} );
			} );
		} );

		// Switcher.
		this.el.querySelectorAll( '.ghpb-switcher' ).forEach( sw => {
			const input = sw.querySelector( '.ghpb-switcher-input' );
			input?.addEventListener( 'change', function() {
				self.onChange( sw.dataset.key, this.checked ? 'yes' : '' );
			} );
		} );

		// Color.
		this.el.querySelectorAll( '.ghpb-color-control' ).forEach( ctrl => {
			const native = ctrl.querySelector( '.ghpb-color-native' );
			const text   = ctrl.querySelector( '.ghpb-color-text' );
			const prev   = ctrl.querySelector( '.ghpb-color-preview' );

			native?.addEventListener( 'input', function() {
				if ( text ) text.value = this.value;
				if ( prev ) prev.style.background = this.value;
				self.onChange( ctrl.dataset.key, this.value );
			} );
			text?.addEventListener( 'input', function() {
				if ( prev ) prev.style.background = this.value;
				self.onChange( ctrl.dataset.key, this.value );
			} );
		} );

		// Slider.
		this.el.querySelectorAll( '.ghpb-slider-control' ).forEach( ctrl => {
			const range  = ctrl.querySelector( '.ghpb-slider' );
			const num    = ctrl.querySelector( '.ghpb-slider-number' );
			const unitEl = ctrl.querySelector( '.ghpb-slider-unit' );
			const key    = ctrl.dataset.key;

			const emit = () => {
				self.onChange( key, {
					size: parseFloat( num?.value || 0 ),
					unit: unitEl?.value || 'px',
				} );
			};

			range?.addEventListener( 'input', function() {
				if ( num ) num.value = this.value;
				emit();
			} );
			num?.addEventListener( 'input', function() {
				if ( range ) range.value = this.value;
				emit();
			} );
			unitEl?.addEventListener( 'change', emit );
		} );

		// Dimensions.
		this.el.querySelectorAll( '.ghpb-dimensions' ).forEach( ctrl => {
			const key     = ctrl.dataset.key;
			let linked    = ctrl.querySelector( '.ghpb-dim-link' )?.classList.contains( 'active' );

			const emit = () => {
				const dims = {};
				ctrl.querySelectorAll( '.ghpb-dim-input' ).forEach( input => {
					dims[ input.dataset.side ] = input.value;
				} );
				dims.unit     = ctrl.querySelector( '.ghpb-dim-unit' )?.value || 'px';
				dims.isLinked = linked;
				self.onChange( key, dims );
			};

			ctrl.querySelectorAll( '.ghpb-dim-input' ).forEach( input => {
				input.addEventListener( 'input', function() {
					if ( linked ) {
						ctrl.querySelectorAll( '.ghpb-dim-input' ).forEach( i => { i.value = this.value; } );
					}
					emit();
				} );
			} );
			ctrl.querySelector( '.ghpb-dim-unit' )?.addEventListener( 'change', emit );
			ctrl.querySelector( '.ghpb-dim-link' )?.addEventListener( 'click', function() {
				linked = ! linked;
				this.classList.toggle( 'active', linked );
				const icon = this.querySelector( '.dashicons' );
				if ( icon ) icon.className = `dashicons ${ linked ? 'dashicons-admin-links' : 'dashicons-editor-unlink' }`;
			} );
		} );

		// URL.
		this.el.querySelectorAll( '.ghpb-url-control' ).forEach( ctrl => {
			const key = ctrl.dataset.key;
			const emit = () => {
				self.onChange( key, {
					url:         ctrl.querySelector( '[data-sub="url"]' )?.value || '',
					is_external: ctrl.querySelector( '[data-sub="is_external"]' )?.checked ? 'on' : '',
					nofollow:    ctrl.querySelector( '[data-sub="nofollow"]' )?.checked ? 'on' : '',
				} );
			};
			ctrl.querySelectorAll( 'input' ).forEach( i => i.addEventListener( 'input', emit ) );
			ctrl.querySelectorAll( 'input[type="checkbox"]' ).forEach( i => i.addEventListener( 'change', emit ) );
		} );

		// Media — uses wp.media.
		this.el.querySelectorAll( '.ghpb-media-control' ).forEach( ctrl => {
			const key = ctrl.dataset.key;
			const selectBtn = ctrl.querySelector( '.ghpb-media-select' );
			const removeBtn = ctrl.querySelector( '.ghpb-media-remove' );

			selectBtn?.addEventListener( 'click', function() {
				if ( ! window.wp || ! window.wp.media ) return;
				const frame = wp.media( {
					title: 'Choose Image',
					button: { text: 'Use This Image' },
					multiple: false,
				} );
				frame.on( 'select', function() {
					const att = frame.state().get( 'selection' ).first().toJSON();
					ctrl.querySelector( '.ghpb-media-url' ).value = att.url;
					ctrl.querySelector( '.ghpb-media-id' ).value  = att.id;
					self.onChange( key, { url: att.url, id: att.id, alt: att.alt || '' } );
				} );
				frame.open();
			} );

			removeBtn?.addEventListener( 'click', function() {
				self.onChange( key, { url: '', id: 0, alt: '' } );
			} );
		} );

		// Icon text input.
		this.el.querySelectorAll( '.ghpb-icon-control' ).forEach( ctrl => {
			const key = ctrl.dataset.key;
			ctrl.querySelector( '.ghpb-icon-input' )?.addEventListener( 'input', function() {
				self.onChange( key, { value: this.value, library: 'solid' } );
			} );
		} );

		// Tab switching.
		this.el.querySelectorAll( '.ghpb-tab-btn' ).forEach( btn => {
			btn.addEventListener( 'click', () => {
				this.activeTab = btn.dataset.tab;
				this.render(); // re-render with new active tab
			} );
		} );

		// Section collapse.
		this.el.querySelectorAll( '.ghpb-section-header' ).forEach( header => {
			header.addEventListener( 'click', function() {
				const section = this.closest( '.ghpb-control-section' );
				section?.classList.toggle( 'ghpb-collapsed' );
			} );
		} );
	},
} );

export default EditPanelView;
