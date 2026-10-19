const HtmlWebpackPlugin = require('html-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const path = require('path');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const { defineConfig } = require('webpack');

const isProd = process.env.NODE_ENV === 'production';

module.exports = defineConfig({
  mode: isProd ? 'production' : 'development',
  devtool: isProd ? 'source-map' : 'eval-source-map',
  optimization: {
    minimize: isProd,
  },
  module: {
    rules: [
      {
        test: /\.s[ac]ss$/i,
        exclude: /node_modules/,
        resourceQuery: { not: [/inline/] },
        use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader'],
      },
      {
        test: /\.s[ac]ss$/i,
        exclude: /node_modules/,
        resourceQuery: /inline/,
        use: ['sass-to-string', 'sass-loader'],
      },
      {
        test: /\.css$/i,
        use: [MiniCssExtractPlugin.loader, 'css-loader'],
      },
      {
        test: /\.html$/,
        include: path.resolve(__dirname, 'src/templates'),
        use: ['raw-loader']
      },
      {
        test: /\.m?js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env'],
          },
        },
      },
    ],
  },
  entry: {
    index: './src/js/index-page.js',
    documents: './src/js/document-page.js'
  },
  plugins: [
    new MiniCssExtractPlugin(),
    new HtmlWebpackPlugin({
      template: 'src/index.html',
      chunks: ['index'],
      minify: {
        removeComments: isProd,
        collapseWhitespace: isProd,
      },
      filename: 'index.html',
    }),
    new HtmlWebpackPlugin({
      template: 'src/privacy.html',
      chunks: ['documents'],
      minify: {
        removeComments: isProd,
        collapseWhitespace: isProd,
      },
      filename: 'privacy.html',
    }),
    new HtmlWebpackPlugin({
      template: 'src/consent.html',
      chunks: ['documents'],
      minify: {
        removeComments: isProd,
        collapseWhitespace: isProd,
      },
      filename: 'consent.html',
    }),
    new HtmlWebpackPlugin({
      template: 'src/consent-ads.html',
      chunks: ['documents'],
      minify: {
        removeComments: isProd,
        collapseWhitespace: isProd,
      },
      filename: 'consent-ads.html',
    }),
    new CopyWebpackPlugin({
      patterns: [
        {
          from: path.resolve(__dirname, 'src/assets'),
          to: path.resolve(__dirname, 'dist/assets'),
        },
        {
          from: path.resolve(__dirname, 'src/php'),
          to: path.resolve(__dirname, 'dist/php'),
        },
        {
          from: path.resolve(__dirname, 'src/send.php'),
          to: path.resolve(__dirname, 'dist/send.php'),
        },
      ],
    }),
  ],
  devServer: {
    port: 3000,
    compress: true,
    hot: true,
    static: {
      directory: path.resolve(__dirname, 'src'),
    },
    watchFiles: ['src/**/*.html', 'src/**/*.js', 'src/**/*.scss'],
  },
  output: {
    filename: '[name].js',
    chunkFilename: '[name].chunk.js',
    path: path.resolve(__dirname, 'dist'),
    clean: true,
  },
});
