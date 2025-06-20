jQuery(document).ready(function($){
    $('.aorp-category').on('click', function(){
        $(this).next('.aorp-items').slideToggle();
    });

    $('#aorp-search').on('keyup', function(){
        var val = $(this).val().toLowerCase();
        $('.aorp-items').hide();
        $('.aorp-category').each(function(){
            var container = $(this).next('.aorp-items');
            var match = false;
            container.find('.aorp-item').each(function(){
                var text = $(this).text().toLowerCase();
                if(text.indexOf(val) !== -1){
                    $(this).show();
                    match = true;
                }else{
                    $(this).hide();
                }
            });
            if(val === ''){
                container.find('.aorp-item').show();
                container.hide();
                match = true;
            }
            if(match){
                container.show();
            }
        });
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
