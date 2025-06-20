jQuery(document).ready(function($){
    $('.aorp-category').on('click', function(){
        $(this).next('.aorp-items').slideToggle();
    });

    if($('#aorp-toggle').length===0){
        $('body').append('<div id="aorp-toggle">🌓</div>');
    }

    $('#aorp-toggle').on('click', function(){
        $('body').toggleClass('aorp-dark');
    });

    if(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches){
        $('body').addClass('aorp-dark');
    }
});
