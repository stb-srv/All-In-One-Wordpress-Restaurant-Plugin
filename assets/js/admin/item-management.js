/**
 * Modern Admin Item Management
 * v2.7.0 - Complete CRUD for Foods and Drinks
 */
jQuery(function($){
    'use strict';
    
    var ingOptions = $('.aorp-ing-select:first').html() || '';
    
    // Initialize
    init();
    
    function init() {
        bindImageUpload($('.aorp-add-form'));
        initExistingIngredients();
    }
    
    function initExistingIngredients() {
        $('.aorp-add-form').each(function() {
            initIngredients($(this));
        });
    }
    
    function showToast(text, type){
        type = type || 'success';
        var toast = $('<div class="aorp-toast aorp-toast-'+type+'" />').text(text);
        $('body').append(toast);
        setTimeout(function(){
            toast.addClass('show');
        }, 10);
        setTimeout(function(){
            toast.removeClass('show');
            setTimeout(function(){ toast.remove(); }, 400);
        }, 3000);
    }
    
    function ajaxForm(form, action){
        var submitBtn = form.find('button[type=submit]');
        var spinner = $('<span class="spinner is-active" style="float:none;margin:0 0 0 8px;"></span>');
        submitBtn.prop('disabled', true).after(spinner);
        
        $.post(aorp_admin.ajax_url, form.serialize()+"&action="+action, function(resp){
            spinner.remove();
            submitBtn.prop('disabled', false);
            
            if(resp.success){
                if(resp.data.row){
                    if(form.hasClass('aorp-add-form')){
                        var table = $('#aorp-items-table tbody');
                        if(table.find('tr').length === 0 || table.find('tr td').text().indexOf('Keine') > -1){
                            table.html(resp.data.row);
                        } else {
                            table.append(resp.data.row);
                        }
                        form[0].reset();
                        form.find('.aorp-selected').empty();
                        form.find('.aorp-image-preview').empty();
                        form.find('.aorp-drink-sizes-list').html('<div class="aorp-size-row"><input type="text" class="aorp-size-vol" placeholder="0,3L" /><input type="text" class="aorp-size-price" placeholder="2.50" /><button type="button" class="button aorp-remove-size">✕</button></div>');
                    }else{
                        var editRow = form.closest('tr.aorp-edit-row');
                        editRow.prev('tr').replaceWith(resp.data.row);
                        editRow.remove();
                    }
                }
                showToast(resp.data.message || 'Erfolgreich gespeichert', 'success');
            }else{
                showToast(resp.data && resp.data.message ? resp.data.message : 'Fehler beim Speichern', 'error');
            }
        }).fail(function(){
            spinner.remove();
            submitBtn.prop('disabled', false);
            showToast('Netzwerkfehler', 'error');
        });
    }

    function bindImageUpload(form){
        form.find('.aorp-upload-image').off('click').on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            var frame = wp.media({
                title:'Bild auswählen',
                button:{text:'Auswählen'},
                multiple:false
            });
            frame.on('select',function(){
                var att = frame.state().get('selection').first().toJSON();
                btn.siblings('.aorp-image-id').val(att.id);
                var thumbUrl = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
                btn.siblings('.aorp-image-preview').html('<img src="'+thumbUrl+'" alt="" />');
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
            var selectInForm = form.find('.aorp-ing-select');
            if(selectInForm.length === 0 && ingOptions) {
                selectInForm = $('<select class="aorp-ing-select">'+ingOptions+'</select>');
            }
            selectInForm.find('option').each(function(){
                map[$(this).val()] = $(this).text();
            });
            val.split(',').forEach(function(i){
                var ing = $.trim(i);
                if(!ing) return;
                var label = map[ing] || ing;
                form.find('.aorp-selected').append('<span class="aorp-ing-chip" data-val="'+ing+'" data-label="'+label+'">'+label+' <a href="#" class="aorp-remove-ing">×</a></span> ');
                form.find('.aorp-ing-select option[value="'+ing+'"]').remove();
            });
        }
    }

    // Food Add Form
    $(document).on('submit','.aorp-add-form[data-type="food"]',function(e){
        e.preventDefault();
        if($(this).find('.aorp-ing-text').val().length > 200){
            showToast('Zu viele Inhaltsstoffe', 'error');
            return;
        }
        ajaxForm($(this), 'aorp_add_item');
    });

    // Drink Add Form
    $(document).on('submit','.aorp-add-form[data-type="drink"]',function(e){
        e.preventDefault();
        if($(this).find('.aorp-ing-text').val().length > 200){
            showToast('Zu viele Inhaltsstoffe', 'error');
            return;
        }
        var sizes = [];
        $(this).find('.aorp-size-row').each(function(){
            var vol = $(this).find('.aorp-size-vol').val();
            var price = $(this).find('.aorp-size-price').val();
            if(vol && price){
                sizes.push(vol + '=' + price);
            }
        });
        $(this).find('input[name="drink_sizes"]').val(sizes.join('\n'));
        ajaxForm($(this), 'aorp_add_drink_item');
    });

    // Edit Button - Works for BOTH foods and drinks
    $(document).on('click','.aorp-edit',function(e){
        e.preventDefault();
        var row = $(this).closest('tr');
        if(row.next().hasClass('aorp-edit-row')) return;
        
        var data = row.data();
        var isDrink = data.type === 'drink';
        var nonce = isDrink ? aorp_admin.nonce_edit_drink : aorp_admin.nonce_edit;
        var action = isDrink ? 'aorp_update_drink_item' : 'aorp_update_item';
        
        var cols = $('<tr class="aorp-edit-row"><td colspan="8"></td></tr>');
        var form = $('<form class="aorp-inline-edit aorp-modern-form" />');
        
        if(isDrink) {
            // Drink Edit Form
            form.append(
                '<input type="hidden" name="action" value="'+action+'" />'+
                '<input type="hidden" name="nonce" value="'+nonce+'" />'+
                '<input type="hidden" name="item_id" value="'+data.id+'" />'+
                '<div class="aorp-form-grid">'+
                    '<div class="aorp-form-group">'+
                        '<label>Titel *</label>'+
                        '<input type="text" name="item_title" value="'+escapeHtml(data.title)+'" required />'+
                    '</div>'+
                    '<div class="aorp-form-group">'+
                        '<label>Kategorie</label>'+
                        '<select name="item_category">'+getCategoryOptions(data.category, 'drink')+'</select>'+
                    '</div>'+
                '</div>'+
                '<div class="aorp-form-group">'+
                    '<label>Beschreibung</label>'+
                    '<textarea name="item_description" rows="3">'+escapeHtml(data.description)+'</textarea>'+
                '</div>'+
                '<div class="aorp-form-group">'+
                    '<label>Getränkegrößen (Volumen = Preis)</label>'+
                    '<div class="aorp-drink-sizes-edit">'+buildDrinkSizesEdit(data.sizes)+'</div>'+
                    '<button type="button" class="button aorp-add-drink-size">+ Größe hinzufügen</button>'+
                '</div>'+
                '<div class="aorp-form-group">'+
                    '<label>Inhaltsstoffe</label>'+
                    '<select class="aorp-ing-select">'+ingOptions+'</select>'+
                    '<div class="aorp-selected"></div>'+
                    '<input type="hidden" name="item_ingredients" class="aorp-ing-text" value="'+(data.ingredients||'')+'" />'+
                '</div>'+
                '<div class="aorp-form-actions">'+
                    '<button type="submit" class="button button-primary">💾 Speichern</button> '+
                    '<button type="button" class="button aorp-cancel">Abbrechen</button>'+
                '</div>'
            );
        } else {
            // Food Edit Form
            form.append(
                '<input type="hidden" name="action" value="'+action+'" />'+
                '<input type="hidden" name="nonce" value="'+nonce+'" />'+
                '<input type="hidden" name="item_id" value="'+data.id+'" />'+
                '<div class="aorp-form-grid">'+
                    '<div class="aorp-form-group">'+
                        '<label>Nummer</label>'+
                        '<input type="text" name="item_number" value="'+escapeHtml(data.number)+'" placeholder="z.B. 42" />'+
                    '</div>'+
                    '<div class="aorp-form-group">'+
                        '<label>Titel *</label>'+
                        '<input type="text" name="item_title" value="'+escapeHtml(data.title)+'" required />'+
                    '</div>'+
                '</div>'+
                '<div class="aorp-form-group">'+
                    '<label>Beschreibung</label>'+
                    '<textarea name="item_description" rows="3">'+escapeHtml(data.description)+'</textarea>'+
                '</div>'+
                '<div class="aorp-form-grid">'+
                    '<div class="aorp-form-group">'+
                        '<label>Preis</label>'+
                        '<input type="text" name="item_price" value="'+escapeHtml(data.price)+'" placeholder="12.50" />'+
                    '</div>'+
                    '<div class="aorp-form-group">'+
                        '<label>Kategorie</label>'+
                        '<select name="item_category">'+getCategoryOptions(data.category, 'food')+'</select>'+
                    '</div>'+
                '</div>'+
                '<div class="aorp-form-group">'+
                    '<label>Inhaltsstoffe</label>'+
                    '<select class="aorp-ing-select">'+ingOptions+'</select>'+
                    '<div class="aorp-selected"></div>'+
                    '<input type="hidden" name="item_ingredients" class="aorp-ing-text" value="'+(data.ingredients||'')+'" />'+
                '</div>'+
                '<div class="aorp-form-group">'+
                    '<label>Bild</label>'+
                    '<button type="button" class="button aorp-upload-image">📷 Bild auswählen</button> '+
                    '<input type="hidden" name="item_image_id" class="aorp-image-id" value="'+(data.imageid||'')+'" />'+
                    '<div class="aorp-image-preview">'+(data.imageurl?'<img src="'+data.imageurl+'" alt="" />':'')+'</div>'+
                '</div>'+
                '<div class="aorp-form-actions">'+
                    '<button type="submit" class="button button-primary">💾 Speichern</button> '+
                    '<button type="button" class="button aorp-cancel">Abbrechen</button>'+
                '</div>'
            );
        }
        
        cols.find('td').append(form);
        row.after(cols);
        row.hide();
        initIngredients(form);
        bindImageUpload(form);
    });

    // Inline Edit Submit
    $(document).on('submit','.aorp-inline-edit',function(e){
        e.preventDefault();
        if($(this).find('.aorp-ing-text').val().length > 200){
            showToast('Zu viele Inhaltsstoffe', 'error');
            return;
        }
        
        // For drinks, collect sizes
        if($(this).find('.aorp-drink-sizes-edit').length > 0) {
            var sizes = [];
            $(this).find('.aorp-size-row').each(function(){
                var vol = $(this).find('.aorp-size-vol').val();
                var price = $(this).find('.aorp-size-price').val();
                if(vol && price){
                    sizes.push(vol + '=' + price);
                }
            });
            $('<input>').attr({
                type: 'hidden',
                name: 'drink_sizes',
                value: sizes.join('\n')
            }).appendTo($(this));
        }
        
        var action = $(this).find('input[name="action"]').val();
        ajaxForm($(this), action);
    });

    // Delete Button - Works for BOTH foods and drinks
    $(document).on('click','.aorp-delete',function(e){
        e.preventDefault();
        if(!confirm('Wirklich löschen?')) return;
        
        var btn = $(this);
        var id = btn.data('id');
        var nonce = btn.data('nonce');
        var row = btn.closest('tr');
        var isDrink = row.data('type') === 'drink';
        var action = isDrink ? 'aorp_delete_drink_item' : 'aorp_delete_item';
        
        btn.prop('disabled', true);
        
        $.post(aorp_admin.ajax_url,{action:action,item_id:id,nonce:nonce},function(resp){
            if(resp.success){
                row.fadeOut(400, function(){
                    $(this).remove();
                    // Check if table is now empty
                    var tbody = $('#aorp-items-table tbody');
                    if(tbody.find('tr:visible').length === 0){
                        tbody.html('<tr><td colspan="8" style="text-align:center;padding:40px;">Keine Einträge gefunden.</td></tr>');
                    }
                });
                showToast('Eintrag gelöscht', 'success');
            } else {
                btn.prop('disabled', false);
                showToast('Fehler beim Löschen', 'error');
            }
        }).fail(function(){
            btn.prop('disabled', false);
            showToast('Netzwerkfehler', 'error');
        });
    });

    // Ingredient Management
    $(document).on('change','.aorp-ing-select',function(){
        var ing = $(this).val();
        var label = $(this).find('option:selected').text();
        if(ing){
            var form = $(this).closest('form');
            form.find('.aorp-selected').append('<span class="aorp-ing-chip" data-val="'+ing+'" data-label="'+label+'">'+label+' <a href="#" class="aorp-remove-ing">×</a></span> ');
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

    // Cancel Edit
    $(document).on('click','.aorp-cancel',function(e){
        e.preventDefault();
        var editRow = $(this).closest('tr.aorp-edit-row');
        editRow.prev('tr').show();
        editRow.remove();
    });

    // Drink Size Management
    $(document).on('click','.aorp-add-drink-size',function(e){
        e.preventDefault();
        var container = $(this).prev('.aorp-drink-sizes-list, .aorp-drink-sizes-edit');
        container.append('<div class="aorp-size-row"><input type="text" class="aorp-size-vol" placeholder="0,5L" /><input type="text" class="aorp-size-price" placeholder="3.50" /><button type="button" class="button aorp-remove-size">×</button></div>');
    });

    $(document).on('click','.aorp-remove-size',function(e){
        e.preventDefault();
        $(this).closest('.aorp-size-row').remove();
    });

    // Helper Functions
    function escapeHtml(text) {
        if(!text) return '';
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function getCategoryOptions(selectedId, type) {
        var options = '<option value="">-- Wählen --</option>';
        var categoryData = type === 'drink' ? window.aorp_drink_categories : window.aorp_food_categories;
        
        if(categoryData) {
            $.each(categoryData, function(id, name){
                var selected = (id == selectedId) ? ' selected' : '';
                options += '<option value="'+id+'"'+selected+'>'+escapeHtml(name)+'</option>';
            });
        }
        
        return options;
    }

    function buildDrinkSizesEdit(sizesStr) {
        if(!sizesStr) {
            return '<div class="aorp-size-row"><input type="text" class="aorp-size-vol" placeholder="0,3L" /><input type="text" class="aorp-size-price" placeholder="2.50" /><button type="button" class="button aorp-remove-size">×</button></div>';
        }
        
        var html = '';
        var lines = sizesStr.split('\n');
        $.each(lines, function(i, line){
            var parts = line.split('=');
            if(parts.length === 2) {
                html += '<div class="aorp-size-row">'+
                    '<input type="text" class="aorp-size-vol" value="'+escapeHtml($.trim(parts[0]))+'" placeholder="0,3L" />'+
                    '<input type="text" class="aorp-size-price" value="'+escapeHtml($.trim(parts[1]))+'" placeholder="2.50" />'+
                    '<button type="button" class="button aorp-remove-size">×</button>'+
                    '</div>';
            }
        });
        
        return html || '<div class="aorp-size-row"><input type="text" class="aorp-size-vol" placeholder="0,3L" /><input type="text" class="aorp-size-price" placeholder="2.50" /><button type="button" class="button aorp-remove-size">×</button></div>';
    }
});
