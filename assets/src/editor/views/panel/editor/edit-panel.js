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

		const tabs    = [ 'content', 'style', 'advanced' ];
		const tabsHTML = tabs.map( tab => `
			<button class="ghpb-tab-btn ${ this.activeTab === tab ? 'active' : '' }" data-tab="${ tab }">
				${ tab.charAt( 0 ).toUpperCase() + tab.slice( 1 ) }
			</button>
		` ).join( '' );

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

		const val        = value !== undefined ? value : ( control.default !== undefined ? control.default : '' );
		const controlHTML = this._renderControlInput( control, val );
		const sepClass   = separator ? ' ghpb-control-sep' : '';

		return `
			<div class="ghpb-control ghpb-control-type-${ type }${ sepClass }" data-control="${ name }">
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
				return `<input type="number" id="${ id }" data-key="${ name }" class="ghpb-input ghpb-number-input" value="${ this._esc( String( value !== '' ? value : '' ) ) }" min="${ control.min ?? '' }" max="${ control.max ?? '' }" step="${ control.step || 1 }">`;

			case 'url': {
				const urlVal = typeof value === 'object' && value ? value : { url: '', is_external: '', nofollow: '' };
				return `
					<div class="ghpb-url-control" data-key="${ name }">
						<input type="url" class="ghpb-input ghpb-url-input" data-sub="url" value="${ this._esc( urlVal.url || '' ) }" placeholder="https://">
						<label class="ghpb-url-opt"><input type="checkbox" data-sub="is_external" ${ urlVal.is_external ? 'checked' : '' }> Open in new tab</label>
						<label class="ghpb-url-opt"><input type="checkbox" data-sub="nofollow" ${ urlVal.nofollow ? 'checked' : '' }> Nofollow</label>
					</div>
				`;
			}

			case 'select': {
				const opts = Object.entries( control.options || {} ).map( ( [ v, l ] ) =>
					`<option value="${ this._esc( v ) }" ${ v === String( value ) ? 'selected' : '' }>${ l }</option>`
				).join( '' );
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
				const checked = value === 'yes' || value === true || value === 1 ? 'checked' : '';
				return `
					<div class="ghpb-switcher" data-key="${ name }">
						<label class="ghpb-switcher-label">
							<input type="checkbox" class="ghpb-switcher-input" ${ checked }>
							<span class="ghpb-switcher-slider"></span>
							<span class="ghpb-switcher-label-text">${ control.label_on || 'Yes' } / ${ control.label_off || 'No' }</span>
						</label>
					</div>
				`;
			}

			case 'color':
				return `
					<div class="ghpb-color-control" data-key="${ name }">
						<div class="ghpb-color-preview" style="background:${ this._esc( value || 'transparent' ) }"></div>
						<input type="color" class="ghpb-color-native" value="${ value && value.startsWith( '#' ) ? value : '#000000' }">
						<input type="text" class="ghpb-color-text ghpb-input" value="${ this._esc( value || '' ) }" placeholder="#000000 or rgba()">
					</div>
				`;

			case 'slider': {
				const sv    = typeof value === 'object' && value ? value : { size: control.default?.size ?? '', unit: control.default?.unit || 'px' };
				const units = ( control.size_units || [ 'px' ] );
				const range = control.range || {};
				const unitRange = range[ sv.unit ] || range.px || { min: 0, max: 100, step: 1 };
				const unitsHTML = units.map( u => `<option value="${ u }" ${ u === sv.unit ? 'selected' : '' }>${ u }</option>` ).join( '' );
				return `
					<div class="ghpb-slider-control" data-key="${ name }">
						<input type="range" class="ghpb-slider"
							min="${ unitRange.min ?? 0 }" max="${ unitRange.max ?? 100 }" step="${ unitRange.step ?? 1 }"
							value="${ sv.size !== '' ? sv.size : ( unitRange.min ?? 0 ) }">
						<input type="number" class="ghpb-slider-number ghpb-input" value="${ sv.size !== '' ? sv.size : '' }">
						${ units.length > 1 ? `<select class="ghpb-slider-unit ghpb-select">${ unitsHTML }</select>` : '' }
					</div>
				`;
			}

			case 'dimensions': {
				const dv    = typeof value === 'object' && value ? value : { top: '', right: '', bottom: '', left: '', unit: 'px', isLinked: true };
				const units = ( control.size_units || [ 'px', 'em', '%' ] );
				const unitsHTML = units.map( u => `<option value="${ u }" ${ u === dv.unit ? 'selected' : '' }>${ u }</option>` ).join( '' );
				return `
					<div class="ghpb-dimensions" data-key="${ name }">
						<div class="ghpb-dimensions-inputs">
							${ [ 'top', 'right', 'bottom', 'left' ].map( side => `
								<div class="ghpb-dim-field">
									<input type="number" class="ghpb-input ghpb-dim-input" data-side="${ side }" value="${ dv[ side ] || '' }" placeholder="0">
									<span class="ghpb-dim-label">${ side.charAt( 0 ).toUpperCase() }</span>
								</div>
							` ).join( '' ) }
							<button type="button" class="ghpb-dim-link ${ dv.isLinked ? 'active' : '' }" title="Link all sides">
								<span class="dashicons ${ dv.isLinked ? 'dashicons-admin-links' : 'dashicons-editor-unlink' }"></span>
							</button>
						</div>
						<select class="ghpb-dim-unit ghpb-select">${ unitsHTML }</select>
					</div>
				`;
			}

			case 'media': {
				const mv = typeof value === 'object' && value ? value : { url: '', id: 0 };
				return `
					<div class="ghpb-media-control" data-key="${ name }">
						${ mv.url ? `<div class="ghpb-media-preview"><img src="${ this._esc( mv.url ) }" alt=""></div>` : '<div class="ghpb-media-preview ghpb-media-empty"><span class="dashicons dashicons-format-image"></span></div>' }
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
				const iv = typeof value === 'object' && value ? value : { value: '', library: 'dashicons' };
				return `
					<div class="ghpb-icon-control" data-key="${ name }">
						<div class="ghpb-icon-preview">
							${ iv.value ? `<span class="${ this._esc( iv.value ) }"></span>` : '<span class="dashicons dashicons-star-empty" style="color:#888"></span>' }
						</div>
						<input type="text" class="ghpb-input ghpb-icon-input" data-sub="value" value="${ this._esc( iv.value || '' ) }" placeholder="dashicons dashicons-star-filled">
					</div>
				`;
			}

			case 'code':
				return `<textarea id="${ id }" data-key="${ name }" class="ghpb-input ghpb-code-input" rows="${ control.rows || 8 }" spellcheck="false" placeholder="${ this._esc( control.placeholder || '' ) }">${ this._esc( value || '' ) }</textarea>`;

			case 'background': {
				const bv = typeof value === 'object' && value ? value : { background: 'classic', color: '' };
				return `
					<div class="ghpb-background-control" data-key="${ name }">
						<div class="ghpb-bg-type-btns">
							<button type="button" class="ghpb-bg-type-btn ${ bv.background !== 'gradient' ? 'active' : '' }" data-bg="classic" title="Classic">
								<span class="dashicons dashicons-admin-appearance"></span>
							</button>
							<button type="button" class="ghpb-bg-type-btn ${ bv.background === 'gradient' ? 'active' : '' }" data-bg="gradient" title="Gradient">
								<span class="dashicons dashicons-art"></span>
							</button>
						</div>
						<input type="hidden" class="ghpb-bg-type" data-sub="background" value="${ this._esc( bv.background || 'classic' ) }">
						<div class="ghpb-bg-classic ${ bv.background === 'gradient' ? 'ghpb-hidden' : '' }">
							<div class="ghpb-color-control ghpb-bg-color-ctrl" data-sub="color">
								<div class="ghpb-color-preview" style="background:${ this._esc( bv.color || 'transparent' ) }"></div>
								<input type="color" class="ghpb-color-native" value="${ bv.color && bv.color.startsWith( '#' ) ? bv.color : '#ffffff' }">
								<input type="text" class="ghpb-color-text ghpb-input" value="${ this._esc( bv.color || '' ) }" placeholder="#ffffff">
							</div>
						</div>
						<div class="ghpb-bg-gradient ${ bv.background !== 'gradient' ? 'ghpb-hidden' : '' }">
							<label class="ghpb-control-label">From</label>
							<div class="ghpb-color-control ghpb-bg-grad-from" data-sub="gradient_from">
								<div class="ghpb-color-preview" style="background:${ this._esc( bv.gradient_from || '#6c63ff' ) }"></div>
								<input type="color" class="ghpb-color-native" value="${ bv.gradient_from || '#6c63ff' }">
								<input type="text" class="ghpb-color-text ghpb-input" value="${ this._esc( bv.gradient_from || '' ) }" placeholder="#6c63ff">
							</div>
							<label class="ghpb-control-label">To</label>
							<div class="ghpb-color-control ghpb-bg-grad-to" data-sub="gradient_to">
								<div class="ghpb-color-preview" style="background:${ this._esc( bv.gradient_to || '#574fd6' ) }"></div>
								<input type="color" class="ghpb-color-native" value="${ bv.gradient_to || '#574fd6' }">
								<input type="text" class="ghpb-color-text ghpb-input" value="${ this._esc( bv.gradient_to || '' ) }" placeholder="#574fd6">
							</div>
							<label class="ghpb-control-label">Angle</label>
							<input type="number" class="ghpb-input" data-sub="gradient_angle" value="${ bv.gradient_angle || 180 }" min="0" max="360">
						</div>
					</div>
				`;
			}

			case 'typography': {
				const tv = typeof value === 'object' && value ? value : {};
				const weights = [ '', '100', '200', '300', '400', '500', '600', '700', '800', '900', 'normal', 'bold' ];
				const transforms = [ [ '', 'Default' ], [ 'none', 'Normal' ], [ 'uppercase', 'UPPER' ], [ 'lowercase', 'lower' ], [ 'capitalize', 'Caps' ] ];
				const fontStyles = [ [ '', 'Default' ], [ 'normal', 'Normal' ], [ 'italic', 'Italic' ], [ 'oblique', 'Oblique' ] ];
				const sz = tv.font_size || { size: '', unit: 'px' };
				const lh = tv.line_height || { size: '', unit: '' };
				const ls = tv.letter_spacing || { size: '', unit: 'px' };
				return `
					<div class="ghpb-typography" data-key="${ name }">
						<div class="ghpb-typo-row">
							<label class="ghpb-control-label">Family</label>
							<input type="text" class="ghpb-input" data-sub="font_family" value="${ this._esc( tv.font_family || '' ) }" placeholder="Roboto, sans-serif" list="ghpb-fonts-list">
							<datalist id="ghpb-fonts-list">
								${ [
									'Arial', 'Roboto', 'Open Sans', 'Lato', 'Montserrat', 'Oswald',
									'Raleway', 'Poppins', 'Nunito', 'Playfair Display', 'Merriweather',
									'Source Sans Pro', 'Ubuntu', 'PT Sans', 'Noto Sans', 'Inter',
									'Georgia', 'Times New Roman', 'Verdana', 'Helvetica',
								].map( f => `<option value="${ f }">` ).join( '' )
								}
							</datalist>
						</div>
						<div class="ghpb-typo-row">
							<label class="ghpb-control-label">Size</label>
							<div class="ghpb-slider-control">
								<input type="range" class="ghpb-slider ghpb-typo-size-range" min="8" max="100" value="${ sz.size || 16 }">
								<input type="number" class="ghpb-slider-number ghpb-input ghpb-typo-size-num" data-sub="font_size_size" value="${ sz.size || '' }">
								<select class="ghpb-slider-unit ghpb-select ghpb-typo-size-unit" data-sub="font_size_unit">
									${ [ 'px', 'em', 'rem', 'vw' ].map( u => `<option ${ u === sz.unit ? 'selected' : '' }>${ u }</option>` ).join( '' ) }
								</select>
							</div>
						</div>
						<div class="ghpb-typo-row">
							<label class="ghpb-control-label">Weight</label>
							<select class="ghpb-select" data-sub="font_weight">
								${ weights.map( w => `<option value="${ w }" ${ w === tv.font_weight ? 'selected' : '' }>${ w || 'Default' }</option>` ).join( '' ) }
							</select>
						</div>
						<div class="ghpb-typo-row">
							<label class="ghpb-control-label">Style</label>
							<div class="ghpb-choose ghpb-typo-style">
								${ fontStyles.map( ( [ v, l ] ) => `<button type="button" class="ghpb-choose-btn ${ ( tv.font_style || '' ) === v ? 'active' : '' }" data-sub="font_style" data-value="${ v }">${ l }</button>` ).join( '' ) }
							</div>
						</div>
						<div class="ghpb-typo-row">
							<label class="ghpb-control-label">Transform</label>
							<div class="ghpb-choose ghpb-typo-transform">
								${ transforms.map( ( [ v, l ] ) => `<button type="button" class="ghpb-choose-btn ${ ( tv.text_transform || '' ) === v ? 'active' : '' }" data-sub="text_transform" data-value="${ v }">${ l }</button>` ).join( '' ) }
							</div>
						</div>
						<div class="ghpb-typo-row">
							<label class="ghpb-control-label">Line Height</label>
							<div class="ghpb-slider-control">
								<input type="range" class="ghpb-slider ghpb-typo-lh-range" min="0" max="5" step="0.1" value="${ lh.size || 1.5 }">
								<input type="number" class="ghpb-slider-number ghpb-input ghpb-typo-lh-num" data-sub="line_height_size" value="${ lh.size || '' }" step="0.1">
								<select class="ghpb-slider-unit ghpb-select ghpb-typo-lh-unit" data-sub="line_height_unit">
									${ [ '', 'px', 'em' ].map( u => `<option ${ u === lh.unit ? 'selected' : '' }>${ u || '—' }</option>` ).join( '' ) }
								</select>
							</div>
						</div>
						<div class="ghpb-typo-row">
							<label class="ghpb-control-label">Letter Spacing</label>
							<div class="ghpb-slider-control">
								<input type="range" class="ghpb-slider ghpb-typo-ls-range" min="-5" max="20" step="0.1" value="${ ls.size || 0 }">
								<input type="number" class="ghpb-slider-number ghpb-input ghpb-typo-ls-num" data-sub="letter_spacing_size" value="${ ls.size || '' }" step="0.1">
								<span style="font-size:11px;color:#888;">px</span>
							</div>
						</div>
					</div>
				`;
			}

			case 'repeater': {
				const rows   = Array.isArray( value ) ? value : ( Array.isArray( control.default ) ? control.default : [] );
				const fields = control.fields || [];

				const rowsHTML = rows.map( ( row, rowIndex ) => {
					const rowLabel = ( row[ fields[ 0 ]?.name ] ) || `Item ${ rowIndex + 1 }`;
					const fieldsHTML = fields.map( field => {
						const fv  = row[ field.name ] !== undefined ? row[ field.name ] : ( field.default !== undefined ? field.default : '' );
						return `
							<div class="ghpb-control ghpb-rep-field" data-field="${ field.name }">
								${ field.label ? `<label class="ghpb-control-label">${ field.label }</label>` : '' }
								${ this._renderControlInput( field, fv ) }
							</div>
						`;
					} ).join( '' );
					return `
						<div class="ghpb-repeater-row" data-row="${ rowIndex }">
							<div class="ghpb-repeater-row-header">
								<span class="ghpb-repeater-row-label">${ this._esc( String( rowLabel ) ) }</span>
								<div class="ghpb-repeater-row-actions">
									<button type="button" class="ghpb-repeater-row-action" data-action="duplicate" title="Duplicate">
										<span class="dashicons dashicons-admin-page"></span>
									</button>
									<button type="button" class="ghpb-repeater-row-action" data-action="delete" title="Delete">
										<span class="dashicons dashicons-trash"></span>
									</button>
								</div>
							</div>
							<div class="ghpb-repeater-row-content">
								${ fieldsHTML }
							</div>
						</div>
					`;
				} ).join( '' );

				return `
					<div class="ghpb-repeater" data-key="${ name }">
						<div class="ghpb-repeater-rows">${ rowsHTML }</div>
						<button type="button" class="ghpb-repeater-add">
							<span class="dashicons dashicons-plus-alt2"></span> Add Item
						</button>
					</div>
				`;
			}

			default:
				return `<div class="ghpb-unsupported" style="font-size:11px;color:#888;">(${ type })</div>`;
		}
	},

	_esc( val ) {
		return String( val )
			.replace( /&/g, '&amp;' )
			.replace( /"/g, '&quot;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' );
	},

	_bindControls() {
		const self = this;

		// ── Text / textarea / number / code ──────────────────────────────────
		this.el.querySelectorAll( '.ghpb-text-input, .ghpb-textarea, .ghpb-number-input, .ghpb-code-input' ).forEach( el => {
			el.addEventListener( 'input', function() {
				self.onChange( this.dataset.key, this.value );
			} );
		} );

		// ── Select ────────────────────────────────────────────────────────────
		this.el.querySelectorAll( '.ghpb-select' ).forEach( el => {
			el.addEventListener( 'change', function() {
				if ( this.dataset.key ) self.onChange( this.dataset.key, this.value );
			} );
		} );

		// ── Choose buttons ────────────────────────────────────────────────────
		this.el.querySelectorAll( '.ghpb-choose:not(.ghpb-typo-style):not(.ghpb-typo-transform)' ).forEach( group => {
			group.querySelectorAll( '.ghpb-choose-btn' ).forEach( btn => {
				btn.addEventListener( 'click', function() {
					group.querySelectorAll( '.ghpb-choose-btn' ).forEach( b => b.classList.remove( 'active' ) );
					this.classList.add( 'active' );
					self.onChange( group.dataset.key, this.dataset.value );
				} );
			} );
		} );

		// ── Switcher ──────────────────────────────────────────────────────────
		this.el.querySelectorAll( '.ghpb-switcher' ).forEach( sw => {
			const input = sw.querySelector( '.ghpb-switcher-input' );
			input?.addEventListener( 'change', function() {
				self.onChange( sw.dataset.key, this.checked ? 'yes' : '' );
			} );
		} );

		// ── Color ─────────────────────────────────────────────────────────────
		this.el.querySelectorAll( '.ghpb-color-control:not([data-sub])' ).forEach( ctrl => {
			this._bindColorControl( ctrl, ( val ) => self.onChange( ctrl.dataset.key, val ) );
		} );

		// ── Slider ────────────────────────────────────────────────────────────
		this.el.querySelectorAll( '.ghpb-slider-control' ).forEach( ctrl => {
			if ( ctrl.closest( '.ghpb-typography' ) ) return; // handled by typography binder
			const range  = ctrl.querySelector( '.ghpb-slider' );
			const num    = ctrl.querySelector( '.ghpb-slider-number' );
			const unitEl = ctrl.querySelector( '.ghpb-slider-unit' );
			const key    = ctrl.dataset.key;
			if ( ! key ) return;

			const emit = () => {
				self.onChange( key, {
					size: parseFloat( num?.value ?? 0 ) || 0,
					unit: unitEl?.value || 'px',
				} );
			};

			range?.addEventListener( 'input', function() { if ( num ) num.value = this.value; emit(); } );
			num?.addEventListener( 'input', function() { if ( range ) range.value = this.value; emit(); } );
			unitEl?.addEventListener( 'change', emit );
		} );

		// ── Dimensions ────────────────────────────────────────────────────────
		this.el.querySelectorAll( '.ghpb-dimensions' ).forEach( ctrl => {
			const key   = ctrl.dataset.key;
			let linked  = ctrl.querySelector( '.ghpb-dim-link' )?.classList.contains( 'active' );

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
					if ( linked ) ctrl.querySelectorAll( '.ghpb-dim-input' ).forEach( i => { i.value = this.value; } );
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

		// ── URL ───────────────────────────────────────────────────────────────
		this.el.querySelectorAll( '.ghpb-url-control' ).forEach( ctrl => {
			const key  = ctrl.dataset.key;
			const emit = () => {
				self.onChange( key, {
					url:         ctrl.querySelector( '[data-sub="url"]' )?.value || '',
					is_external: ctrl.querySelector( '[data-sub="is_external"]' )?.checked ? 'on' : '',
					nofollow:    ctrl.querySelector( '[data-sub="nofollow"]' )?.checked ? 'on' : '',
				} );
			};
			ctrl.querySelectorAll( 'input[type="url"]' ).forEach( i => i.addEventListener( 'input', emit ) );
			ctrl.querySelectorAll( 'input[type="checkbox"]' ).forEach( i => i.addEventListener( 'change', emit ) );
		} );

		// ── Media ─────────────────────────────────────────────────────────────
		this.el.querySelectorAll( '.ghpb-media-control' ).forEach( ctrl => {
			const key = ctrl.dataset.key;
			ctrl.querySelector( '.ghpb-media-select' )?.addEventListener( 'click', function() {
				if ( ! window.wp?.media ) return;
				const frame = wp.media( { title: 'Choose Image', button: { text: 'Use This Image' }, multiple: false } );
				frame.on( 'select', function() {
					const att = frame.state().get( 'selection' ).first().toJSON();
					ctrl.querySelector( '.ghpb-media-url' ).value = att.url;
					ctrl.querySelector( '.ghpb-media-id' ).value  = att.id;
					self.onChange( key, { url: att.url, id: att.id, alt: att.alt || '' } );
				} );
				frame.open();
			} );
			ctrl.querySelector( '.ghpb-media-remove' )?.addEventListener( 'click', () => {
				self.onChange( key, { url: '', id: 0, alt: '' } );
			} );
		} );

		// ── Icon text ─────────────────────────────────────────────────────────
		this.el.querySelectorAll( '.ghpb-icon-control' ).forEach( ctrl => {
			const key = ctrl.dataset.key;
			ctrl.querySelector( '.ghpb-icon-input' )?.addEventListener( 'input', function() {
				self.onChange( key, { value: this.value, library: 'dashicons' } );
			} );
		} );

		// ── Background ────────────────────────────────────────────────────────
		this.el.querySelectorAll( '.ghpb-background-control' ).forEach( ctrl => {
			const key = ctrl.dataset.key;

			const getVal = () => {
				const bg = ctrl.querySelector( '.ghpb-bg-type' )?.value || 'classic';
				const val = { background: bg };
				if ( bg === 'gradient' ) {
					val.gradient_from  = ctrl.querySelector( '[data-sub="gradient_from"] .ghpb-color-text' )?.value || '';
					val.gradient_to    = ctrl.querySelector( '[data-sub="gradient_to"] .ghpb-color-text' )?.value || '';
					val.gradient_angle = ctrl.querySelector( '[data-sub="gradient_angle"]' )?.value || 180;
				} else {
					val.color = ctrl.querySelector( '.ghpb-bg-classic .ghpb-color-text' )?.value || '';
				}
				return val;
			};

			// Type toggle
			ctrl.querySelectorAll( '.ghpb-bg-type-btn' ).forEach( btn => {
				btn.addEventListener( 'click', function() {
					ctrl.querySelectorAll( '.ghpb-bg-type-btn' ).forEach( b => b.classList.remove( 'active' ) );
					this.classList.add( 'active' );
					const bg = this.dataset.bg;
					ctrl.querySelector( '.ghpb-bg-type' ).value = bg;
					ctrl.querySelector( '.ghpb-bg-classic' ).classList.toggle( 'ghpb-hidden', bg === 'gradient' );
					ctrl.querySelector( '.ghpb-bg-gradient' ).classList.toggle( 'ghpb-hidden', bg !== 'gradient' );
					self.onChange( key, getVal() );
				} );
			} );

			// Color sub-controls
			ctrl.querySelectorAll( '.ghpb-color-control' ).forEach( cc => {
				self._bindColorControl( cc, () => self.onChange( key, getVal() ) );
			} );

			ctrl.querySelector( '[data-sub="gradient_angle"]' )?.addEventListener( 'input', () => self.onChange( key, getVal() ) );
		} );

		// ── Typography ────────────────────────────────────────────────────────
		this.el.querySelectorAll( '.ghpb-typography' ).forEach( ctrl => {
			const key = ctrl.dataset.key;

			const getVal = () => {
				const sizeNum  = ctrl.querySelector( '.ghpb-typo-size-num' );
				const sizeUnit = ctrl.querySelector( '.ghpb-typo-size-unit' );
				const lhNum    = ctrl.querySelector( '.ghpb-typo-lh-num' );
				const lhUnit   = ctrl.querySelector( '.ghpb-typo-lh-unit' );
				const lsNum    = ctrl.querySelector( '.ghpb-typo-ls-num' );

				const getChoiceVal = ( groupClass ) => {
					return ctrl.querySelector( `.${ groupClass } .ghpb-choose-btn.active` )?.dataset.value || '';
				};

				return {
					font_family:    ctrl.querySelector( '[data-sub="font_family"]' )?.value || '',
					font_size:      { size: parseFloat( sizeNum?.value ) || '', unit: sizeUnit?.value || 'px' },
					font_weight:    ctrl.querySelector( '[data-sub="font_weight"]' )?.value || '',
					font_style:     getChoiceVal( 'ghpb-typo-style' ),
					text_transform: getChoiceVal( 'ghpb-typo-transform' ),
					line_height:    { size: parseFloat( lhNum?.value ) || '', unit: ( lhUnit?.value || '' ).replace( '—', '' ) },
					letter_spacing: { size: parseFloat( lsNum?.value ) || 0, unit: 'px' },
				};
			};

			// Sync range ↔ number
			const addRangeSync = ( rangeClass, numClass ) => {
				const range = ctrl.querySelector( `.${ rangeClass }` );
				const num   = ctrl.querySelector( `.${ numClass }` );
				if ( range && num ) {
					range.addEventListener( 'input', function() { num.value = this.value; self.onChange( key, getVal() ); } );
					num.addEventListener( 'input', function() { range.value = this.value; self.onChange( key, getVal() ); } );
				}
			};
			addRangeSync( 'ghpb-typo-size-range', 'ghpb-typo-size-num' );
			addRangeSync( 'ghpb-typo-lh-range', 'ghpb-typo-lh-num' );
			addRangeSync( 'ghpb-typo-ls-range', 'ghpb-typo-ls-num' );

			ctrl.querySelectorAll( 'select, input[type="text"], input[type="number"]' ).forEach( el => {
				el.addEventListener( 'change', () => self.onChange( key, getVal() ) );
				el.addEventListener( 'input', () => self.onChange( key, getVal() ) );
			} );

			// Style/transform choose buttons
			ctrl.querySelectorAll( '.ghpb-typo-style .ghpb-choose-btn, .ghpb-typo-transform .ghpb-choose-btn' ).forEach( btn => {
				btn.addEventListener( 'click', function() {
					this.closest( '.ghpb-choose' ).querySelectorAll( '.ghpb-choose-btn' ).forEach( b => b.classList.remove( 'active' ) );
					this.classList.add( 'active' );
					self.onChange( key, getVal() );
				} );
			} );

			// Size unit change updates range min/max
			ctrl.querySelector( '.ghpb-typo-size-unit' )?.addEventListener( 'change', () => self.onChange( key, getVal() ) );
		} );

		// ── Repeater ─────────────────────────────────────────────────────────
		this.el.querySelectorAll( '.ghpb-repeater' ).forEach( ctrl => {
			const key     = ctrl.dataset.key;
			const control = ( this.widgetConfig.controls || [] ).find( c => c.name === key );
			const fields  = control?.fields || [];

			const getData = () => {
				const rows = [];
				ctrl.querySelectorAll( '.ghpb-repeater-row' ).forEach( row => {
					const rowData = {};
					fields.forEach( field => {
						const fieldEl = row.querySelector( `[data-field="${ field.name }"]` );
						if ( ! fieldEl ) return;
						const inp = fieldEl.querySelector( 'input:not([type="hidden"]), textarea, select' );
						if ( inp ) rowData[ field.name ] = inp.type === 'checkbox' ? ( inp.checked ? 'yes' : '' ) : inp.value;
					} );
					rows.push( rowData );
				} );
				return rows;
			};

			const bindRowInputs = ( row ) => {
				row.querySelectorAll( 'input, textarea, select' ).forEach( inp => {
					inp.addEventListener( 'change', () => self.onChange( key, getData() ) );
					inp.addEventListener( 'input', () => self.onChange( key, getData() ) );
				} );
			};

			// Bind existing rows
			ctrl.querySelectorAll( '.ghpb-repeater-row' ).forEach( row => {
				bindRowInputs( row );
			} );

			// Row header click (toggle content)
			ctrl.addEventListener( 'click', function( e ) {
				const header = e.target.closest( '.ghpb-repeater-row-header' );
				if ( header && ! e.target.closest( '.ghpb-repeater-row-action' ) ) {
					header.closest( '.ghpb-repeater-row' ).querySelector( '.ghpb-repeater-row-content' )?.classList.toggle( 'open' );
					return;
				}

				// Delete
				const delBtn = e.target.closest( '[data-action="delete"]' );
				if ( delBtn ) {
					delBtn.closest( '.ghpb-repeater-row' )?.remove();
					self.onChange( key, getData() );
					return;
				}

				// Duplicate
				const dupBtn = e.target.closest( '[data-action="duplicate"]' );
				if ( dupBtn ) {
					const row   = dupBtn.closest( '.ghpb-repeater-row' );
					const clone = row.cloneNode( true );
					row.after( clone );
					bindRowInputs( clone );
					self.onChange( key, getData() );
				}
			} );

			// Add new item
			ctrl.querySelector( '.ghpb-repeater-add' )?.addEventListener( 'click', () => {
				const defaults = {};
				fields.forEach( f => { defaults[ f.name ] = f.default !== undefined ? f.default : ''; } );
				const idx = ctrl.querySelectorAll( '.ghpb-repeater-row' ).length;

				const fieldsHTML = fields.map( field => `
					<div class="ghpb-control ghpb-rep-field" data-field="${ field.name }">
						${ field.label ? `<label class="ghpb-control-label">${ this._esc( field.label ) }</label>` : '' }
						${ this._renderControlInput( field, field.default !== undefined ? field.default : '' ) }
					</div>
				` ).join( '' );

				const newRow = document.createElement( 'div' );
				newRow.className = 'ghpb-repeater-row';
				newRow.dataset.row = idx;
				newRow.innerHTML = `
					<div class="ghpb-repeater-row-header">
						<span class="ghpb-repeater-row-label">Item ${ idx + 1 }</span>
						<div class="ghpb-repeater-row-actions">
							<button type="button" class="ghpb-repeater-row-action" data-action="duplicate"><span class="dashicons dashicons-admin-page"></span></button>
							<button type="button" class="ghpb-repeater-row-action" data-action="delete"><span class="dashicons dashicons-trash"></span></button>
						</div>
					</div>
					<div class="ghpb-repeater-row-content open">${ fieldsHTML }</div>
				`;
				ctrl.querySelector( '.ghpb-repeater-rows' ).appendChild( newRow );
				bindRowInputs( newRow );
				self.onChange( key, getData() );
			} );
		} );

		// ── Tab switching ─────────────────────────────────────────────────────
		this.el.querySelectorAll( '.ghpb-tab-btn' ).forEach( btn => {
			btn.addEventListener( 'click', () => {
				this.activeTab = btn.dataset.tab;
				this.render();
			} );
		} );

		// ── Section collapse ──────────────────────────────────────────────────
		this.el.querySelectorAll( '.ghpb-section-header' ).forEach( header => {
			header.addEventListener( 'click', function() {
				this.closest( '.ghpb-control-section' )?.classList.toggle( 'ghpb-collapsed' );
			} );
		} );
	},

	/** Bind a color-control div (native + text + preview) to a callback. */
	_bindColorControl( ctrl, callback ) {
		const native = ctrl.querySelector( '.ghpb-color-native' );
		const text   = ctrl.querySelector( '.ghpb-color-text' );
		const prev   = ctrl.querySelector( '.ghpb-color-preview' );

		native?.addEventListener( 'input', function() {
			if ( text ) text.value = this.value;
			if ( prev ) prev.style.background = this.value;
			callback( this.value );
		} );
		prev?.addEventListener( 'click', () => native?.click() );
		text?.addEventListener( 'input', function() {
			if ( prev ) prev.style.background = this.value;
			callback( this.value );
		} );
	},
} );

export default EditPanelView;
