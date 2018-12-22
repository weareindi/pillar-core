module.exports = (gulp, options, plugins) => {
    gulp.task('scss', gulp.series(
        gulp.parallel('scss-css')
    ));

    gulp.task('scss-css', () => {
        return gulp.src(process.env.SCSS_SRC + 'pillar-style.scss')
            .pipe(plugins.sass())
            .pipe(plugins.postcss([
                plugins.autoprefixer()
            ]))
            .pipe(gulp.dest(process.env.SCSS_DEST))
            .on('error', plugins.log.error);
    });

    gulp.task('scss-minify', () => {
        return gulp.src([
            process.env.SCSS_DEST + 'pillar-style.css'
        ])
            .pipe(plugins.postcss([
                plugins.cssnano({
                    preset: 'default'
                })
            ]))
            .pipe(gulp.dest(process.env.SCSS_DEST))
            .on('error', plugins.log.error);
    });
};
