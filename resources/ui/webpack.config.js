// This webpack configuration dynamically
// builds itself from key information
// stored in other project files.

const path = require('path');
const fs = require('fs');
const TerserPlugin = require('terser-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const webpack = require('webpack');
let env = process.env.WEBPACK_ENV;

// The folder to output files to. This can be
// overridden by adding a "outputDir" to
// the "project.json" project file.
let outputFolder = 'dist';

// Default to production mode.
if (typeof env === 'undefined') {
    env = 'prod';
}

// Remove any whitespace (can trip up win32).
env = env.trim();

// Load the project settings from "project.json" and "package.json".
const projDefinition = JSON.parse(fs.readFileSync('./project.json'));
const packageDefinition = JSON.parse(fs.readFileSync('./package.json'));

let copyrightText = '';

if (fs.existsSync('./COPYRIGHT.txt')) {
    copyrightText = fs.readFileSync('./COPYRIGHT.txt', 'utf8');
}

copyrightText = copyrightText.trim();

if (typeof projDefinition['outputDir'] !== 'undefined') {
    outputFolder = projDefinition.outputDir;
}

let webpackMode = 'production';
let configPlugins = [];
let extractOptions = {
    filename: '../css/' + projDefinition.name + '.css'
};

if (env === 'prod') {
    webpackMode = 'production';

    configPlugins = [
        new MiniCssExtractPlugin(extractOptions),
        new OptimizeCSSAssetsPlugin({})
    ];

    if (copyrightText.length > 0) {
        configPlugins.push(new webpack.BannerPlugin(copyrightText));
    }
} else {
    webpackMode = 'development';

    configPlugins = [
        new MiniCssExtractPlugin(extractOptions)
    ];

    if (copyrightText.length > 0) {
        configPlugins.push(new webpack.BannerPlugin(copyrightText));
    }
}

// If no entry points have been specified, we will create one from the package.json information.
if (typeof projDefinition['libs'] === 'undefined' || projDefinition.libs.length === 0) {
    let mainEntryPoint = './src/' + packageDefinition.main;
    let libraryName = packageDefinition.name;

    if (typeof projDefinition['name'] !== 'undefined' && projDefinition.name.trim().length > 0) {
        libraryName = projDefinition.name;
    }

    projDefinition.libs = [{
        'entry': mainEntryPoint,
        'library': libraryName
    }];
}

const defaultConfiguration = {
    mode: webpackMode,
    entry: [],
    output: {
        filename: '',
        library: '',
        libraryTarget: 'umd',
        umdNamedDefine: true
    },
    module: {
        rules: [
            {
                test: /\.less$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    'less-loader'
                ]
            },
            {
                test: /\.m?js$/,
                exclude: /(node_modules)/,
                use: {
                    loader: 'babel-loader'
                }
            },
            {
                test: /\.(html)$/,
                use: {
                    loader: 'html-loader'
                }
            }
        ]
    },
    resolve: {
        modules: [path.resolve('./node_modules'), path.resolve('./src')],
        extensions: ['.json', '.js']
    },
    plugins: configPlugins,
    devtool: 'source-map',
    optimization: {
        minimize: true,
        minimizer: [new TerserPlugin()]
    }
};

/**
 * A collection of webpack configuration objects. The
 * configuration objects are built from the entry
 * list in the "project.json" project file.
 */
const config = [];


for (let i = 0; i < projDefinition.libs.length; i += 1) {
    let entryConfig = Object.assign({}, defaultConfiguration);
    let entry = projDefinition.libs[i];
    let userEntries = entry.entry;

    // Convert the user-provided entry into an array.
    if (Array.isArray(userEntries) === false) {
        userEntries = [userEntries];
    }

    // If the project definition just has "library"
    // we can use that as the filename, to make
    // things just a little bit easier to use.
    if (typeof entry.filename === 'undefined') {
        entry.filename = entry.library;
    }

    let fileName = entry.filename + '.js';

    if (env === 'prod') {
        fileName = entry.filename + '.min.js';
    }

    entryConfig.entry = userEntries;
    entryConfig.output = {
        path: path.join(__dirname, '../', outputFolder + '/js/'),
        filename: fileName,
        library: entry.library,
        libraryTarget: 'umd',
        umdNamedDefine: true
    };

    config.push(entryConfig);
}

module.exports = config;
