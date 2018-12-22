require('events').EventEmitter.prototype._maxListeners = 100;
require('dotenv').config(); // Loads default .env file variables into `process.env`
require('dotenv').config({ // Loads gulp .env file variables into `process.env`
    path: './.env.gulp'
});

const gulp = require('gulp');
const forwardRef = require('undertaker-forward-reference');

const plugins = {
    autoprefixer: require('autoprefixer'),
    babelify: require('babelify'),
    browserify: require('browserify'),
    browsersync: require('browser-sync').create(),
    cssnano: require('cssnano'),
    log: require('fancy-log'),
    postcss: require('gulp-postcss'),
    sass: require('gulp-sass'),
    uglify: require('gulp-uglify'),
    vinylBuffer: require('vinyl-buffer'),
    vinylSource: require('vinyl-source-stream')
};

gulp.registry(forwardRef());

require('load-gulp-tasks')(gulp, {
    pattern: ['_gulp/**/*.gulp.js']
}, plugins);
