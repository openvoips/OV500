import webpack from 'webpack';
import baseConfig from './webpack.config.babel';

let config = Object.create(baseConfig);

config.entry = {
  'jquery.emojiarea.min.js': './src/js/jquery.emojiarea.js',
};

// noinspection JSUnresolvedFunction
config.plugins = [
  new webpack.ProvidePlugin({
    $: 'jquery',
    jQuery: 'jquery',
  }),
  new webpack.optimize.UglifyJsPlugin({
    compress: {
      unused: true,
      dead_code: true,
      warnings: false
    }
  })
];

// noinspection JSUnusedGlobalSymbols
export default config;
