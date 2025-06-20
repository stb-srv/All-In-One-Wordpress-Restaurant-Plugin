jQuery(document).ready(function($){
    $('#aorp-item-filter').on('keyup', function(){
        var val = $(this).val().toLowerCase();
        $('#aorp-items-table tbody tr').each(function(){
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(val) !== -1);
        });
    });

    $('#aorp-cat-filter').on('keyup', function(){
        var val = $(this).val().toLowerCase();
        $('#aorp-cat-table tbody tr').each(function(){
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(val) !== -1);
        });
    });

    $('#aorp-ing-filter').on('keyup', function(){
        var val = $(this).val().toLowerCase();
        $('#aorp-ing-table tbody tr').each(function(){
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(val) !== -1);
        });
    });

    function updateInput(container){
        var list = [];
        container.find('.aorp-ing-chip').each(function(){
            list.push($(this).data('val'));
        });
        container.closest('form').find('.aorp-ing-text').val(list.join(', '));
    }

    $('.aorp-ing-select').on('change', function(){
        var ing = $(this).val();
        if(ing){
            var form = $(this).closest('form');
            form.find('.aorp-selected').append('<span class="aorp-ing-chip" data-val="'+ing+'">'+ing+' <a href="#" class="aorp-remove-ing">x</a></span> ');
            $(this).find('option[value="'+ing+'"]').remove();
            $(this).val('');
            updateInput(form);
        }
    });

    $(document).on('click','.aorp-remove-ing',function(e){
        e.preventDefault();
        var chip = $(this).closest('.aorp-ing-chip');
        var ing = chip.data('val');
        var form = chip.closest('form');
        form.find('.aorp-ing-select').append('<option value="'+ing+'">'+ing+'</option>');
        chip.remove();
        updateInput(form);
    });

    $('.aorp-ing-text').each(function(){
        var form = $(this).closest('form');
        var val = $(this).val();
        if(val){
            var arr = val.split(',');
            for(var i=0;i<arr.length;i++){
                var ing = $.trim(arr[i]);
                if(!ing) continue;
                form.find('.aorp-selected').append('<span class="aorp-ing-chip" data-val="'+ing+'">'+ing+' <a href="#" class="aorp-remove-ing">x</a></span> ');
                form.find('.aorp-ing-select option[value="'+ing+'"]').remove();
            }
        }
    });

    var aorp_frame;
    $(document).on('click', '.aorp-image-upload', function(e){
        e.preventDefault();
        var button = $(this);
        if(aorp_frame){
            aorp_frame.open();
            return;
        }
        aorp_frame = wp.media({
            title: 'Bild auswählen',
            multiple: false,
            library: { type: 'image' }
        });
        aorp_frame.on('select', function(){
            var attachment = aorp_frame.state().get('selection').first().toJSON();
            button.prev('input').val(attachment.id);
            button.next('.aorp-image-preview').html('<img src="'+attachment.sizes.thumbnail.url+'" alt="" />');
        });
        aorp_frame.open();
    });
});
