const L = require('leaflet');
const xhr = require('xhr');
require('leaflet-polylinedecorator');
require('./Leaflet.Geodesic');

const map = L.map('map').setView([51.505, -0.09], 3);

L.tileLayer(MAP_TILE_LAYER_URL, {
    maxZoom: 18,
    id: 'mapbox.streets',
    accessToken: MAP_API_KEY
}).addTo(map);

map.zoomControl.setPosition('bottomright');

let legend = L.control({position: 'bottomleft'});

legend.onAdd = function () {
    const div = L.DomUtil.create('div', 'info legend');
    let legendItems = [];
    const elements = [
        {
            'color': '#3388ff',
            'label': 'Avion',
        },
        {
            'color': '#33C0FF',
            'label': 'Bateau',
        },
        {
            'color': '#000080',
            'label': 'Voiture',
        },
        {
            'color': '#00BA42',
            'label': 'Bus',
        },
        {
            'color': '#B450C5',
            'label': 'Train',
        },
    ];

    for (let element of elements) {
        legendItems.push('<div class="legend-item"><span class="legend-item-color" style="background:' + element['color'] + '"></span> <span>' + element['label'] + '</span></div>');
    }

    div.innerHTML = legendItems.join('');

    return div;
};
legend.addTo(map);

const geodesicOptions = {
    lineCap: 'round',
    opacity: 0,
    steps: 50,
};

let flights = L.geodesic([], geodesicOptions).addTo(map);
let boats = L.geodesic([], geodesicOptions).addTo(map);
let cars = L.geodesic([], L.extend(geodesicOptions, {
    color: '#000080',
    opacity: 1,
})).addTo(map);
let bus = L.geodesic([], L.extend(geodesicOptions, {
    color: '#00BA42',
    opacity: 1,
})).addTo(map);
let trains = L.geodesic([], L.extend(geodesicOptions, {
    color: '#B450C5',
    opacity: 1,
})).addTo(map);

const transportLayers = {
    'bus': bus,
    'car': cars,
    'plane': flights,
    'boat': boats,
    'train': trains,
};

const addLinesDecorations = function() {
    L.polylineDecorator(boats, {
        patterns: [
            // displays an arrow at the end of the line
            {offset: '100%', repeat: 0, symbol: L.Symbol.arrowHead({pixelSize: 10, polygon: true, pathOptions: {stroke: true, color: '#33C0FF', fillColor: '#33C0FF', fillOpacity: 1}})},

            // defines a pattern of dashes, repeated every 30px on the line
            {offset: 0, repeat: 30, symbol: L.Symbol.dash({pixelSize: 10, pathOptions: {weight: 3, color: '#33C0FF'}})},
        ]
    }).addTo(map);

    L.polylineDecorator(flights, {
        patterns: [
            // displays an arrow at the end of the line
            {offset: '100%', repeat: 0, symbol: L.Symbol.arrowHead({pixelSize: 10, polygon: true, pathOptions: {stroke: true, color: '#3388ff', fillColor: '#3388ff', fillOpacity: 1}})},

            // defines a pattern of dots, repeated every 20px on the line
            {offset: 0, repeat: 20, symbol: L.Symbol.dash({pixelSize: 0, pathOptions: {weight: 6}})},

            // displays a plane icon in the middle of the line
            //{offset: '50%', repeat: 0, symbol: L.Symbol.marker({rotate: true, markerOptions: {icon: L.icon({iconUrl: 'icon_plane.png', iconAnchor: [16, 16]})}})}
        ]
    }).addTo(map);

    L.polylineDecorator(cars, {
        patterns: [
            // displays an arrow at the end of the line
            {offset: '100%', repeat: 0, symbol: L.Symbol.arrowHead({pixelSize: 10, polygon: true, pathOptions: {stroke: true, color: '#000080', fillColor: '#000080', fillOpacity: 1}})},
        ]
    }).addTo(map);

    L.polylineDecorator(trains, {
        patterns: [
            // displays an arrow at the end of the line
            {offset: '100%', repeat: 0, symbol: L.Symbol.arrowHead({pixelSize: 10, polygon: true, pathOptions: {stroke: true, color: '#B450C5', fillColor: '#B450C5', fillOpacity: 1}})},
        ]
    }).addTo(map);

    L.polylineDecorator(bus, {
        patterns: [
            // displays an arrow at the end of the line
            {offset: '100%', repeat: 0, symbol: L.Symbol.arrowHead({pixelSize: 10, polygon: true, pathOptions: {stroke: true, color: '#00BA42', fillColor: '#00BA42', fillOpacity: 1}})},
        ]
    }).addTo(map);
};

xhr({
    method: 'GET',
    uri: './boucle.json'
}, function (err, response, body) {
    if (err || response.statusCode !== 200) {
        return;
    }

    const boucle = JSON.parse(body);

    for (let transport in boucle.steps) {
        if (!boucle.steps.hasOwnProperty(transport)) {
            continue;
        }

        let latLongs = boucle.steps[transport].map(function (step) {
            return [
                new L.LatLng(step.from.lat, step.from.long),
                new L.LatLng(step.to.lat, step.to.long),
            ];
        });

        transportLayers[transport].setLatLngs(latLongs);
    }

    addLinesDecorations();
});