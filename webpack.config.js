const path = require('path');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');
const WebpackNotifierPlugin = require('webpack-notifier');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const RemoveEmptyScripts = require('webpack-remove-empty-scripts');
const CssMinimizerWebpackPlugin = require('css-minimizer-webpack-plugin');
const { getIfUtils, removeEmpty } = require('webpack-config-utils');
const { ifProduction } = getIfUtils(process.env.NODE_ENV);

module.exports = {
	mode: ifProduction('production', 'development'),

	/**
	 * Add your entry files here
	 */
	entry: {
		'css/frontend-acf-form': './source/scss/frontend-acf-form.scss',
	},

	/**
	 * Output settings
	 */
	output: {
		filename: ifProduction('[name].[contenthash].js', '[name].js'),
		path: path.resolve(__dirname, 'dist'),
		publicPath: '',
	},
	/**
	 * Define external dependencies here
	 */
	externals: {},
	module: {
		rules: [
			/**
			 * Babel
			 */
			{
				test: /\.js?/,
				exclude: /(node_modules)/,
        use: [
          {
            loader: 'babel-loader',
            options: {
              presets: [
                ['@babel/preset-env', { targets: 'last 2 versions' }],
                '@babel/preset-react'
              ],
              plugins: [
                '@babel/plugin-syntax-dynamic-import',
                '@babel/plugin-proposal-export-default-from',
                '@babel/plugin-proposal-class-properties'
              ]
            }
          }
        ]
			},
			/**
			 * Styles
			 */
			{
				test: /\.(sa|sc|c)ss$/,
				use: [
					MiniCssExtractPlugin.loader,
					{
						loader: 'css-loader',
						options: {
							importLoaders: 3, // 0 => no loaders (default); 1 => postcss-loader; 2 => sass-loader
						},
					},
					{
						loader: 'postcss-loader',
						options: {},
					},
					{
						loader: 'sass-loader',
						options: {},
					},
					'import-glob-loader',
				],
			},
			/**
			* TypeScript
			*/
			{
				test: /\.ts?$/,
				loader: 'ts-loader',
				options: { allowTsInNodeModules: true }
			},
		],
	},
	resolve: {
		extensions: ['.tsx', '.ts', '.js'],
	},
	plugins: removeEmpty([
	
		/**
		 * Fix CSS entry chunks generating js file
		 */
		new RemoveEmptyScripts(),

		/**
		 * Clean dist folder
		 */
		new CleanWebpackPlugin(),
		/**
		 * Output CSS files
		 */
		new MiniCssExtractPlugin({
			filename: ifProduction('[name].[contenthash:8].css', '[name].css'),
		}),

		/**
		 * Output manifest.json for cache busting
		 */
		new WebpackManifestPlugin({
			filter: function (file) {
				if (file.path.match(/\.(map)$/)) {
					return false;
				}
				return true;
			}
		}),

		/**
		 * Enable build OS notifications (when using watch command)
		 */
		new WebpackNotifierPlugin({ 
      alwaysNotify: true, 
      skipFirstNotification: true 
    }),

		/**
		 * Minimize CSS assets
		 */
		ifProduction(
			new CssMinimizerWebpackPlugin({
				minimizerOptions: {
					preset: [
						'default',
						{
							discardComments: { removeAll: true },
						},
					],
				},
			})
		),
	]).filter(Boolean),
	devtool: 'source-map',
	stats: { children: false },
};