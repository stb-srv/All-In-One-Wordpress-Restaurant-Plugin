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

    $('.aorp-ing-select').on('change', function(){
        var ing = $(this).val();
        if(ing){
            var textarea = $(this).closest('form').find('.aorp-ing-text');
            var current = textarea.val();
            if(current){
                current += ', ';
            }
            textarea.val(current + ing);
            $(this).val('');
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
