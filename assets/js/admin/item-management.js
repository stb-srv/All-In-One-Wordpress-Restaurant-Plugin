jQuery(function($){
    var ingOptions = $('.aorp-ing-select:first').html() || '';
    bindImageUpload($('.aorp-add-form'));
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
                        form.find('.aorp-selected').empty();
                        form.find('.aorp-image-preview').empty();
                    }else{
                        form.closest('tr').replaceWith(resp.data.row);
                        form.prev('tr').remove();
                    }
                }
                showToast('Gespeichert');
            }else if(resp.data && resp.data.message){
                alert(resp.data.message);
            }
        });
    }

    function bindImageUpload(form){
        form.find('.aorp-upload-image').off('click').on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            var frame = wp.media({title:'Bild auswählen',button:{text:'Auswählen'},multiple:false});
            frame.on('select',function(){
                var att = frame.state().get('selection').first().toJSON();
                btn.siblings('.aorp-image-id').val(att.id);
                btn.siblings('.aorp-image-preview').html('<img src="'+att.sizes.thumbnail.url+'" />');
            });
            frame.open();
        });
    }

    function updateIngInput(form){
        var list = [];
        form.find('.aorp-ing-chip').each(function(){
            list.push($(this).data('val'));
        });
        form.find('.aorp-ing-text').val(list.join(', '));
    }

    function initIngredients(form){
        var val = form.find('.aorp-ing-text').val();
        if(val){
            var map = {};
            form.find('.aorp-ing-select option').each(function(){
                map[$(this).val()] = $(this).text();
            });
            val.split(',').forEach(function(i){
                var ing = $.trim(i);
                if(!ing) return;
                var label = map[ing] || ing;
                form.find('.aorp-selected').append('<span class="aorp-ing-chip" data-val="'+ing+'" data-label="'+label+'">'+label+' <a href="#" class="aorp-remove-ing">x</a></span> ');
                form.find('.aorp-ing-select option[value="'+ing+'"]').remove();
            });
        }
    }

    $(document).on('submit','.aorp-add-form',function(e){
        e.preventDefault();
        if($(this).find('.aorp-ing-text').val().length > 200){
            alert('Zu viele Inhaltsstoffe');
            return;
        }
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
            '<p><select class="aorp-ing-select">'+ingOptions+'</select></p>'+
            '<div class="aorp-selected"></div>'+
            '<input type="hidden" name="item_ingredients" class="aorp-ing-text" value="'+(data.ingredients||'')+'" />'+
            '<p><button class="button aorp-upload-image">Bild auswählen</button> <input type="hidden" name="item_image_id" class="aorp-image-id" value="'+(data.imageid||'')+'" /> <span class="aorp-image-preview">'+(data.imageurl?'<img src="'+data.imageurl+'" />':'')+'</span></p>'+
            '<button type="submit" class="button button-primary">Speichern</button> '+
            '<button class="button aorp-cancel">Abbrechen</button>'
        );
        cols.find('td').append(form);
        row.after(cols); row.hide();
        initIngredients(form);
        bindImageUpload(form);
    });

    $(document).on('change','.aorp-ing-select',function(){
        var ing = $(this).val();
        var label = $(this).find('option:selected').text();
        if(ing){
            var form = $(this).closest('form');
            form.find('.aorp-selected').append('<span class="aorp-ing-chip" data-val="'+ing+'" data-label="'+label+'">'+label+' <a href="#" class="aorp-remove-ing">x</a></span> ');
            $(this).find('option:selected').remove();
            $(this).val('');
            updateIngInput(form);
        }
    });

    $(document).on('click','.aorp-remove-ing',function(e){
        e.preventDefault();
        var chip = $(this).closest('.aorp-ing-chip');
        var ing = chip.data('val');
        var label = chip.data('label');
        var form = chip.closest('form');
        form.find('.aorp-ing-select').append('<option value="'+ing+'">'+label+'</option>');
        chip.remove();
        updateIngInput(form);
    });

    $(document).on('click','.aorp-cancel',function(e){
        e.preventDefault();
        var editRow = $(this).closest('tr.aorp-edit-row');
        editRow.prev('tr').show();
        editRow.remove();
    });

    $(document).on('submit','.aorp-inline-edit',function(e){
        e.preventDefault();
        if($(this).find('.aorp-ing-text').val().length > 200){
            alert('Zu viele Inhaltsstoffe');
            return;
        }
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
