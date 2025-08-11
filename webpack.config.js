import path from 'path';
import { fileURLToPath } from 'url';
import HtmlWebpackPlugin from 'html-webpack-plugin';
import MiniCssExtractPlugin from 'mini-css-extract-plugin';
const isProduction = process.env.NODE_ENV === 'production';
import CopyWebpackPlugin from 'copy-webpack-plugin';

// Исправляем __dirname для ES-модулей
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

console.log(`isProduction: ${isProduction}`);

export default {
  mode: isProduction ? 'production' : 'development',
  entry: './src/index.js',
  output: {
    clean: true,
    path: path.resolve(__dirname, 'dist/public'),
    filename: 'static/script.js',
    environment: {
      arrowFunction: true
    }
  },
  devServer: {
    static: './src',
    open: true,
    port: 8001,
    host: 'localhost',
    hot: true,
  },
  module: {
    rules: [
      {
        test: /\.twig$/,
        use: [{ loader: 'twig-loader' }]
      },
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env', '@babel/preset-react'],
          },
        },
      },
      {
        test: /\.s[ac]ss|css$/i,
        use: [isProduction ? MiniCssExtractPlugin.loader : 'style-loader', 'css-loader', 'postcss-loader'],
      },
      {
        test: /\.(ttf|woff2)$/i,
        type: 'asset/resource',
        generator: { filename: '../static/fonts/[name][ext]' }
      },
    ],
  },
  plugins: [
    new HtmlWebpackPlugin({
      template: './src/templates/index.twig',
      inject: false,
    }),
    new MiniCssExtractPlugin({
      filename: 'static/styles.css'
    }),
    new CopyWebpackPlugin({
      patterns: [
        { from: path.resolve(__dirname, 'src/static'), to: path.resolve(__dirname, 'dist/public/static') },
        { from: path.resolve(__dirname, 'src/php'), to: path.resolve(__dirname, 'dist/') },
        { from: path.resolve(__dirname, 'src/templates'), to: path.resolve(__dirname, 'dist/templates') },
      ],
    }),

  ]


};