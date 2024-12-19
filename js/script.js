document.addEventListener('DOMContentLoaded', function() {
    mapboxgl.accessToken = 'pk.eyJ1IjoieWhhbmllMTUiLCJhIjoiY2x5bHBrenB1MGxmczJpczYxbjRxbGxsYSJ9.DPO8TGv3Z4Q9zg08WhfoCQ';
    var map = new mapboxgl.Map({
        container: 'map_dashboard',
        style: 'mapbox://styles/mapbox/streets-v11',
        center: [121.0437, 14.6760], // Coordinates of Quezon City
        zoom: 12
    });

    var marker = new mapboxgl.Marker()
        .setLngLat([121.0437, 14.6760])
        .addTo(map);
});

document.addEventListener('DOMContentLoaded', function() {
    mapboxgl.accessToken = 'pk.eyJ1IjoieWhhbmllMTUiLCJhIjoiY2x5bHBrenB1MGxmczJpczYxbjRxbGxsYSJ9.DPO8TGv3Z4Q9zg08WhfoCQ';
    var map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/streets-v11',
        center: [121.0437, 14.6760], // Coordinates of Quezon City
        zoom: 12
    });

    var marker = new mapboxgl.Marker()
        .setLngLat([121.0437, 14.6760])
        .addTo(map);
});

