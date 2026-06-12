/**
 * REST API wrapper using wp.apiFetch.
 */
const api = {
	get( path ) {
		return wp.apiFetch( { path: `/gohigh/v1/${ path }`, method: 'GET' } );
	},

	post( path, data ) {
		return wp.apiFetch( {
			path: `/gohigh/v1/${ path }`,
			method: 'POST',
			data,
		} );
	},

	async getDocument( postId ) {
		return this.get( `documents/${ postId }` );
	},

	async saveDocument( postId, elements, pageSettings = {}, status = 'publish' ) {
		return this.post( `documents/${ postId }`, { elements, page_settings: pageSettings, status } );
	},

	async autosave( postId, elements, pageSettings = {} ) {
		return this.post( `documents/${ postId }/autosave`, { elements, page_settings: pageSettings } );
	},

	async getWidgetPreview( postId, widgetType, settings ) {
		return this.post( 'elements/preview', { post_id: postId, widget_type: widgetType, settings } );
	},
};

export default api;
