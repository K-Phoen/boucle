import L from 'leaflet';
import xhr from 'xhr';
import './leaflet.boucle-arrow';

class MapView {
    constructor(config = {}) {
        this.config = config;

        this.map = this.mountMap();

        this.mountLegend();
        this.loadSteps();
    }

    mountMap() {
        const map = L.map(this.config.container);

        new L.TileLayer(this.config.tileLayerUrl, {
            maxZoom: 18,
            id: 'mapbox.streets',
            accessToken: this.config.tileLayerApiKey
        }).addTo(map);

        map.zoomControl.setPosition('bottomright');
        map.setView([51.505, -0.09], 3);

        return map;
    }

    mountLegend() {
        const legend = L.control({position: 'bottomleft'});
        const transports = this.config.transports;

        legend.onAdd = function () {
            const div = L.DomUtil.create('div', 'info legend');
            let legendItems = [];

            for (let transport of Object.values(transports)) {
                legendItems.push('<div class="legend-item"><span class="legend-item-color" style="background:' + transport['color'] + '"></span> <span>' + transport['label'] + '</span></div>');
            }

            div.innerHTML = legendItems.join('');

            return div;
        };
        legend.addTo(this.map);

        return legend;
    }

    loadSteps() {
        const map = this.map;
        const transports = this.config.transports;

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
                    const arrow = L.boucleArrow([step.from.lat, step.from.long], [step.to.lat, step.to.long], {
                        color: transports[transport]['color'],
                        dashArray: transports[transport]['dashed'] ? '10 10': '',
                        weight: 2,
                    });

                    arrow.addTo(map);

                    arrow.getCurve().bindPopup(step.from.name + ' → ' + step.to.name + ' – ' + step.date);
                });
            }

            // add the "start" marker
            const markerIcon = L.divIcon({
                className: 'start-marker-icon',
                html: '<span style="background-color: ' + transports[boucle.start.with]['color'] + '"></span>',
            });
            L.marker([boucle.start.from.lat, boucle.start.from.long], {icon: markerIcon}).addTo(map);
        });
    }
}

new MapView({
    container: 'map',

    tileLayerUrl: MAP_TILE_LAYER_URL,
    tileLayerApiKey: MAP_API_KEY,

    transports: {
        'plane': {
            'color': '#2e5266',
            'dashed': true,
            'label': 'Avion'
        },
        'boat': {
            'color': '#6a4c93',
            'dashed': true,
            'label': 'Bateau'
        },
        'car': {
            'color': '#e94f37',
            'dashed': false,
            'label': 'Voiture'
        },
        'bus': {
            'color': '#039960',
            'dashed': false,
            'label': 'Bus'
        },
        'train': {
            'color': '#f49d37',
            'dashed': false,
            'label': 'Train'
        }
    },
});
