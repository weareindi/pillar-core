module.exports = (gulp, options, plugins) => {
    gulp.task('production', gulp.series(
        gulp.parallel('scss', 'js'),
        gulp.parallel('scss-minify', 'js-uglify')
    ));
};
