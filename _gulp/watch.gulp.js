module.exports = (gulp, options, plugins) => {
    gulp.task('watch-scss', () => {
        return gulp.watch([
            plugins.path.join(process.env.SCSS_SRC, '**/*.scss'),
            plugins.path.join(process.env.JS_SRC, '**/*.scss'),
            plugins.path.join(process.cwd(), 'public/**/*.scss')
        ], gulp.series(
            gulp.parallel('scss')
        ));
    });

    gulp.task('watch-js', () => {
        return gulp.watch([
            plugins.path.join(process.env.JS_SRC, '**/*.js'),
            plugins.path.join(process.cwd(), 'public/**/*.js')
        ], gulp.series(
            gulp.parallel('js')
        ));
    });

    gulp.task('watch-patterns', () => {
        return gulp.watch([
            plugins.path.join(process.cwd(), 'public/**/*.json'),
            plugins.path.join(process.cwd(), 'public/**/*.twig')
        ]);
    });

    gulp.task('watch', gulp.series(
        gulp.parallel('watch-scss', 'watch-js', 'watch-patterns')
    ));
};
