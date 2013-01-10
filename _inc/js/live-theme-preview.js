(function($) {
    var blog_url, previewed_theme, previewed_theme_template, iframe;

    $(document).ready( function() {
        // Get some vars from our template
        blog_url = liveAdmin.blog_url,
            previewed_theme = liveAdmin.previewed_theme,
            previewed_theme_template = liveAdmin.previewed_theme_template,
            iframe = $('.wp-full-overlay-main iframe');

        // Fade iFrame in onload
        iframe.load(function() {
            iframe.fadeIn();
        });

        // Load iFrame
        preview_theme( previewed_theme_template, previewed_theme );

        // The action
        $('#live-admin-theme-controls .thumbnail').click( function() {
            var stylesheet = this.id;
            var template = $("input." + stylesheet ).val();

            preview_theme( template, stylesheet);
        });

    });

    function preview_theme( template, stylesheet ) {

        var new_src = blog_url + '/?preview=1&preview_iframe=1&template=' + template + '&stylesheet=' + stylesheet;
        if ( iframe.attr('src') == new_src ) return;

        iframe.fadeOut( function() { $(this).attr( "src", new_src ) } );

        var selected  = $('div#'+ stylesheet);

        $('.selected_theme').removeClass('selected_theme');
        selected.addClass('selected_theme');

    };

})(jQuery);