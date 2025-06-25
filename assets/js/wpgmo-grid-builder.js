jQuery(function($){
    var templates = WPGMO_GB.templates || {};
    var container = $('#wpgmo-template-manager');
    if(!container.length) return;
    var network = container.data('network');

    function render(){
        container.empty();
        $.each(templates, function(slug, tpl){
            var div = $('<div class="wpgmo-template"/>').append('<strong>'+tpl.label+'</strong> (<em>'+slug+'</em>)');
            var btn = $('<button class="button">'+WPGMO_GB.setDefault+'</button>').on('click',function(){setDefault(slug);});
            div.append(btn);
            container.append(div);
        });
    }

    function setDefault(slug){
        $.post(WPGMO_GB.ajaxUrl,{action:'wpgmo_set_default_template',slug:slug,nonce:WPGMO_GB.nonce},render);
    }

    render();
});
