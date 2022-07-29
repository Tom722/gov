require.config({
    paths: {
        'jquery-colorpicker': '../addons/ask/js/jquery.colorpicker.min',
    },
    shim: {
        'jquery-colorpicker': {
            deps: ['jquery'],
            exports: '$.fn.extend'
        },
    }
});
