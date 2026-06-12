/**
 * Global Backbone.Radio channels used throughout the editor.
 * Import this module anywhere to get the shared channel references.
 */
import Radio from 'backbone.radio';

export const editor   = Radio.channel( 'editor' );   // global editor state
export const elements = Radio.channel( 'elements' );  // element tree changes
export const panel    = Radio.channel( 'panel' );     // panel navigation
export const preview  = Radio.channel( 'preview' );   // iframe bridge
export const data     = Radio.channel( 'data' );      // save/dirty state
