const elixir = require('laravel-elixir');

require('laravel-elixir-vue-2');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function (mix) {
     mix.sass('app.scss')
         .webpack('app.js')
         .copy('./node_modules/bootstrap-sass/assets/fonts', 'public/build/fonts')
         .styles([
             './node_modules/animate.css/animate.min.css',
             './public/css/app.css'
         ])
         .version(['js/app.js', 'css/all.css'])
       ;
});
