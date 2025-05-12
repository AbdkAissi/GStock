const Encore = require('@symfony/webpack-encore');
const path = require('path');

Encore
    // Répertoire de sortie
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .enableSassLoader()

    // Entrées JS principales
    .addEntry('app', './assets/app.js')
    .addEntry('admin', './assets/admin.js')
    .addEntry('auto-prix-achat', './assets/js/auto-prix-achat.js')
    .addEntry('auto-prix-vente', './assets/js/auto-prix-vente.js')
    .addEntry('paiement', './assets/js/paiement.js')
    .addEntry('easyadmin-ligne-commande', './assets/js/easyadmin-ligne-commande.js')
    // Ajoute d'autres fichiers si besoin

    // Active React, Vue, Stimulus ou autre si besoin
    .enableStimulusBridge('./assets/controllers.json')

    // Active Sass/SCSS
    .enableSassLoader()

    // PostCSS (utile pour compatibilité navigateur)
    .enablePostCssLoader()

    // Génère un seul fichier runtime
    .enableSingleRuntimeChunk()

    // Active sourcemaps en dev
    .enableSourceMaps(!Encore.isProduction())

    // Nettoie le dossier /build à chaque compilation
    .cleanupOutputBeforeBuild()

    // Hashage des fichiers pour la production
    .enableVersioning(Encore.isProduction())

    // Active l'intégration de jQuery si besoin
    //.autoProvidejQuery()

    // Active Babel avec polyfills automatiques
    .configureBabel(() => {}, {
        useBuiltIns: 'usage',
        corejs: 3
    })

    // Affiche les erreurs de build plus clairement
    .enableBuildNotifications()
;

module.exports = Encore.getWebpackConfig();
