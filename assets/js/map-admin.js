(function(){
    document.addEventListener('DOMContentLoaded', function(){
        var mapEl = document.getElementById('aio-leaflet-map-admin');
        var latInput = document.getElementById('aio_leaflet_lat');
        var lngInput = document.getElementById('aio_leaflet_lng');
        var zoomInput = document.getElementById('aio_leaflet_zoom');
        if(!mapEl || !latInput || !lngInput) return;
        var lat = parseFloat(latInput.value) || 0;
        var lng = parseFloat(lngInput.value) || 0;
        var zoom = parseInt(zoomInput ? zoomInput.value : 13, 10) || 13;
        var map = L.map(mapEl).setView([lat, lng], zoom);
        var dark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        var tileUrl = dark ?
            'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png' :
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        L.tileLayer(tileUrl, { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
        var marker = L.marker([lat, lng]).addTo(map);
        function setInputs(latlng){
            latInput.value = latlng.lat.toFixed(6);
            lngInput.value = latlng.lng.toFixed(6);
        }
        map.on('click', function(e){
            marker.setLatLng(e.latlng);
            setInputs(e.latlng);
        });
        if(zoomInput){
            map.on('zoomend', function(){
                zoomInput.value = map.getZoom();
            });
        }
    });
})();
