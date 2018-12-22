module.exports = (gulp, options, plugins) => {
    gulp.task('watch', gulp.series(
        gulp.parallel('watch-scss', 'watch-js', 'watch-patterns')
    ));

    gulp.task('watch-scss', () => {
        return gulp.watch([
            process.env.SCSS_SRC + '**/*.scss',
            process.env.JS_SRC + '**/*.scss',
            process.env.PWD + '/public/**/*.scss'
        ], gulp.series(
            gulp.parallel('scss')
        ));
    });

    gulp.task('watch-js', () => {
        return gulp.watch([
            process.env.JS_SRC + '**/*.js',
            process.env.PWD + 'public/**/*.js'
        ], gulp.series(
            gulp.parallel('js')
        ));
    });

    gulp.task('watch-patterns', () => {
        return gulp.watch([
            process.env.PWD + '/public/**/*.json',
            process.env.PWD + '/public/**/*.twig'
        ]);
    });
};
