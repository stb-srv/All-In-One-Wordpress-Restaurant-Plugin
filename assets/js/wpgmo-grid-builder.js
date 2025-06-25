jQuery(function($){
    var templates = WPGMO_GB.templates || {};
    var networkSlugs = WPGMO_GB.networkSlugs || [];
    var container = $('#wpgmo-template-manager');
    if(!container.length) return;
    var network = container.data('network');

    function render(){
        container.empty();
        $.each(templates, function(slug, tpl){
            var div = $('<div class="wpgmo-template"/>').append('<strong>'+tpl.label+'</strong> (<em>'+slug+'</em>)');
            var btn = $('<button class="button">'+WPGMO_GB.setDefault+'</button>').on('click',function(){setDefault(slug);});
            var dup = $('<button class="button">'+WPGMO_GB.duplicate+'</button>').on('click',function(){
                var ns = prompt('Slug');
                if(ns){ duplicateTemplate(slug,ns); }
            });
            if(!WPGMO_GB.isNetwork && networkSlugs.indexOf(slug) !== -1){
                dup.prop('disabled', true);
            }
            div.append(btn).append(dup);
            container.append(div);
        });
    }

    function setDefault(slug){
        $.post(WPGMO_GB.ajaxUrl,{action:'wpgmo_set_default_template',slug:slug,nonce:WPGMO_GB.nonce},render);
    }

    function duplicateTemplate(slug,newSlug){
        $.post(WPGMO_GB.ajaxUrl,{action:'wpgmo_duplicate_template',slug:slug,new_slug:newSlug,nonce:WPGMO_GB.nonce},function(resp){if(resp.success){templates[newSlug]=templates[slug];render();}});
    }

    render();
});
