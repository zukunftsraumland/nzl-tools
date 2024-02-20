<template>
    <div class="embed-regions-content-map-container" :id="'random-'+random" ref="mapContainer" :class="{'is-loading': isLoading}"></div>
    <div class="embed-regions-content-map-container-popup" v-if="hoverCity">
        <strong>{{ $t('Gemeinde', locale) }}:</strong> {{ hoverCity?.name }}<br>
        <template v-if="hoverRegions.length">
            <strong v-if="hoverRegions.length < 2">{{ $t('Region', locale) }}:</strong>
            <strong v-else>{{ $t('Regionen', locale) }}:</strong>
            <template v-for="(hoverRegion, index) in hoverRegions">
                <template v-if="index"> / </template>
                {{ translateField(hoverRegion, 'name', locale) }}
            </template>
        </template>
    </div>
    <div class="embed-regions-content-map-container-legend" :style="{display: hasOverlappingRegions ? 'block' : 'none'}">
        <div class="embed-regions-content-map-container-legend-row">
            <div class="embed-regions-content-map-container-legend-row-thumb" ref="legendMultiRegion"></div>
            <div class="embed-regions-content-map-container-legend-row-label">{{ $t('Mehreren Regionen zugehörig', locale) }}</div>
        </div>
    </div>
    <transition name="embed-regions-overlay" mode="out-in">
        <div class="embed-regions-content-map-container-loader" v-if="isLoading">{{ $t('Karte wird geladen...', locale) }}</div>
    </transition>
</template>

<script>
import mapboxgl from 'mapbox-gl/dist/mapbox-gl';
import api from '../api';
import {sleep, translateField} from '../utils/filters';
import intersect from '@turf/intersect';
import combine from '@turf/combine';
import swissmaptiles from '../../../config/gis/swissmaptiles.json';

export default {

    data() {
        return {
            random: Math.random().toString(16),
            map: null,
            hoverCity: null,
            hoverRegion: null,
            hoverRegions: [],
            renderLoop: null,
            isLoading: true,
            geoJson: {},
            regionsGeoJSON: {},
            hasOverlappingRegions: false,
        };
    },

    props: {
        locale: {
            type: String,
            required: true,
        },
        regionType: {
            type: String,
            required: true,
        },
        activeCity: {
            type: Object,
        },
        activeRegion: {
            type: Object,
        },
    },

    emits: [
        'clickCity',
        'clickRegion',
    ],

    computed: {

    },

    created() {
        mapboxgl.accessToken = this.$env.MAPBOX_API_TOKEN;
    },

    mounted() {

        let minZoom = 7;

        if(this.$refs.mapContainer && this.$refs.mapContainer.offsetWidth < 840) {
            minZoom = 5.8;
        }

        this.map = new mapboxgl.Map({
            container: this.$refs.mapContainer.id,
            //style: 'mapbox://styles/mapbox/empty-v9',
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

            let response = await api.regions.cities();

            this.geoJson = response.data;

            let insertBefore = 'waterway_line_label';

            this.map.addSource('cities', {
                type: 'geojson',
                generateId: true,
                data: this.geoJson,
            }, insertBefore);

            this.map.addSource('cities-hover', {
                type: 'geojson',
                generateId: true,
                data: {
                    type: 'FeatureCollection',
                    features: [],
                },
            }, insertBefore);

            this.map.addSource('cities-active', {
                type: 'geojson',
                generateId: true,
                data: {
                    type: 'FeatureCollection',
                    features: [],
                },
            }, insertBefore);

            this.map.addSource('regions', {
                type: 'geojson',
                generateId: true,
                data: {
                    type: 'FeatureCollection',
                    features: [],
                },
            }, insertBefore);

            this.map.addSource('regions-hover', {
                type: 'geojson',
                generateId: true,
                data: {
                    type: 'FeatureCollection',
                    features: [],
                },
            }, insertBefore);

            this.map.addSource('regions-active', {
                type: 'geojson',
                generateId: true,
                data: {
                    type: 'FeatureCollection',
                    features: [],
                },
            }, insertBefore);

            const canvas = document.createElement('canvas');
            canvas.width = 5;
            canvas.height = 10;

            const ctx = canvas.getContext('2d');
            ctx.beginPath();
            ctx.fillStyle = '#a4a4a4';
            ctx.rect(4, 0, 1, 8);
            ctx.fill();

            const stripes = canvas.toDataURL('image/png');

            this.$refs.legendMultiRegion.style.backgroundImage = 'url('+stripes+')';
            this.$refs.legendMultiRegion.style.backgroundColor = '#eaefd5';

            this.map.loadImage(
                stripes,
                (err, image) => {
                    this.map.addImage('intersect-pattern', image);
                }
            );

            this.map.addSource('regions-intersect', {
                type: 'geojson',
                generateId: true,
                data: {
                    type: 'FeatureCollection',
                    features: [],
                },
            }, insertBefore);

            this.map.addSource('regions-outline', {
                type: 'geojson',
                generateId: true,
                data: {
                    type: 'FeatureCollection',
                    features: [],
                },
            }, insertBefore);

            this.map.addLayer({
                id: 'cities',
                type: 'fill',
                source: 'cities',
                paint: {
                    'fill-color': '#F3F3F3',
                    'fill-opacity': 0,
                },
            }, insertBefore);

            this.map.addLayer({
                id: 'regions',
                type: 'fill',
                source: 'regions',
                paint: {
                    'fill-color': '#eaefd5',
                    'fill-opacity': .25,
                },
            }, insertBefore);

            this.map.addLayer({
                id: 'regions-hover',
                type: 'fill',
                source: 'regions-hover',
                paint: {
                    'fill-color': '#FFFFFF',
                    'fill-opacity': .75,
                },
            }, insertBefore);

            this.map.addLayer({
                id: 'regions-intersect',
                type: 'fill',
                source: 'regions-intersect',
                paint: {
                    'fill-pattern': 'intersect-pattern',
                },
            }, insertBefore);

            this.map.addLayer({
                id: 'regions-active',
                type: 'fill',
                source: 'regions-active',
                paint: {
                    'fill-color': '#D3E292',
                    'fill-opacity': .75,
                },
            }, insertBefore);

            this.map.addLayer({
                id: 'regions-outline',
                type: 'line',
                source: 'regions-outline',
                paint: {
                    'line-width': 2,
                    'line-color': '#a4a4a4',
                    'line-opacity': 1,
                },
            }, insertBefore);

            this.map.addLayer({
                id: 'cities-hover',
                type: 'fill',
                source: 'cities-hover',
                paint: {
                    'fill-color': '#a4a4a4',
                    'fill-opacity': 0,
                },
            }, insertBefore);

            this.map.addLayer({
                id: 'cities-active',
                type: 'fill',
                source: 'cities-active',
                paint: {
                    'fill-color': '#FFFFFF',
                    'fill-opacity': 0,
                },
            }, insertBefore);

            this.map.on('mousemove', (event) => {

                this.map.getCanvasContainer().style.cursor = null;

                let features = this.map.queryRenderedFeatures(event.point, {
                    layers: ['cities'],
                });

                let feature = features?.find(feature => feature.source === 'cities');

                if(feature) {
                    this.map.getCanvasContainer().style.cursor = 'pointer';
                }

                this.hoverCity = feature ? JSON.parse(feature.properties?.city) : null;

                this.map.getSource('cities-hover').setData({
                    type: 'FeatureCollection',
                    features: feature ? [feature] : [],
                });

                let regions = feature ? JSON.parse(feature.properties?.regions).filter(region => region.type === this.regionType) : [];

                if(!regions.length) {
                    this.map.getSource('regions-hover').setData({
                        type: 'FeatureCollection',
                        features: [],
                    });
                }

                if(regions.length && this.hoverRegions !== regions) {
                    this.map.getSource('regions-hover').setData({
                        type: 'FeatureCollection',
                        features: this.geoJson.features
                            .filter(feature => feature.properties?.regions?.find(r => regions.find(region => r.id === region.id))),
                    });
                }

                this.hoverRegions = regions;

            });

            this.map.on('click', 'cities', (event) => {

                let features = this.map.queryRenderedFeatures(event.point, {
                    layers: ['cities'],
                });

                let feature = features?.find(feature => feature.source === 'cities');

                if(!feature) {
                    return;
                }

                this.$emit('clickCity', JSON.parse(feature.properties?.city));

                this.$emit('clickRegion', JSON.parse(feature.properties?.regions).find(region => region.type === this.regionType));

            });

            this.map.once('idle', async () => {

                await this.updateRegions();

                this.map.once('idle', async () => {

                    this.fitBounds();

                    await sleep(50);

                    this.isLoading = false;

                    // TODO: cleanup

                    this.map.once('idle', async () => {

                        this.map.getSource('cities-active').setData({
                            type: 'FeatureCollection',
                            features: [],
                        });

                        if(this.activeCity) {
                            this.map.getSource('cities-active').setData({
                                type: 'FeatureCollection',
                                features: this.geoJson.features.filter(feature => feature.properties?.city?.id === this.activeCity.id),
                            });
                        }

                        this.map.getSource('regions-active').setData({
                            type: 'FeatureCollection',
                            features: [],
                        });

                        if(this.activeRegion) {
                            this.map.getSource('regions-active').setData({
                                type: 'FeatureCollection',
                                features: this.geoJson.features.filter(feature => feature.properties?.regions?.find(region => region.id === this.activeRegion.id)),
                            });
                        }

                    });

                });

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

            this.geoJson.features
                .forEach(feature => feature.geometry.coordinates
                    .forEach(coordinates => coordinates
                        .forEach(coordinate => coordinate?.length === 2 ? bounds.extend(coordinate) : null)
                    )
                );

            return bounds;
        },

        fitBounds () {

            this.map.fitBounds(this.getBounds(), {
                padding: 50,
            });

        },

        async updateRegions () {

            let regionsGeoJSON = (await api.regions.regions(this.regionType)).data;

            this.regionsGeoJSON = regionsGeoJSON;

            this.map.getSource('regions').setData(regionsGeoJSON);

            this.map.getSource('regions-outline').setData(regionsGeoJSON);

            let intersectFeatures = [];

            regionsGeoJSON.features.forEach((a) => {
                regionsGeoJSON.features.forEach((b) => {
                    if(a === b) {
                        return;
                    }

                    intersectFeatures.push(intersect(a, b));
                });
            });

            intersectFeatures = intersectFeatures.filter(a => a);

            this.hasOverlappingRegions = intersectFeatures.length > 0;

            this.map.getSource('regions-intersect').setData(combine({
                type: 'FeatureCollection',
                features: intersectFeatures,
            }));

        },

    },

    watch: {

        regionType () {
            (async () => {
                this.isLoading = true;
                await sleep(50);
                await this.updateRegions();
                await sleep(50);
                this.fitBounds();
                await sleep(50);
                this.isLoading = false;
            })();
        },

        activeCity (newVal) {
            this.map.getSource('cities-active').setData({
                type: 'FeatureCollection',
                features: [],
            });

            this.map.once('idle', async () => {
                if(newVal) {
                    this.map.getSource('cities-active').setData({
                        type: 'FeatureCollection',
                        features: this.geoJson.features.filter(feature => feature.properties?.city?.id === newVal.id),
                    });
                }
            });
        },

        activeRegion (newVal) {
            this.map.getSource('regions-active').setData({
                type: 'FeatureCollection',
                features: [],
            });

            this.map.once('idle', async () => {
                if(newVal) {
                    this.map.getSource('regions-active').setData({
                        type: 'FeatureCollection',
                        features: this.geoJson.features.filter(feature => feature.properties?.regions?.find(region => region.id === newVal.id)),
                    });
                }
            });
        },

    },

};
</script>