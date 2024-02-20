const Encore = require('@symfony/webpack-encore');
const {env, sassVariables, embedSassVariables} = require('./webpack.env');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore.reset();

Encore
    .configureDefinePlugin(options => {
        Object.keys(env).forEach((key) => {
            options['process.env.' + key] = JSON.stringify(env[key]);
        });
        options.__VUE_OPTIONS_API__ = true;
        options.__VUE_PROD_DEVTOOLS__ = false;
    })
    .setOutputPath('public/embed/events/')
    .setPublicPath(env.HOST + '/embed/events')
    .setManifestKeyPrefix('embed/events/')
    .addEntry('embed-events', './assets/js/embed-events.js')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    //.enableSourceMaps(!Encore.isProduction())
    .enableSourceMaps(true)
    .enableVersioning(Encore.isProduction())
    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
    .addRule({
        test: /\.ya?ml$/,
        use: 'yaml-loader',
    })
    .enableSassLoader(() => {
        return {
            additionalData: embedSassVariables,
        };
    })
    .enableVueLoader(() => {
    }, {
        runtimeCompilerBuild: false
    })
    .copyFiles({
        from: './assets/images',
        to: 'images/[path][name].[hash:8].[ext]',
        pattern: /\.(png|jpg|jpeg|gif|svg|pdf)$/
    })
    .copyFiles({
        from: './assets/custom/images',
        to: 'custom/images/[path][name].[hash:8].[ext]',
        pattern: /\.(png|jpg|jpeg|gif|svg|pdf)$/
    })
;

const embedEventsConfig = Encore.getWebpackConfig();

embedEventsConfig.name = 'embed-events';

Encore.reset();

Encore
    .configureDefinePlugin(options => {
        Object.keys(env).forEach((key) => {
            options['process.env.' + key] = JSON.stringify(env[key]);
        });
        options.__VUE_OPTIONS_API__ = true;
        options.__VUE_PROD_DEVTOOLS__ = false;
    })
    .setOutputPath('public/embed/events-latest/')
    .setPublicPath(env.HOST + '/embed/events-latest')
    .setManifestKeyPrefix('embed/events-latest/')
    .addEntry('embed-events-latest', './assets/js/embed-events.js')
    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    //.enableSourceMaps(!Encore.isProduction())
    .enableSourceMaps(true)
    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
    .addRule({
        test: /\.ya?ml$/,
        use: 'yaml-loader',
    })
    .enableSassLoader(() => {
        return {
            additionalData: embedSassVariables,
        };
    })
    .enableVueLoader(() => {
    }, {
        runtimeCompilerBuild: false
    })
    .copyFiles({
        from: './assets/images',
        to: 'images/[path][name].[hash:8].[ext]',
        pattern: /\.(png|jpg|jpeg|gif|svg|pdf)$/
    })
    .copyFiles({
        from: './assets/custom/images',
        to: 'custom/images/[path][name].[hash:8].[ext]',
        pattern: /\.(png|jpg|jpeg|gif|svg|pdf)$/
    })
;

const embedEventsLatestConfig = Encore.getWebpackConfig();

embedEventsLatestConfig.name = 'embed-events-latest';

Encore.reset();

Encore
    .configureDefinePlugin(options => {
        Object.keys(env).forEach((key) => {
            options['process.env.' + key] = JSON.stringify(env[key]);
        });
        options.__VUE_OPTIONS_API__ = true;
        options.__VUE_PROD_DEVTOOLS__ = false;
    })
    .setOutputPath('public/embed/event-collection/')
    .setPublicPath(env.HOST + '/embed/event-collection')
    .setManifestKeyPrefix('embed/event-collection/')
    .addEntry('embed-event-collection', './assets/js/embed-event-collection.js')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    //.enableSourceMaps(!Encore.isProduction())
    .enableSourceMaps(true)
    .enableVersioning(Encore.isProduction())
    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
    .addRule({
        test: /\.ya?ml$/,
        use: 'yaml-loader',
    })
    .enableSassLoader(() => {
        return {
            additionalData: embedSassVariables,
        };
    })
    .enableVueLoader(() => {
    }, {
        runtimeCompilerBuild: false
    })
    .copyFiles({
        from: './assets/images',
        to: 'images/[path][name].[hash:8].[ext]',
        pattern: /\.(png|jpg|jpeg|gif|svg|pdf)$/
    })
    .copyFiles({
        from: './assets/custom/images',
        to: 'custom/images/[path][name].[hash:8].[ext]',
        pattern: /\.(png|jpg|jpeg|gif|svg|pdf)$/
    })
;

const embedEventCollectionConfig = Encore.getWebpackConfig();

embedEventCollectionConfig.name = 'embed-event-collection';

Encore.reset();

Encore
    .configureDefinePlugin(options => {
        Object.keys(env).forEach((key) => {
            options['process.env.' + key] = JSON.stringify(env[key]);
        });
        options.__VUE_OPTIONS_API__ = true;
        options.__VUE_PROD_DEVTOOLS__ = false;
    })
    .setOutputPath('public/embed/event-collection-latest/')
    .setPublicPath(env.HOST + '/embed/event-collection-latest')
    .setManifestKeyPrefix('embed/event-collection-latest/')
    .addEntry('embed-event-collection-latest', './assets/js/embed-event-collection.js')
    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    //.enableSourceMaps(!Encore.isProduction())
    .enableSourceMaps(true)
    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
    .addRule({
        test: /\.ya?ml$/,
        use: 'yaml-loader',
    })
    .enableSassLoader(() => {
        return {
            additionalData: embedSassVariables,
        };
    })
    .enableVueLoader(() => {
    }, {
        runtimeCompilerBuild: false
    })
    .copyFiles({
        from: './assets/images',
        to: 'images/[path][name].[hash:8].[ext]',
        pattern: /\.(png|jpg|jpeg|gif|svg|pdf)$/
    })
    .copyFiles({
        from: './assets/custom/images',
        to: 'custom/images/[path][name].[hash:8].[ext]',
        pattern: /\.(png|jpg|jpeg|gif|svg|pdf)$/
    })
;

const embedEventCollectionLatestConfig = Encore.getWebpackConfig();

embedEventCollectionLatestConfig.name = 'embed-event-collection-latest';

// export the final configuration as an array of multiple configurations
module.exports = [
    embedEventsConfig,
    embedEventsLatestConfig,
    embedEventCollectionConfig,
    embedEventCollectionLatestConfig,
];
