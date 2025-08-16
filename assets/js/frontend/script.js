jQuery(document).ready(function($){
    $('.aorp-category').on('click', function(){
        $(this).next('.aorp-items').slideToggle();
    });

    $('#aorp-close-cats').on('click', function(){
        $('.aorp-items').slideUp();
    });

    if($('#aorp-toggle').length===0){
        $('body').append('<div id="aorp-toggle" aria-label="Dark Mode umschalten" role="button" tabindex="0">'+aorp_ajax.icon_light+'</div>');
    } else {
        $('#aorp-toggle').html(aorp_ajax.icon_light);
    }

    function setDark(active){
        if(active){
            $('body').addClass('aorp-dark');
            $('#aorp-toggle').html(aorp_ajax.icon_dark);
            localStorage.setItem('aorp-dark-mode','on');
        }else{
            $('body').removeClass('aorp-dark');
            $('#aorp-toggle').html(aorp_ajax.icon_light);
            localStorage.setItem('aorp-dark-mode','off');
        }
        $.post(aorp_ajax.url,{action:'aorp_toggle_dark',mode:active?'on':'off'});
    }

    $('#aorp-toggle').on('click', function(){
        setDark(!$('body').hasClass('aorp-dark'));
    });

    $(document).on('keydown', function(e){
        if(e.ctrlKey && e.altKey && e.key.toLowerCase()=='d'){
            setDark(!$('body').hasClass('aorp-dark'));
        }
    });

    var stored = localStorage.getItem('aorp-dark-mode');
    var cookieMatch = document.cookie.match(/aorp_dark_mode=(on|off)/);
    if(stored){
        setDark(stored==='on');
    }else if(cookieMatch){
        setDark(cookieMatch[1]==='on');
    }else if(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches){
        setDark(true);
    }
});

document.addEventListener('DOMContentLoaded', function(){
    function normalizeText(s) {
        return s.toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/\s+/g, ' ').trim();
    }

    const search = document.querySelector('#main-search');
    const list = document.querySelector('.aorp-menu');
    const items = [...list.querySelectorAll('.aorp-item')];
    const groups = [...list.querySelectorAll('.aorp-items')];
    const cats = [...list.querySelectorAll('.aorp-category')];

    groups.forEach((grp, i) => grp.dataset.groupIndex = String(i));

    items.forEach((el, idx) => {
        if (!el.dataset.index) el.dataset.index = String(idx);
        if (!el.dataset.number) {
            const numEl = el.querySelector('.aorp-number');
            if (numEl) el.dataset.number = (numEl.textContent.match(/\d+/)||['0'])[0];
        }
        if (!el.dataset.search) {
            const name = el.querySelector('.aorp-title')?.textContent || '';
            const desc = el.querySelector('.aorp-desc')?.textContent || '';
            el.dataset.search = normalizeText(`${name} ${desc}`);
        }
        if (!el.dataset.group) {
            el.dataset.group = el.closest('.aorp-items')?.dataset.groupIndex || '';
        }
    });

    let t;
    function applyFilter(qRaw) {
        const q = normalizeText(qRaw);
        let visible = [];
        if (!q) {
            cats.forEach(cat => cat.style.display = '');
            groups.forEach(grp => grp.style.display = '');
            items.forEach(el => {
                el.style.display = '';
                const parent = groups[+el.dataset.group];
                if (parent) parent.appendChild(el);
            });
            groups.forEach(grp => {
                [...grp.children].sort((a,b)=> (+a.dataset.index) - (+b.dataset.index)).forEach(ch=>grp.appendChild(ch));
            });
        } else {
            cats.forEach(cat => cat.style.display = 'none');
            groups.forEach(grp => grp.style.display = 'none');
            items.forEach(el => {
                const match = el.dataset.search.includes(q);
                el.style.display = match ? '' : 'none';
                if (match) visible.push(el);
            });
            visible.sort((a,b)=> (+a.dataset.number) - (+b.dataset.number));
            visible.forEach(el => list.appendChild(el));
        }

        let empty = list.querySelector('.no-results');
        if (!empty) {
            empty = document.createElement('div');
            empty.className = 'no-results';
            empty.style.display = 'none';
            empty.textContent = 'Keine Treffer';
            list.appendChild(empty);
        }
        empty.style.display = (q && visible.length === 0) ? '' : 'none';
    }

    function setDimActive(on) {
        document.getElementById('search-dim')?.classList.toggle('active', on);
        document.querySelector('.main-search-wrap')?.classList.toggle('active', on);
    }

    search?.addEventListener('input', () => {
        clearTimeout(t);
        const val = search.value;
        setDimActive(document.activeElement === search || val.trim().length > 0);
        t = setTimeout(() => applyFilter(val), 100);
    });

    search?.addEventListener('focus', () => setDimActive(true));
    search?.addEventListener('blur', () => {
        if (!search.value.trim()) setDimActive(false);
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (!search.value.trim()) setDimActive(false);
        }
    });
    document.getElementById('search-dim')?.addEventListener('click', () => search?.focus());

    applyFilter(search?.value || '');
});
