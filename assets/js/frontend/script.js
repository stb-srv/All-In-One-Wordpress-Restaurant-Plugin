jQuery(document).ready(function($){
    $('.aorp-category').on('click', function(){
        $(this).next('.aorp-items').slideToggle();
    });



    function performSearch(val){
        val = val.toLowerCase();
        var list = $('#aorp-search-results');
        list.empty();
        if(val === ''){
            return;
        }
        $('.aorp-item').each(function(){
            if($(this).text().toLowerCase().indexOf(val) !== -1){
                list.append($(this).clone());
            }
        });
    }

    $('#aorp-search-input').on('input', function(){
        performSearch($(this).val());
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
        $.post(aorp_ajax.url,{action:'aorp_toggle_dark',mode:active?'on':'off'});
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
    var cookieMatch = document.cookie.match(/aorp_dark_mode=(on|off)/);
    if(stored){
        setDark(stored==='on');
    }else if(cookieMatch){
        setDark(cookieMatch[1]==='on');
    }else if(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches){
        setDark(true);
    }
});
