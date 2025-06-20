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

    $('#aorp-close-cats').on('click', function(){
        $('.aorp-items').slideUp();
    });

    if($('#aorp-toggle').length===0){
        $('body').append('<div id="aorp-toggle" aria-label="Dark Mode umschalten" role="button" tabindex="0">'+aorp_ajax.icon_light+'</div>');
    } else {
        $('#aorp-toggle').html(aorp_ajax.icon_light);
    }

    function setDark(active){
        if(active){
            $('body').addClass('aorp-dark');
            $('#aorp-toggle').html(aorp_ajax.icon_dark);
            localStorage.setItem('aorp-dark-mode','on');
        }else{
            $('body').removeClass('aorp-dark');
            $('#aorp-toggle').html(aorp_ajax.icon_light);
            localStorage.setItem('aorp-dark-mode','off');
        }
        $.post(aorp_ajax.url,{action:'aorp_toggle_dark'});
    }

    $('#aorp-toggle').on('click', function(){
        setDark(!$('body').hasClass('aorp-dark'));
    });

    $(document).on('keydown', function(e){
        if(e.ctrlKey && e.altKey && e.key.toLowerCase()=='d'){
            setDark(!$('body').hasClass('aorp-dark'));
        }
    });

    var stored = localStorage.getItem('aorp-dark-mode');
    if(stored){
        setDark(stored==='on');
    }else if(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches){
        setDark(true);
    }
});
