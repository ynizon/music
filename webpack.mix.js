let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

 /*
mix.js('resources/assets/js/app.js', 'public/js')
   .sass('resources/assets/sass/app.scss', 'public/css');
*/

mix.scripts([
	'public/js/jquery-3.3.1.min.js',
	'public/js/jquery-ui.min.js',
	'public/js/utils.js',
],'public/js/app.js');
   
mix.styles([
    'public/css/font-awesome.min.css',
	'public/css/bootstrap/bootstrap.min.css',
	'public/css/jquery-ui.min.css',
	'public/css/jquery-ui-1.8.17.custom.css',
	'public/css/styles.css',
], 'public/css/app.css');

mix.js('public/js/app.js', 'public/js').version()
   .sass('resources/sass/app.scss', 'public/css');