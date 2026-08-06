require('dotenv').config({quiet: true});
require('dotenv').config({
    quiet: true,
    path: './.env.gulp'
});

const gulp = require('gulp');

const plugins = {
    autoprefixer: require('autoprefixer'),
    cssnano: require('cssnano'),
    esbuild: require('esbuild'),
    fs: require('node:fs/promises'),
    path: require('node:path'),
    postcss: require('postcss'),
    sass: require('sass')
};

require('./_gulp/scss.gulp.js')(gulp, {}, plugins);
require('./_gulp/js.gulp.js')(gulp, {}, plugins);
require('./_gulp/watch.gulp.js')(gulp, {}, plugins);
require('./_gulp/production.gulp.js')(gulp, {}, plugins);
require('./_gulp/default.gulp.js')(gulp, {}, plugins);
