var elixir = require('laravel-elixir');

elixir(function(mix) {
    mix.styles([
        'bootstrap.min.css',
        'ie10-viewport-bug-workaround.css',
        'jumbotron-narrow.css',
        'app.css'
    ], 'public/css/app.css');
    
    mix.scripts([
        'bootstrap.min.js',
        'ie-emulation-modes-warning.js',
        'ie10-viewport-bug-workaround.js',
        'app.js'
    ], 'public/js/app.js');
    
     mix.version(['css/app.css', 'js/app.js']);
});
