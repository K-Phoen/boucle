import L from 'leaflet';
import 'leaflet-curve';
import {points as turfPoints} from '@turf/helpers';
import turfCenter from '@turf/center';

const BoucleArrow = L.Class.extend({
    options: {
        factor: 0.3,
        color: '#3388ff',
        popup: null,
    },

    initialize: function (fromLatlng, toLatlng, options){
        L.setOptions(this, options);

        this.fromLatlng = L.latLng(fromLatlng);
        this.toLatlng = L.latLng(toLatlng);
    },

    addTo: function (map) {
        this._map = map;

        const curve = this.getCurve();

        curve.addTo(map);

        const arrowId = 'lala';
        const arrow = this._createArrow(arrowId);
        curve.getPane().appendChild(arrow);
        curve._path.setAttribute('marker-end', `url(#${arrowId})`);

        return this;
    },

    getCurve: function () {
        if (this._curve) {
            return this._curve;
        }

        const controlLatlng = this._getControlPoint(L.latLng(this.fromLatlng), L.latLng(this.toLatlng));

        this._curve = L.curve([
            'M', [this.fromLatlng.lat, this.fromLatlng.lng],
            'Q', [controlLatlng.lat, controlLatlng.lng], [this.toLatlng.lat, this.toLatlng.lng]
        ], this.options);

        return this._curve;
    },

    _createArrow: function (arrowId) {
        const svg = L.SVG.create('svg');
        const container = L.SVG.create('defs');
        const marker = L.SVG.create('marker');
        const path = L.SVG.create('polyline');

        marker.setAttribute('id', arrowId);
        marker.setAttribute('markerWidth', '6.75');
        marker.setAttribute('markerHeight', '6.75');
        marker.setAttribute('viewBox', '-10 -10 20 20');
        marker.setAttribute('orient', 'auto');
        marker.setAttribute('refX', '0');
        marker.setAttribute('refY', '0');
        marker.setAttribute('fill', 'none');
        marker.setAttribute('stroke', this.options.color);
        marker.setAttribute('stroke-width', 3);

        path.setAttribute('stroke-linejoin', 'bevel');
        path.setAttribute('fill', this.options.color);
        path.setAttribute('stroke', this.options.color);
        path.setAttribute('points', '-6.75,-6.75 0,0 -6.75,6.75');

        marker.appendChild(path);

        container.appendChild(marker);
        svg.appendChild(container);

        return svg;
    },

    _getControlPoint: function (start, end) {
        const features = turfPoints([
            [start.lat, start.lng],
            [end.lat, end.lng],
        ]);

        const center = turfCenter(features);

        // get pixel coordinates for start, end and center
        const startPx = this._map.latLngToContainerPoint(start);
        const centerPx = this._map.latLngToContainerPoint(L.latLng(center.geometry.coordinates[0], center.geometry.coordinates[1]));
        const rotatedPx = this._rotatePoint(centerPx, startPx, 90);

        const distance = Math.sqrt(Math.pow(startPx.x - centerPx.x, 2) + Math.pow(startPx.y - centerPx.y, 2));
        const angle = Math.atan2(rotatedPx.y - centerPx.y, rotatedPx.x - centerPx.x);
        const offset = (this.options.factor * distance) - distance;

        const sin = Math.sin(angle) * offset;
        const cos = Math.cos(angle) * offset;

        const controlPoint = L.point(rotatedPx.x + cos, rotatedPx.y + sin);

        return this._map.containerPointToLatLng(controlPoint);
    },

    _rotatePoint: function (origin, point, angle) {
        const radians = angle * Math.PI / 180.0;

        return {
            x: Math.cos(radians) * (point.x - origin.x) - Math.sin(radians) * (point.y - origin.y) + origin.x,
            y: Math.sin(radians) * (point.x - origin.x) + Math.cos(radians) * (point.y - origin.y) + origin.y
        };
    },
});

L.boucleArrow = function (fromLatlng, toLatlng, options) {
    return new BoucleArrow(fromLatlng, toLatlng, options);
};
