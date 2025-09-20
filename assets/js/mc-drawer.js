// mc-drawer.js
jQuery(document).ready(function($){
    // Load Altmetric script dynamically
    var altmetricScript = document.createElement('script');
    altmetricScript.src = 'https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js';
    altmetricScript.async = true;
    document.body.appendChild(altmetricScript);

    // Drawer open/close
    $(document).on('click', '#open-mc-drawer', function(e){
        e.preventDefault();
        if ($('#mc-overlay').length === 0){
            $('body').append('<div id="mc-overlay" class="mc-overlay"></div>');
        }
        $('#mc-overlay').addClass('active');
        $('#mc-drawer').addClass('active');
    });

    $(document).on('click', '.mc-close, #mc-overlay', function(){
        $('#mc-drawer').removeClass('active');
        $('#mc-overlay').removeClass('active').remove();
    });

    $(document).on('keyup', function(e){
        if (e.key === 'Escape'){
            $('#mc-drawer').removeClass('active');
            $('#mc-overlay').removeClass('active').remove();
        }
    });
});
