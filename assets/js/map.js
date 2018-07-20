import L from 'leaflet';
import xhr from 'xhr';
import './leaflet.boucle-arrow';
import {points as turfPoints} from '@turf/helpers';
import turfBbox from '@turf/bbox';
import omnivore from '@mapbox/leaflet-omnivore';
import Pikaday from 'pikaday';

import me_marker_img from '../img/me-marker.png';

class MapView {
    constructor(config = {}) {
        this.config = config;

        this.map = this.mountMap();
        this.boucle = {};

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

    mountDatePicker() {
        const container = L.control({position: 'bottomright'});
        const datepicker = document.createElement('div');
        const picker = new Pikaday({
            firstDay: 1,
            minDate: new Date(this.boucle.start.departure_date),
            maxDate: new Date(this.boucle.end.arrival_date),
            onSelect: (date) => {
                picker.hide();
                this.addMeMarker(date);
            }
        });

        datepicker.setAttribute('id', 'datepicker');
        datepicker.appendChild(picker.el);
        picker.hide();

        document.getElementById('map').appendChild(datepicker);

        container.onAdd = () => {
            const div = L.DomUtil.create('div');

            div.innerHTML = '<div class="leaflet-bar"><a href="#" title="Calendar" role="button" aria-label="Calendar">📅</a></div>';

            div.onclick = () => picker.show();

            return div;
        };

        container.addTo(this.map);
    }

    loadSteps() {
        xhr({method: 'GET', uri: './boucle.json'}, (err, response, body) => {
            if (err || response.statusCode !== 200) {
                return;
            }

            this.boucle = JSON.parse(body);

            this.drawStart();
            this.drawSteps();
            this.centerMap();
            this.addMeMarker(new Date());
            this.mountDatePicker();
        });
    }

    centerMap() {
        let points = [
            [this.boucle.start.from.lat, this.boucle.start.from.long],
        ];

        for (let transport in this.boucle.steps) {
            if (!this.boucle.steps.hasOwnProperty(transport)) {
                continue;
            }

            this.boucle.steps[transport].forEach(step => points.push([step.to.lat, step.to.long]));
        }

        const bbox = turfBbox(turfPoints(points));

        this.map.fitBounds([
            [bbox[0], bbox[1]],
            [bbox[2], bbox[3]]
        ]);
    }

    addMeMarker(now) {
        const markerIcon = L.icon({
            iconUrl: './dist/'+me_marker_img,
            iconSize: [32, 32], // size of the icon
            iconAnchor: [16, 32], // point of the icon which will correspond to marker's location
        });

        if (this.meMarker) {
            this.meMarker.remove();
            this.meMarker = null;
        }

        for (let transport in this.boucle.steps) {
            if (!this.boucle.steps.hasOwnProperty(transport)) {
                continue;
            }

            this.boucle.steps[transport].forEach(step => {
                if (step.departure_date.length == 0) {
                    return;
                }

                const arrival = new Date(step.arrival_date);
                const departure = new Date(step.departure_date);

                if (arrival <= now && departure > now) {
                    this.meMarker = L.marker(
                        [step.to.lat, step.to.long],
                        {icon: markerIcon}
                    );

                    this.meMarker.addTo(this.map);
                }
            });
        }
    }

    drawStart() {
        const markerIcon = L.divIcon({
            className: 'start-marker-icon',
            html: '<span style="background-color: ' + this.config.transports[this.boucle.start.with]['color'] + '"></span>',
        });

        L.marker(
            [this.boucle.start.from.lat, this.boucle.start.from.long],
            {icon: markerIcon}
        ).addTo(this.map);
    }

    drawSteps() {
        for (let transport in this.boucle.steps) {
            if (!this.boucle.steps.hasOwnProperty(transport)) {
                continue;
            }

            this.boucle.steps[transport].forEach(step => {
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
        return step.from.name + ' → ' + step.to.name + ' – ' + step.arrival_date;
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
