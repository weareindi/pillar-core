# Pillar Core

Pillar Core provides the PHP application, Twig integration, console commands, interface templates, and interface assets used by the [Pillar pattern system](https://github.com/weareindi/pillar).

## Requirements

- PHP 8.4 or newer
- Composer 2
- Node.js and npm for interface asset development

## Installation

Pillar projects normally install Core through Composer:

```sh
composer require weareindi/pillar-core
```

The companion Pillar boilerplate can also symlink a sibling `pillar-core` checkout for local development.

## Core asset development

Install the Node dependencies and create the local Gulp environment file:

```sh
npm install
cp .env.gulp.example .env.gulp
```

Available commands:

```sh
npm run build   # compile and minify the interface CSS and JavaScript
npm run watch   # rebuild changed core source files
```

The build uses Gulp 5, Dart Sass, PostCSS, and esbuild. Sass uses the module system (`@use` and `@forward`), with `_src/scss` configured as a load path.

## Runtime performance

Twig templates use an automatically reloading compilation cache. Pattern previews are rendered once, while code-viewer Twig, HTML, and JSON content is loaded and syntax-highlighted only when its Pre or Post panel is opened.

## Version

Current development version: `2.0.0`.
