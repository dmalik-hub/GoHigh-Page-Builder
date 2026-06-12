const path = require( 'path' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );

const isProduction = process.env.NODE_ENV === 'production';

module.exports = [
	{
		name: 'editor',
		entry: './assets/src/editor/editor.js',
		output: {
			path: path.resolve( __dirname, 'dist/js' ),
			filename: 'editor.js',
			library: 'GoHighEditor',
			libraryTarget: 'window',
		},
		module: {
			rules: [
				{
					test: /\.js$/,
					exclude: /node_modules/,
					use: { loader: 'babel-loader' },
				},
				{
					test: /\.scss$/,
					use: [
						MiniCssExtractPlugin.loader,
						'css-loader',
						'sass-loader',
					],
				},
				{
					test: /\.(png|svg|jpg|gif|woff|woff2|eot|ttf|otf)$/i,
					type: 'asset/resource',
					generator: { filename: '../css/fonts/[name][ext]' },
				},
			],
		},
		plugins: [
			new MiniCssExtractPlugin( { filename: '../css/editor.css' } ),
		],
		externals: {
			jquery: 'jQuery',
			underscore: '_',
			backbone: 'Backbone',
			'backbone.marionette': 'Marionette',
			'backbone.radio': 'Backbone.Radio',
		},
		devtool: isProduction ? false : 'source-map',
	},
	{
		name: 'frontend',
		entry: './assets/src/frontend/frontend.js',
		output: {
			path: path.resolve( __dirname, 'dist/js' ),
			filename: 'frontend.js',
		},
		module: {
			rules: [
				{
					test: /\.js$/,
					exclude: /node_modules/,
					use: { loader: 'babel-loader' },
				},
				{
					test: /\.scss$/,
					use: [
						MiniCssExtractPlugin.loader,
						'css-loader',
						'sass-loader',
					],
				},
			],
		},
		plugins: [
			new MiniCssExtractPlugin( { filename: '../css/frontend.css' } ),
		],
		externals: { jquery: 'jQuery' },
		devtool: isProduction ? false : 'source-map',
	},
	{
		name: 'admin',
		entry: './assets/src/admin/admin.js',
		output: {
			path: path.resolve( __dirname, 'dist/js' ),
			filename: 'admin.js',
		},
		module: {
			rules: [
				{
					test: /\.js$/,
					exclude: /node_modules/,
					use: { loader: 'babel-loader' },
				},
				{
					test: /\.scss$/,
					use: [
						MiniCssExtractPlugin.loader,
						'css-loader',
						'sass-loader',
					],
				},
			],
		},
		plugins: [
			new MiniCssExtractPlugin( { filename: '../css/admin.css' } ),
		],
		externals: { jquery: 'jQuery' },
		devtool: isProduction ? false : 'source-map',
	},
];
