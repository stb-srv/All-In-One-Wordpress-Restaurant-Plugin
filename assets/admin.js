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
        var label = $(this).find('option:selected').text();
        if(ing){
            var form = $(this).closest('form');
            form.find('.aorp-selected').append('<span class="aorp-ing-chip" data-val="'+ing+'" data-label="'+label+'">'+label+' <a href="#" class="aorp-remove-ing">x</a></span> ');
            $(this).find('option:selected').remove();
            $(this).val('');
            updateInput(form);
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
        updateInput(form);
    });

    $('.aorp-ing-text').each(function(){
        var form = $(this).closest('form');
        var val = $(this).val();
        if(val){
            var map = {};
            form.find('.aorp-ing-select option').each(function(){
                map[$(this).val()] = $(this).text();
            });
            var arr = val.split(',');
            for(var i=0;i<arr.length;i++){
                var ing = $.trim(arr[i]);
                if(!ing) continue;
                var label = map[ing] || ing;
                form.find('.aorp-selected').append('<span class="aorp-ing-chip" data-val="'+ing+'" data-label="'+label+'">'+label+' <a href="#" class="aorp-remove-ing">x</a></span> ');
                form.find('.aorp-ing-select option[value="'+ing+'"]').remove();
            }
        }
    });

    var aorp_frame;
    var wpgmoLayout = {small:'Klein', large:'Groß'};
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

    $('.aorp-select-all').on('click', function(e){
        e.preventDefault();
        $($(this).data('target')).prop('checked', true);
    });
    $('.aorp-unselect-all').on('click', function(e){
        e.preventDefault();
        $($(this).data('target')).prop('checked', false);
    });

    // Live preview for font size settings on the settings page
    if($('#aorp_size_number').length){
        function aorpUpdatePreview(){
            $('.aorp-number').css('font-size', $('#aorp_size_number').val() || '');
            $('.aorp-title').css('font-size', $('#aorp_size_title').val() || '');
            $('.aorp-desc').css('font-size', $('#aorp_size_desc').val() || '');
            $('.aorp-price').css('font-size', $('#aorp_size_price').val() || '');
        }
        $('#aorp_size_number,#aorp_size_title,#aorp_size_desc,#aorp_size_price').on('change', aorpUpdatePreview);
        aorpUpdatePreview();
    }

    if($('#aorp-dark-tabs').length){
        $('#aorp-dark-tabs .nav-tab').on('click', function(e){
            e.preventDefault();
            $('#aorp-dark-tabs .nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            $('.aorp-dark-tab').hide();
            $($(this).attr('href')).show();
        });
    }

    if($('#aorp_icon_set').length){
        function updateIconFields(){
            var set = $('#aorp_icon_set').val();
            if(set==='custom') return;
            var map = {
                'default':['☀️','🌙'],
                'alt':['🌞','🌜'],
                'minimal':['🔆','🌑'],
                'eclipse':['🌞','🌚'],
                'sunset':['🌇','🌃'],
                'cloudy':['⛅','🌙'],
                'simple':['☼','☾'],
                'twilight':['🌄','🌌'],
                'starry':['⭐','🌜'],
                'morning':['🌅','🌠'],
                'bright':['🔆','🔅'],
                'flower':['🌻','🌑'],
                'smiley':['😀','😴']
            };
            if(map[set]){
                $('#aorp_icon_light').val(map[set][0]);
                $('#aorp_icon_dark').val(map[set][1]);
                $('#aorp_icon_preview').text(map[set][0]+' / '+map[set][1]);
            } else {
                $('#aorp_icon_preview').text('Eigenes Icon-Set');
            }
        }
        $('#aorp_icon_set').on('change',updateIconFields);
        updateIconFields();
    }

    if($('#wpgmo-layout-table').length){
        function updateLayout(){
            var layout = [];
            $('#wpgmo-layout-table tbody tr').each(function(){
                var type = $(this).find('.wpgmo-type').val();
                var size = $(this).find('.wpgmo-size').val();
                if(type){
                    layout.push({type:type, size:size});
                }
            });
            $('#wpgmo_layout').val(JSON.stringify(layout));
        }

        $('#wpgmo_add_row').on('click', function(e){
            e.preventDefault();
            var row = $('<tr>\
                <td><select class="wpgmo-type">'+$('#wpgmo_types_options').html()+'</select></td>\
                <td><select class="wpgmo-size"><option value="small">'+wpgmoLayout.small+'</option><option value="large">'+wpgmoLayout.large+'</option></select></td>\
                <td><button class="button wpgmo-remove-row">×</button></td></tr>');
            $('#wpgmo-layout-table tbody').append(row);
        });

        $(document).on('click','.wpgmo-remove-row',function(e){
            e.preventDefault();
            $(this).closest('tr').remove();
            updateLayout();
        });

        $(document).on('change','#wpgmo-layout-table select', updateLayout);

        try {
            var data = JSON.parse($('#wpgmo_layout').val());
            if(Array.isArray(data)){
                data.forEach(function(cell){
                    $('#wpgmo_add_row').trigger('click');
                    var row = $('#wpgmo-layout-table tbody tr:last');
                    row.find('.wpgmo-type').val(cell.type);
                    row.find('.wpgmo-size').val(cell.size);
                });
            }
        } catch(e){}
        updateLayout();
    }

});
