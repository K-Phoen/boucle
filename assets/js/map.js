import L from 'leaflet';
import xhr from 'xhr';
import './leaflet.boucle-arrow';
import {points as turfPoints} from '@turf/helpers';
import turfBbox from '@turf/bbox';
import omnivore from '@mapbox/leaflet-omnivore';

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

        legend.onAdd = () => {
            const div = L.DomUtil.create('div', 'info legend');
            let legendItems = [];

            for (let transport of Object.values(this.config.transports)) {
                legendItems.push('<div class="legend-item"><span class="legend-item-color" style="background:' + transport['color'] + '"></span> <span>' + transport['label'] + '</span></div>');
            }

            div.innerHTML = legendItems.join('');

            return div;
        };

        legend.addTo(this.map);

        return legend;
    }

    loadSteps() {
        xhr({method: 'GET', uri: './boucle.json'}, (err, response, body) => {
            if (err || response.statusCode !== 200) {
                return;
            }

            const boucle = JSON.parse(body);

            this.drawStart(boucle);
            this.drawSteps(boucle);
            this.centerMap(boucle);
        });
    }

    centerMap(boucle) {
        let points = [
            [boucle.start.from.lat, boucle.start.from.long],
        ];

        for (let transport in boucle.steps) {
            if (!boucle.steps.hasOwnProperty(transport)) {
                continue;
            }

            boucle.steps[transport].forEach(step => points.push([step.to.lat, step.to.long]));
        }

        const bbox = turfBbox(turfPoints(points));

        this.map.fitBounds([
            [bbox[0], bbox[1]],
            [bbox[2], bbox[3]]
        ]);
    }

    drawStart(boucle) {
        const markerIcon = L.divIcon({
            className: 'start-marker-icon',
            html: '<span style="background-color: ' + this.config.transports[boucle.start.with]['color'] + '"></span>',
        });

        L.marker([boucle.start.from.lat, boucle.start.from.long], {icon: markerIcon}).addTo(this.map);
    }

    drawSteps(boucle) {
        for (let transport in boucle.steps) {
            if (!boucle.steps.hasOwnProperty(transport)) {
                continue;
            }

            boucle.steps[transport].forEach(step => {
                if (step.path) {
                    this.drawPath(transport, step);
                } else {
                    this.drawArrow(transport, step);
                }
            });
        }
    }

    drawPath(transport, step) {
        const customLayer = L.geoJson(null, {
            // http://leafletjs.com/reference.html#geojson-style
            style: () => {
                return {
                    color: this.config.transports[transport]['color'],
                    dashArray: this.config.transports[transport]['dashed'] ? '10 10': '',
                    weight: 3,
                };
            },
        });

        customLayer.bindPopup(this.popupContent(step));

        omnivore.gpx(step.path, null, customLayer).addTo(this.map);
    }

    drawArrow(transport, step) {
        const arrow = L.boucleArrow([step.from.lat, step.from.long], [step.to.lat, step.to.long], {
            color: this.config.transports[transport]['color'],
            dashArray: this.config.transports[transport]['dashed'] ? '10 10': '',
            weight: 3,
        });

        arrow.addTo(this.map);

        arrow.getCurve().bindPopup(this.popupContent(step));
    }

    popupContent(step) {
        return step.from.name + ' → ' + step.to.name + ' – ' + step.date;
    }
}

new MapView({
    container: 'map',

    tileLayerUrl: MAP_TILE_LAYER_URL,
    tileLayerApiKey: MAP_API_KEY,

    transports: {
        'walking': {
            'color': '#37392e',
            'dashed': false,
            'label': 'Walking'
        },
        'plane': {
            'color': '#2e5266',
            'dashed': true,
            'label': 'Plane'
        },
        'boat': {
            'color': '#6a4c93',
            'dashed': true,
            'label': 'Boat'
        },
        'car': {
            'color': '#e94f37',
            'dashed': false,
            'label': 'Car'
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
