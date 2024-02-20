<template>
    <div class="embed-projects-map-container" :id="'random-'+random" ref="mapContainer" :class="{'is-loading': isLoading}"></div>
    <div class="embed-projects-map-container-legend" v-if="topics.length">
        <div class="embed-projects-map-container-legend-row" v-for="(mainTopic, id) in mainTopics">
            <div class="embed-projects-map-container-legend-row-thumb" :style="{ background: mainTopic.color }"></div>
            <div class="embed-projects-map-container-legend-row-label">{{ $t(translateField(mainTopic, 'name', locale), locale) }}</div>
        </div>
    </div>
    <transition name="embed-regions-overlay" mode="out-in">
        <div class="embed-projects-map-container-loader" v-if="isLoading">{{ $t('Karte wird geladen...', locale) }}</div>
    </transition>
</template>

<script>
import mapboxgl from 'mapbox-gl/dist/mapbox-gl';
import api from '../api';
import {translateField} from '../utils/filters';
import swissmaptiles from '../../../config/gis/swissmaptiles.json';

export default {

    data() {
        return {
            random: Math.random().toString(16),
            map: null,
            isLoading: true,
            topics: [],
            geoJson: {},
            colors: {
                1: '#2e85da',
                2: '#d3455f',
                3: '#e8d058',
                4: '#bdc0c2',
            },
            markers: {},
            markersOnScreen: {},
            mainTopics: [
                {
                    id: 1,
                    name: 'Regionale Innovationssysteme (RIS)',
                    color: '#2e85da',
                },
                {
                    id: 2,
                    name: 'Industrie',
                    color: '#d3455f',
                },
                {
                    id: 3,
                    name: 'Tourismus',
                    color: '#e8d058',
                },
                {
                    id: 4,
                    name: 'Sonstige',
                    color: '#bdc0c2',
                },
            ],
        };
    },

    props: {
        locale: {
            type: String,
            required: true,
        },
        projects: {
            type: Array,
            required: true,
        },
        filters: {
            type: Object,
            required: false,
            default: {},
        },
    },

    emits: [
        'clickProject',
        'clickCluster',
    ],

    computed: {

    },

    created() {
        mapboxgl.accessToken = this.$clientOptions?.mapboxApiToken;
    },

    mounted() {

        let minZoom = 7;

        if(this.$refs.mapContainer && this.$refs.mapContainer.offsetWidth < 840) {
            minZoom = 5.8;
        }

        this.map = new mapboxgl.Map({
            container: this.$refs.mapContainer.id,
            style: swissmaptiles,
            center: [
                8.24245129900188,
                46.94058782182699
            ],
            zoom: minZoom,
            minZoom: minZoom,
            maxZoom: 14,
        });

        this.map.dragRotate.disable();
        this.map.touchZoomRotate.disableRotation();
        this.map.keyboard.disableRotation();

        this.map.addControl(new mapboxgl.NavigationControl({
            showCompass: false,
            showZoom: true,
        }));

        let component = this;

        this.map.addControl({
            onAdd(map) {
                const div = document.createElement('div');
                div.className = 'mapboxgl-ctrl mapboxgl-ctrl-group';
                div.innerHTML = `<button>
                                     <svg focusable="false" viewBox="0 0 24 24" aria-hidden="true" style="font-size: 20px;"><title>Reset map</title><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"></path></svg>
                                 </button>`;
                div.addEventListener('contextmenu', (e) => e.preventDefault());
                div.addEventListener('click', () => component.fitBounds());

                return div;
            },
            onRemove(map) {

            },
        });

        this.map.on('load', async () => {

            response = await api.topics.getAll();

            this.topics = response.data
                .filter(topic => topic.isPublic && (!topic.context || topic.context === 'project'));

            let response = await api.projects.geoJson(this.filters);

            this.geoJson = response.data;

            this.map.addSource('projects', {
                type: 'geojson',
                generateId: true,
                data: this.geoJson,
                cluster: true,
                clusterRadius: 50,
                clusterProperties: {
                    mainTopic1: ['+', ['case', ['==', 1, ['get', 'mainTopic']], 1, 0]],
                    mainTopic2: ['+', ['case', ['==', 2, ['get', 'mainTopic']], 1, 0]],
                    mainTopic3: ['+', ['case', ['==', 3, ['get', 'mainTopic']], 1, 0]],
                    mainTopic4: ['+', ['case', ['==', 4, ['get', 'mainTopic']], 1, 0]],
                },
            });

            this.map.addLayer({
                id: 'projects',
                type: 'circle',
                source: 'projects',
                paint: {
                    'circle-color': [
                        'match',
                        ['get', 'mainTopic'],
                        ...this.mainTopics
                            .map(mainTopic => ([mainTopic.id, mainTopic.color]))
                            .reduce((a, b) => [...a, ...b], []),
                        this.mainTopics[this.mainTopics.length - 1].color,
                    ],
                    'circle-opacity': 1,
                    'circle-radius': 8,
                    'circle-stroke-width': 2,
                    'circle-stroke-color': '#000000',
                },
            });

            this.map.on('render', () => {
                if (!this.map.isSourceLoaded('projects')) {
                    return;
                }
                this.updateMarkers();
            });

            this.map.on('click', 'projects', (event) => {

                let features = this.map.queryRenderedFeatures(event.point, {
                    layers: ['projects'],
                });

                let feature = features?.find(feature => feature.source === 'projects');

                if(!feature) {
                    return;
                }

                if(feature.properties?.cluster) {
                    /*this.map.getSource('projects').getClusterLeaves(feature.properties.cluster_id, feature.properties.point_count, 0, (error, features) => {
                        this.$emit('clickCluster', features.map(feature => feature.properties?.id));
                    })*/
                    return;
                }

                this.$emit('clickProject', feature.properties?.id);

            });

            this.map.on('mousemove', (event) => {

                this.map.getCanvasContainer().style.cursor = null;

                let features = this.map.queryRenderedFeatures(event.point, {
                    layers: ['projects'],
                });

                let feature = features?.find(feature => feature.source === 'projects');

                if(feature) {
                    this.map.getCanvasContainer().style.cursor = 'pointer';
                }

            });

            this.map.once('idle', async () => {
                this.fitBounds();
                this.isLoading = false;
            });
        });

    },

    beforeUnmount() {
        this.map.remove();
    },

    methods: {

        translateField,

        getBounds () {
            let bounds = new mapboxgl.LngLatBounds();

            this.geoJson?.features?.forEach(feature => bounds.extend(feature.geometry.coordinates));

            return bounds;
        },

        fitBounds () {

            this.map.fitBounds(this.getBounds(), {
                padding: 50,
            });

        },

        updateMarkers() {
            const newMarkers = {};
            const features = this.map.querySourceFeatures('projects');

            for (const feature of features) {

                const coords = feature.geometry.coordinates;
                const props = feature.properties;

                if (!props.cluster) {
                    continue;
                }

                const id = props.cluster_id;

                let marker = this.markers[id];
                if (!marker) {
                    const el = this.createDonutChart(props);
                    el.style.cursor = 'pointer';
                    el.addEventListener('click', () => {
                        this.map.getSource('projects').getClusterLeaves(feature.properties.cluster_id, feature.properties.point_count, 0, (error, features) => {
                            this.$emit('clickCluster', features.map(feature => feature.properties?.id));
                        });
                    });
                    marker = this.markers[id] = new mapboxgl.Marker({
                        element: el
                    }).setLngLat(coords);
                }
                newMarkers[id] = marker;

                if (!this.markersOnScreen[id]) {
                    marker.addTo(this.map);
                }
            }

            for (const id in this.markersOnScreen) {
                if (!newMarkers[id]) {
                    this.markersOnScreen[id].remove();
                }
            }

            this.markersOnScreen = newMarkers;
        },

        createDonutChart(props) {

            const offsets = [];

            const segments = this.mainTopics.map(mainTopic => {
                return [mainTopic.id, props['mainTopic'+mainTopic.id], mainTopic.color];
            });

            let total = 0;

            for (let segment of segments) {
                offsets.push(total);
                total = total + segment[1];
            }

            let fontSize = 12;
            let r = 20;

            if(total > 50) {
                fontSize = 14;
                r = 24;
            }

            if(total > 100) {
                fontSize = 16;
                r = 28;
            }

            let r0 = Math.round(r * 0.6);
            let w = r * 2;

            let html = `<svg width="${w}" height="${w}" viewbox="0 0 ${w} ${w}" text-anchor="middle" style="font: ${fontSize}px sans-serif; display: block">`;

            for (let i = 0; i < segments.length; i++) {
                html += this.donutSegment(
                    offsets[i] / total,
                    (offsets[i] + segments[i][1]) / total,
                    r,
                    r0,
                    segments[i][2]
                );
            }

            html += `
                    <circle cx="${r}" cy="${r}" r="${r0}" fill="white" />
                    <text dominant-baseline="central" transform="translate(${r}, ${r})">
                        ${total.toLocaleString()}
                    </text>
                    </svg>
            `;

            const el = document.createElement('div');
            el.innerHTML = html;

            return el;
        },

        donutSegment(start, end, r, r0, color) {
            if (end - start === 1) end -= 0.00001;
            const a0 = 2 * Math.PI * (start - 0.25);
            const a1 = 2 * Math.PI * (end - 0.25);
            const x0 = Math.cos(a0),
                y0 = Math.sin(a0);
            const x1 = Math.cos(a1),
                y1 = Math.sin(a1);
            const largeArc = end - start > 0.5 ? 1 : 0;

            // draw an SVG path
            return `<path d="M ${r + r0 * x0} ${r + r0 * y0} L ${r + r * x0} ${
                        r + r * y0
                    } A ${r} ${r} 0 ${largeArc} 1 ${r + r * x1} ${r + r * y1} L ${
                        r + r0 * x1
                    } ${r + r0 * y1} A ${r0} ${r0} 0 ${largeArc} 0 ${r + r0 * x0} ${
                        r + r0 * y0
                    }" fill="${color}" />`;
        },

    },

    watch: {
        filters: {
            handler(newVal, oldVal) {
                (async () => {
                    let response = await api.projects.geoJson(this.filters);

                    this.geoJson = response.data;

                    this.map.getSource('projects').setData(response.data);
                })();
            },
        },
    },

};
</script>