(function(){
    document.addEventListener('DOMContentLoaded', function(){
        if (typeof aio_leaflet_map_settings === 'undefined') return;
        var opts = aio_leaflet_map_settings;
        var lat = parseFloat(opts.lat) || 0;
        var lng = parseFloat(opts.lng) || 0;
        var zoom = parseInt(opts.zoom, 10) || 13;
        var popup = opts.popup || '';
        var container = document.getElementById('aio-leaflet-map');
        if(!container) return;
        var map = L.map(container).setView([lat, lng], zoom);
        var dark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        var tileUrl = dark ?
            'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png' :
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        L.tileLayer(tileUrl, { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
        L.marker([lat, lng]).addTo(map).bindPopup(popup);
    });
})();
