module.exports = (gulp, options, plugins) => {
    const source = () => plugins.path.join(process.env.SCSS_SRC, 'pillar-style.scss');
    const destination = () => plugins.path.join(process.env.SCSS_DEST, 'pillar-style.css');

    const compile = async () => {
        const result = plugins.sass.compile(source(), {
            loadPaths: [
                plugins.path.join(process.cwd(), '_src/scss'),
                process.cwd()
            ],
            style: 'expanded'
        });
        const processed = await plugins.postcss([
            plugins.autoprefixer()
        ]).process(result.css, {
            from: source(),
            to: destination()
        });

        await plugins.fs.mkdir(process.env.SCSS_DEST, {recursive: true});
        await plugins.fs.writeFile(destination(), processed.css);
    };

    const minify = async () => {
        const css = await plugins.fs.readFile(destination(), 'utf8');
        const processed = await plugins.postcss([
            plugins.cssnano({preset: 'default'})
        ]).process(css, {
            from: destination(),
            to: destination()
        });

        await plugins.fs.writeFile(destination(), processed.css);
    };

    gulp.task('scss-css', compile);

    gulp.task('scss-minify', minify);

    gulp.task('scss', gulp.series(
        gulp.parallel('scss-css')
    ));
};
