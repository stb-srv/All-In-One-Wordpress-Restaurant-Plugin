var WPGMOBuilder = (function($){
    var layout = [];
    var currentSlug = '';
    function render(){
        var container = $('#wpgmo-grid-editor');
        container.empty();
        layout.forEach(function(row,rowIndex){
            var rowWrap = $('<div class="wpgmo-row-editor"></div>');
            row.forEach(function(cell,cellIndex){
                var cellWrap = $('<div class="wpgmo-cell-editor"></div>');
                cellWrap.append('<input type="text" class="cell-id" value="'+cell.id+'" /> ');
                var sel = $('<select class="cell-size"><option value="small">small</option><option value="large">large</option></select>');
                sel.val(cell.size);
                cellWrap.append(sel);
                cellWrap.append(' <button class="remove-cell">×</button>');
                cellWrap.data('row',rowIndex).data('cell',cellIndex);
                rowWrap.append(cellWrap);
            });
            rowWrap.append(' <button class="add-cell" data-row="'+rowIndex+'">+ cell</button> <button class="remove-row" data-row="'+rowIndex+'">× row</button>');
            container.append(rowWrap);
        });
        $('#wpgmo_layout').val(JSON.stringify(layout));
    }
    function addRow(data){
        layout.push(data||[]);
        render();
    }
    $(document).on('click','#wpgmo-add-row',function(e){
        e.preventDefault();
        addRow([]);
    });
    $(document).on('click','.add-cell',function(e){
        e.preventDefault();
        var r=parseInt($(this).data('row'),10);
        layout[r].push({id:'cell'+Date.now(),size:'small'});
        render();
    });
    $(document).on('click','.remove-cell',function(e){
        e.preventDefault();
        var wrap=$(this).closest('.wpgmo-cell-editor');
        var r=wrap.data('row'), c=wrap.data('cell');
        layout[r].splice(c,1);
        render();
    });
    $(document).on('click','.remove-row',function(e){
        e.preventDefault();
        var r=parseInt($(this).data('row'),10);
        layout.splice(r,1);
        render();
    });
    $(document).on('change','.cell-id,.cell-size',function(){
        var wrap=$(this).closest('.wpgmo-cell-editor');
        var r=wrap.data('row'), c=wrap.data('cell');
        layout[r][c].id=wrap.find('.cell-id').val();
        layout[r][c].size=wrap.find('.cell-size').val();
        $('#wpgmo_layout').val(JSON.stringify(layout));
    });
    $(document).on('submit','#wpgmo-template-form',function(e){
        e.preventDefault();
        var data={
            action:'wpgmo_save_template',
            nonce:WPGMO.nonce,
            slug:$('#wpgmo_template_slug').val(),
            label:$('#wpgmo_template_label').val(),
            layout:$('#wpgmo_layout').val()
        };
        $.post(WPGMO.ajaxurl,data,function(res){
            if(res.success){
                alert(WPGMO.saved);
            }else{
                alert(WPGMO.error);
            }
        });
    });
    function loadTemplate(slug){
        if(!slug){
            layout=[];
            $('#wpgmo_template_label').val('');
            render();
            return;
        }
        $.post(WPGMO.ajaxurl,{action:'wpgmo_get_template',nonce:WPGMO.nonce,slug:slug},function(res){
            if(res.success){
                layout = Array.isArray(res.data.layout)?res.data.layout:[];
                $('#wpgmo_template_label').val(res.data.label);
            }
            render();
        });
    }
    function initTemplateEditor(slug){
        currentSlug = slug||'';
        $('#wpgmo_template_slug').val(currentSlug);
        if(currentSlug){$('#wpgmo_template_slug').prop('readonly',true);} else {$('#wpgmo_template_slug').prop('readonly',false);}    
        loadTemplate(currentSlug);
    }
    $(document).on('click','.wpgmo-duplicate',function(e){
        e.preventDefault();
        var slug=$(this).data('slug');
        $.post(WPGMO.ajaxurl,{action:'wpgmo_duplicate_template',nonce:$('#wpgmo_templates_nonce').val(),slug:slug},function(){location.reload();});
    });
    $(document).on('click','.wpgmo-delete',function(e){
        e.preventDefault();
        if(!confirm('Delete?')) return;
        var slug=$(this).data('slug');
        $.post(WPGMO.ajaxurl,{action:'wpgmo_delete_template',nonce:$('#wpgmo_templates_nonce').val(),slug:slug},function(){location.reload();});
    });
    $(document).on('click','.wpgmo-set-default',function(e){
        e.preventDefault();
        var slug=$(this).data('slug');
        $.post(WPGMO.ajaxurl,{action:'wpgmo_set_default_template',nonce:$('#wpgmo_templates_nonce').val(),slug:slug},function(){location.reload();});
    });
    return{initTemplateEditor:initTemplateEditor};
})(jQuery);
