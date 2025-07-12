jQuery(function($){
    var templates = WPGMO_GB.templates || {};
    var networkSlugs = WPGMO_GB.networkSlugs || [];
    var container = $('#wpgmo-template-manager');
    if(!container.length) return;
    var editing = false;
    var state = null;
    var nextId = 1;

    function isReadOnly(slug){
        return !WPGMO_GB.isNetwork && networkSlugs.indexOf(slug) !== -1;
    }

    function getNextId(layout){
        var id = 1;
        $.each(layout,function(_,row){
            $.each(row,function(_,cell){
                var n = parseInt(String(cell.id).replace('cell',''),10);
                if(n >= id){ id = n + 1; }
            });
        });
        return id;
    }

    function render(){
        container.empty();
        if(state){
            renderEditor();
        }else{
            renderList();
        }
    }

    function renderList(){
        var addBtn = $('<button class="button button-primary"/>').text(WPGMO_GB.new).on('click',function(){
            state = {slug:'',label:'',layout:[]};
            nextId = 1;
            editing = false;
            render();
        });
        container.append(addBtn);
        var table = $('<table class="widefat striped"><thead><tr><th>Slug</th><th>'+WPGMO_GB.label+'</th><th>'+WPGMO_GB.actions+'</th></tr></thead><tbody></tbody></table>');
        $.each(templates,function(slug,tpl){
            var tr = $('<tr/>');
            var slugCell = $('<td/>').text(slug);
            if(slug === WPGMO_GB.default){ slugCell.append(' *'); }
            tr.append(slugCell);
            tr.append($('<td/>').text(tpl.label));
            var actions = $('<td/>');
            var editB = $('<button class="button"/>').text(WPGMO_GB.edit).on('click',function(){
                if(isReadOnly(slug)) return;
                state = {slug:slug,label:tpl.label,layout:tpl.layout||[]};
                nextId = getNextId(state.layout);
                editing = true;
                render();
            });
            var dupB = $('<button class="button"/>').text(WPGMO_GB.duplicate).on('click',function(){
                var ns = prompt('Slug');
                if(ns){ duplicateTemplate(slug,ns); }
            });
            var delB = $('<button class="button"/>').text(WPGMO_GB.del).on('click',function(){ deleteTemplate(slug); });
            var defB = $('<button class="button"/>').text(WPGMO_GB.setDefault).on('click',function(){ setDefault(slug); });
            if(isReadOnly(slug)){
                editB.prop('disabled',true);
                delB.prop('disabled',true);
            }
            actions.append(editB).append(' ').append(dupB).append(' ').append(delB).append(' ').append(defB);
            tr.append(actions);
            table.find('tbody').append(tr);
        });
        container.append(table);
    }

    function renderEditor(){
        var wrap = $('<div class="wpgmo-editor"/>');
        wrap.append('<p><label>'+WPGMO_GB.slug+'</label><br><input type="text" id="wpgmo-slug" '+(editing?'readonly':'')+' value="'+state.slug+'"></p>');
        wrap.append('<p><label>'+WPGMO_GB.label+'</label><br><input type="text" id="wpgmo-label" value="'+state.label+'"></p>');
        var layoutDiv = $('<div id="wpgmo-layout"/>');
        $.each(state.layout,function(i,row){
            var rdiv = $('<div class="wpgmo-row"/>');
            $.each(row,function(j,cell){
                var cdiv = $('<div class="wpgmo-cell"/>');
                cdiv.append('<span>'+cell.id+'</span>');
                var sel = $('<select><option value="large">Groß</option><option value="medium">Mittel</option><option value="small">Klein</option></select>');
                sel.val(cell.size);
                sel.on('change',function(){ cell.size = $(this).val(); });
                cdiv.append(sel);
                var del = $('<button type="button" class="remove-cell">&times;</button>').on('click',function(){ removeColumn(i,j); });
                cdiv.append(del);
                rdiv.append(cdiv);
            });
            var addC = $('<button class="button">+ Spalte</button>').on('click',function(){ addColumn(i); });
            var delR = $('<button class="button remove-row"/>').text(WPGMO_GB.removeRow).on('click',function(){ removeRow(i); });
            rdiv.append(addC).append(delR);
            layoutDiv.append(rdiv);
        });
        layoutDiv.append($('<button class="button">+ Zeile</button>').on('click',addRow));
        wrap.append(layoutDiv);
        var save = $('<button class="button button-primary"/>').text(WPGMO_GB.save).on('click',function(e){ e.preventDefault(); saveTemplate(); });
        var cancel = $('<button class="button"/>').text(WPGMO_GB.cancel).on('click',function(e){ e.preventDefault(); state=null; render(); });
        wrap.append(save).append(' ').append(cancel);
        container.append(wrap);
    }

    function addRow(){
        state.layout.push([{id:'cell'+(nextId++),size:'large'}]);
        render();
    }

    function addColumn(index){
        state.layout[index].push({id:'cell'+(nextId++),size:'medium'});
        render();
    }

    function removeColumn(rowIndex,colIndex){
        state.layout[rowIndex].splice(colIndex,1);
        if(state.layout[rowIndex].length===0){
            state.layout.splice(rowIndex,1);
        }
        render();
    }

    function removeRow(rowIndex){
        state.layout.splice(rowIndex,1);
        render();
    }

    function saveTemplate(){
        state.slug = $('#wpgmo-slug').val().trim();
        state.label = $('#wpgmo-label').val().trim();
        $.post(WPGMO_GB.ajaxUrl,{action:'wpgmo_save_template',nonce:WPGMO_GB.nonce,slug:state.slug,template:{label:state.label,layout:state.layout}},function(resp){
            if(resp.success){
                templates[state.slug] = {label:state.label,layout:state.layout};
                state = null;
                render();
            }
        });
    }

    function deleteTemplate(slug){
        if(!confirm(WPGMO_GB.confirm)) return;
        $.post(WPGMO_GB.ajaxUrl,{action:'wpgmo_delete_template',slug:slug,nonce:WPGMO_GB.nonce},function(resp){
            if(resp.success){ delete templates[slug]; render(); }
        });
    }

    function duplicateTemplate(slug,newSlug){
        $.post(WPGMO_GB.ajaxUrl,{action:'wpgmo_duplicate_template',slug:slug,new_slug:newSlug,nonce:WPGMO_GB.nonce},function(resp){
            if(resp.success){ templates[newSlug]=templates[slug]; render(); }
        });
    }

    function setDefault(slug){
        $.post(WPGMO_GB.ajaxUrl,{action:'wpgmo_set_default_template',slug:slug,nonce:WPGMO_GB.nonce},function(resp){
            if(resp.success){ WPGMO_GB.default = slug; render(); }
        });
    }

    render();
});
