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
    .setOutputPath('public/embed/contacts/')
    .setPublicPath(env.HOST + '/embed/contacts')
    .setManifestKeyPrefix('embed/contacts/')
    .addEntry('embed-contacts', './assets/js/embed-contacts.js')
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

const embedContactsConfig = Encore.getWebpackConfig();

embedContactsConfig.name = 'embed-contacts';

Encore.reset();

Encore
    .configureDefinePlugin(options => {
        Object.keys(env).forEach((key) => {
            options['process.env.' + key] = JSON.stringify(env[key]);
        });
        options.__VUE_OPTIONS_API__ = true;
        options.__VUE_PROD_DEVTOOLS__ = false;
    })
    .setOutputPath('public/embed/contacts-latest/')
    .setPublicPath(env.HOST + '/embed/contacts-latest')
    .setManifestKeyPrefix('embed/contacts-latest/')
    .addEntry('embed-contacts-latest', './assets/js/embed-contacts.js')
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

const embedContactsLatestConfig = Encore.getWebpackConfig();

embedContactsLatestConfig.name = 'embed-contacts-latest';

// export the final configuration as an array of multiple configurations
module.exports = [
    embedContactsConfig,
    embedContactsLatestConfig,
];
