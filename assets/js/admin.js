jQuery(function($){
    function showToast(text){
        var toast = $('<div class="aorp-toast" />').text(text);
        $('body').append(toast);
        setTimeout(function(){ toast.fadeOut(400,function(){ $(this).remove(); }); },3000);
    }
    function ajaxForm(form, action){
        var spinner = $('<span class="aorp-spinner is-active" />');
        form.find('button[type=submit]').after(spinner);
        $.post(aorp_admin.ajax_url, form.serialize()+"&action="+action, function(resp){
            spinner.remove();
            if(resp.success){
                if(resp.data.row){
                    if(form.hasClass('aorp-add-form')){
                        $('#aorp-items-table tbody').append(resp.data.row);
                        form[0].reset();
                    }else{
                        form.closest('tr').replaceWith(resp.data.row);
                    }
                }
                showToast('Gespeichert');
            }else if(resp.data && resp.data.message){
                alert(resp.data.message);
            }
        });
    }

    $(document).on('submit','.aorp-add-form',function(e){
        e.preventDefault();
        ajaxForm($(this), $(this).data('action'));
    });

    $(document).on('click','.aorp-edit',function(e){
        e.preventDefault();
        var row = $(this).closest('tr');
        if(row.next().hasClass('aorp-edit-row')) return;
        var data = row.data();
        var cols = $('<tr class="aorp-edit-row"><td colspan="8"></td></tr>');
        var form = $('<form class="aorp-inline-edit" />').append(
            '<input type="hidden" name="action" value="aorp_update_item" />'+
            '<input type="hidden" name="nonce" value="'+aorp_admin.nonce_edit+'" />'+
            '<input type="hidden" name="item_id" value="'+data.id+'" />'+
            '<p><input type="text" name="item_number" value="'+data.number+'" placeholder="Nummer" /></p>'+
            '<p><input type="text" name="item_title" value="'+data.title+'" required /></p>'+
            '<p><textarea name="item_description">'+data.description+'</textarea></p>'+
            '<p><input type="text" name="item_price" value="'+data.price+'" /></p>'+
            '<button type="submit" class="button button-primary">Speichern</button> '+
            '<button class="button aorp-cancel">Abbrechen</button>'
        );
        cols.find('td').append(form);
        row.after(cols); row.hide();
    });

    $(document).on('click','.aorp-cancel',function(e){
        e.preventDefault();
        var editRow = $(this).closest('tr.aorp-edit-row');
        editRow.prev('tr').show();
        editRow.remove();
    });

    $(document).on('submit','.aorp-inline-edit',function(e){
        e.preventDefault();
        ajaxForm($(this), 'aorp_update_item');
    });

    $(document).on('click','.aorp-delete',function(e){
        e.preventDefault();
        var id = $(this).data('id');
        var nonce = $(this).data('nonce');
        var row = $(this).closest('tr');
        $.post(aorp_admin.ajax_url,{action:'aorp_delete_item',item_id:id,nonce:nonce},function(resp){
            if(resp.success){
                row.hide();
                var undo = $('<div class="aorp-toast">Eintrag gelöscht. <a href="#">Rückgängig</a></div>');
                $('body').append(undo);
                undo.find('a').on('click',function(ev){
                    ev.preventDefault();
                    $.post(aorp_admin.ajax_url,{action:'aorp_undo_delete_item',item_id:id,nonce:resp.data.undo_nonce},function(r){
                        if(r.success&&r.data.row){
                            row.replaceWith(r.data.row);
                        }
                        undo.remove();
                    });
                });
                setTimeout(function(){ undo.fadeOut(400,function(){ $(this).remove(); }); },5000);
            }
        });
    });
});
