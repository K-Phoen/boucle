const L = require('leaflet');
const xhr = require('xhr');
require('leaflet-swoopy');

const map = L.map('map').setView([51.505, -0.09], 3);

L.tileLayer(MAP_TILE_LAYER_URL, {
    maxZoom: 18,
    id: 'mapbox.streets',
    accessToken: MAP_API_KEY
}).addTo(map);

map.zoomControl.setPosition('bottomright');

let legend = L.control({position: 'bottomleft'});

const transports = {
    'plane': {
        'color': '#3388ff',
        'label': 'Avion',
    },
    'boat': {
        'color': '#33C0FF',
        'label': 'Bateau',
    },
    'car': {
        'color': '#000080',
        'label': 'Voiture',
    },
    'bus': {
        'color': '#00BA42',
        'label': 'Bus',
    },
    'train': {
        'color': '#B450C5',
        'label': 'Train',
    }
};

legend.onAdd = function () {
    const div = L.DomUtil.create('div', 'info legend');
    let legendItems = [];

    for (let transport of Object.values(transports)) {
        legendItems.push('<div class="legend-item"><span class="legend-item-color" style="background:' + transport['color'] + '"></span> <span>' + transport['label'] + '</span></div>');
    }

    div.innerHTML = legendItems.join('');

    return div;
};
legend.addTo(map);

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

        boucle.steps[transport].forEach(function (step) {
            L.swoopyArrow([step.from.lat, step.from.long], [step.to.lat, step.to.long], {
                color: transports[transport]['color'],
                weight: 2,
                factor: 0.3,

                arrowFilled: true,
            }).addTo(map);
        });
    }
});