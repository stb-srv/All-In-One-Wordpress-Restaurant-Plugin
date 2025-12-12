jQuery(function($){
    var ingOptions = $('.aorp-ing-select:first').html() || '';
    bindImageUpload($('.aorp-add-form'));
    
    console.log('AORP Admin JS loaded', aorp_admin);

    function showToast(text, type = 'success') {
        var bgClass = type === 'success' ? 'aorp-toast-success' : 'aorp-toast-error';
        var toast = $('<div class="aorp-toast ' + bgClass + '" />').text(text);
        $('body').append(toast);
        setTimeout(function(){ 
            toast.fadeOut(400, function(){ $(this).remove(); });
        }, 3000);
    }

    function ajaxForm(form, action) {
        var spinner = $('<span class="aorp-spinner is-active" />');
        form.find('button[type=submit]').prop('disabled', true).after(spinner);
        
        console.log('Submitting form', action, form.serialize());
        
        $.post(aorp_admin.ajax_url, form.serialize() + "&action=" + action, function(resp){
            console.log('Response:', resp);
            spinner.remove();
            form.find('button[type=submit]').prop('disabled', false);
            
            if (resp.success) {
                if (resp.data.row) {
                    if (form.hasClass('aorp-add-form')) {
                        $('#aorp-items-table tbody').prepend(resp.data.row);
                        form[0].reset();
                        form.find('.aorp-selected').empty();
                        form.find('.aorp-image-preview').empty();
                    } else {
                        var oldRow = form.closest('tr.aorp-edit-row').prev('tr');
                        $(resp.data.row).insertAfter(form.closest('tr.aorp-edit-row'));
                        oldRow.remove();
                        form.closest('tr.aorp-edit-row').remove();
                    }
                }
                showToast(resp.data.message || '✅ Erfolgreich gespeichert!', 'success');
            } else {
                showToast(resp.data && resp.data.message ? resp.data.message : '❌ Fehler beim Speichern', 'error');
            }
        }).fail(function(xhr, status, error) {
            console.error('AJAX Error:', status, error, xhr.responseText);
            spinner.remove();
            form.find('button[type=submit]').prop('disabled', false);
            showToast('❌ Netzwerkfehler', 'error');
        });
    }

    function bindImageUpload(form) {
        form.find('.aorp-upload-image').off('click').on('click', function(e) {
            e.preventDefault();
            var btn = $(this);
            var frame = wp.media({
                title: 'Bild auswählen',
                button: { text: 'Auswählen' },
                multiple: false
            });
            frame.on('select', function() {
                var att = frame.state().get('selection').first().toJSON();
                btn.siblings('.aorp-image-id').val(att.id);
                var imgUrl = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
                btn.siblings('.aorp-image-preview').html('<img src="' + imgUrl + '" />');
            });
            frame.open();
        });
    }

    function updateIngInput(form) {
        var list = [];
        form.find('.aorp-ing-chip').each(function() {
            list.push($(this).data('val'));
        });
        form.find('.aorp-ing-text').val(list.join(', '));
    }

    function initIngredients(form) {
        var val = form.find('.aorp-ing-text').val();
        if (val) {
            var map = {};
            form.find('.aorp-ing-select option').each(function() {
                map[$(this).val()] = $(this).text();
            });
            val.split(',').forEach(function(i) {
                var ing = $.trim(i);
                if (!ing) return;
                var label = map[ing] || ing;
                form.find('.aorp-selected').append('<span class="aorp-ing-chip" data-val="' + ing + '" data-label="' + label + '">' + label + ' <a href="#" class="aorp-remove-ing">×</a></span> ');
                form.find('.aorp-ing-select option[value="' + ing + '"]').remove();
            });
        }
    }

    // Add item (food or drink)
    $(document).on('submit', '.aorp-add-form', function(e) {
        e.preventDefault();
        if ($(this).find('.aorp-ing-text').val().length > 200) {
            showToast('❌ Zu viele Inhaltsstoffe', 'error');
            return;
        }
        ajaxForm($(this), $(this).data('action'));
    });

    // Edit food item
    $(document).on('click', '.aorp-edit', function(e) {
        e.preventDefault();
        if ($(this).hasClass('aorp-edit-drink')) return; // Let drink handler do it
        
        var row = $(this).closest('tr');
        if (row.next().hasClass('aorp-edit-row')) return;
        
        var data = row.data();
        console.log('Edit food data:', data);
        
        // Build category select
        var catSelect = '<select name="item_category">' + aorp_admin.food_categories + '</select>';
        catSelect = catSelect.replace('value="' + data.category + '"', 'value="' + data.category + '" selected');
        
        var editRow = $('<tr class="aorp-edit-row"><td colspan="8"></td></tr>');
        var form = $('<form class="aorp-inline-edit" />').html(
            '<input type="hidden" name="nonce" value="' + aorp_admin.nonce_edit + '" />' +
            '<input type="hidden" name="item_id" value="' + data.id + '" />' +
            '<p><label>Nummer</label><input type="text" name="item_number" value="' + (data.number || '') + '" /></p>' +
            '<p><label>Name</label><input type="text" name="item_title" value="' + (data.title || '') + '" required /></p>' +
            '<p><label>Beschreibung</label><textarea name="item_description">' + (data.description || '') + '</textarea></p>' +
            '<p><label>Preis</label><input type="text" name="item_price" value="' + (data.price || '') + '" /></p>' +
            '<p><label>Kategorie</label>' + catSelect + '</p>' +
            '<p><label>Inhaltsstoffe</label><select class="aorp-ing-select">' + ingOptions + '</select></p>' +
            '<div class="aorp-selected"></div>' +
            '<input type="hidden" name="item_ingredients" class="aorp-ing-text" value="' + (data.ingredients || '') + '" />' +
            '<p><button class="button aorp-upload-image">Bild auswählen</button> ' +
            '<input type="hidden" name="item_image_id" class="aorp-image-id" value="' + (data.imageid || '') + '" /> ' +
            '<span class="aorp-image-preview">' + (data.imageurl ? '<img src="' + data.imageurl + '" />' : '') + '</span></p>' +
            '<p><button type="submit" class="button button-primary">💾 Speichern</button> ' +
            '<button type="button" class="button aorp-cancel">Abbrechen</button></p>'
        );
        
        editRow.find('td').append(form);
        row.after(editRow);
        row.hide();
        initIngredients(form);
        bindImageUpload(form);
    });

    // Edit drink item
    $(document).on('click', '.aorp-edit-drink', function(e) {
        e.preventDefault();
        var row = $(this).closest('tr');
        if (row.next().hasClass('aorp-edit-row')) return;
        
        var data = row.data();
        console.log('Edit drink data:', data);
        
        // Build category select
        var catSelect = '<select name="item_category">' + aorp_admin.drink_categories + '</select>';
        catSelect = catSelect.replace('value="' + data.category + '"', 'value="' + data.category + '" selected');
        
        var editRow = $('<tr class="aorp-edit-row"><td colspan="8"></td></tr>');
        var form = $('<form class="aorp-inline-edit" />').html(
            '<input type="hidden" name="nonce" value="' + aorp_admin.nonce_edit_drink + '" />' +
            '<input type="hidden" name="item_id" value="' + data.id + '" />' +
            '<p><label>Name</label><input type="text" name="item_title" value="' + (data.title || '') + '" required /></p>' +
            '<p><label>Beschreibung</label><textarea name="item_description">' + (data.description || '') + '</textarea></p>' +
            '<p><label>Größen/Preise <small>(Format: 0.3L = 2.50)</small></label><textarea name="item_sizes" placeholder="0.3L = 2.50\n0.5L = 3.50">' + (data.sizes || '') + '</textarea></p>' +
            '<p><label>Kategorie</label>' + catSelect + '</p>' +
            '<p><label>Inhaltsstoffe</label><select class="aorp-ing-select">' + ingOptions + '</select></p>' +
            '<div class="aorp-selected"></div>' +
            '<input type="hidden" name="item_ingredients" class="aorp-ing-text" value="' + (data.ingredients || '') + '" />' +
            '<p><button class="button aorp-upload-image">Bild auswählen</button> ' +
            '<input type="hidden" name="item_image_id" class="aorp-image-id" value="' + (data.imageid || '') + '" /> ' +
            '<span class="aorp-image-preview">' + (data.imageurl ? '<img src="' + data.imageurl + '" />' : '') + '</span></p>' +
            '<p><button type="submit" class="button button-primary">💾 Speichern</button> ' +
            '<button type="button" class="button aorp-cancel">Abbrechen</button></p>'
        );
        
        editRow.find('td').append(form);
        row.after(editRow);
        row.hide();
        initIngredients(form);
        bindImageUpload(form);
    });

    // Ingredient selection
    $(document).on('change', '.aorp-ing-select', function() {
        var ing = $(this).val();
        var label = $(this).find('option:selected').text();
        if (ing) {
            var form = $(this).closest('form');
            form.find('.aorp-selected').append('<span class="aorp-ing-chip" data-val="' + ing + '" data-label="' + label + '">' + label + ' <a href="#" class="aorp-remove-ing">×</a></span> ');
            $(this).find('option:selected').remove();
            $(this).val('');
            updateIngInput(form);
        }
    });

    // Remove ingredient
    $(document).on('click', '.aorp-remove-ing', function(e) {
        e.preventDefault();
        var chip = $(this).closest('.aorp-ing-chip');
        var ing = chip.data('val');
        var label = chip.data('label');
        var form = chip.closest('form');
        form.find('.aorp-ing-select').append('<option value="' + ing + '">' + label + '</option>');
        chip.remove();
        updateIngInput(form);
    });

    // Cancel editing
    $(document).on('click', '.aorp-cancel', function(e) {
        e.preventDefault();
        var editRow = $(this).closest('tr.aorp-edit-row');
        editRow.prev('tr').show();
        editRow.remove();
    });

    // Submit inline edit
    $(document).on('submit', '.aorp-inline-edit', function(e) {
        e.preventDefault();
        if ($(this).find('.aorp-ing-text').val().length > 200) {
            showToast('❌ Zu viele Inhaltsstoffe', 'error');
            return;
        }
        var action = $(this).find('input[name="nonce"]').val() === aorp_admin.nonce_edit_drink 
            ? 'aorp_update_drink_item' 
            : 'aorp_update_item';
        console.log('Submitting inline edit with action:', action);
        ajaxForm($(this), action);
    });

    // Delete food item
    $(document).on('click', '.aorp-delete', function(e) {
        e.preventDefault();
        if ($(this).hasClass('aorp-delete-drink')) return; // Let drink handler do it
        
        if (!confirm('Wirklich löschen?')) return;
        
        var id = $(this).data('id');
        var nonce = $(this).data('nonce');
        var row = $(this).closest('tr');
        
        console.log('Deleting food', id);
        
        $.post(aorp_admin.ajax_url, {
            action: 'aorp_delete_item',
            item_id: id,
            nonce: nonce
        }, function(resp) {
            console.log('Delete response:', resp);
            if (resp.success) {
                row.fadeOut(300, function() { $(this).remove(); });
                showToast('✓ Eintrag gelöscht', 'success');
            } else {
                showToast(resp.data && resp.data.message ? resp.data.message : '❌ Fehler beim Löschen', 'error');
            }
        }).fail(function(xhr, status, error) {
            console.error('Delete error:', status, error);
            showToast('❌ Fehler beim Löschen', 'error');
        });
    });

    // Delete drink item
    $(document).on('click', '.aorp-delete-drink', function(e) {
        e.preventDefault();
        if (!confirm('Wirklich löschen?')) return;
        
        var id = $(this).data('id');
        var nonce = $(this).data('nonce');
        var row = $(this).closest('tr');
        
        console.log('Deleting drink', id, nonce);
        
        $.post(aorp_admin.ajax_url, {
            action: 'aorp_delete_drink_item',
            item_id: id,
            nonce: nonce
        }, function(resp) {
            console.log('Delete drink response:', resp);
            if (resp.success) {
                row.fadeOut(300, function() { $(this).remove(); });
                showToast('✓ Getränk gelöscht', 'success');
            } else {
                showToast(resp.data && resp.data.message ? resp.data.message : '❌ Fehler beim Löschen', 'error');
            }
        }).fail(function(xhr, status, error) {
            console.error('Delete drink error:', status, error, xhr.responseText);
            showToast('❌ Fehler beim Löschen', 'error');
        });
    });

    // Live search filter
    $('#aorp-item-filter').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#aorp-items-table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Select/Unselect all checkboxes
    $('.aorp-select-all').on('click', function() {
        $($(this).data('target')).prop('checked', true);
    });

    $('.aorp-unselect-all').on('click', function() {
        $($(this).data('target')).prop('checked', false);
    });
});
