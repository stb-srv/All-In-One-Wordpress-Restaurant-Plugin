jQuery(document).ready(function($){
    $('#aorp-item-filter').on('keyup', function(){
        var val = $(this).val().toLowerCase();
        $('#aorp-items-table tbody tr').each(function(){
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
});
