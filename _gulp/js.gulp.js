module.exports = (gulp, options, plugins) => {
    const source = () => plugins.path.join(process.env.JS_SRC, 'pillar-script.js');
    const destination = () => plugins.path.join(process.env.JS_DEST, 'pillar-script.js');

    const build = (minify = false) => plugins.esbuild.build({
        bundle: true,
        entryPoints: [source()],
        minify,
        outfile: destination(),
        platform: 'browser',
        sourcemap: false,
        target: ['es2018']
    });

    gulp.task('js-script', () => build());
    gulp.task('js-uglify', () => build(true));

    gulp.task('js', gulp.series(
        gulp.parallel('js-script')
    ));
};
