<template>
    <div class="embed-regions-content-map-container" :id="'random-'+random" ref="mapContainer"></div>
    <div class="embed-regions-content-map-container-popup" v-if="hoverCityId">
        <strong>Gemeinde:</strong> {{ cities.find(city => city.id === hoverCityId).name }}<br>
        <template v-if="hoverRegionId">
            <strong>Region:</strong> {{ typeRegions.find(region => region.id === hoverRegionId).name }}<br>
        </template>
    </div>
</template>

<script>
import mapboxgl from 'mapbox-gl/dist/mapbox-gl';
import chroma from 'chroma-js';

export default {

    data() {
        return {
            random: Math.random().toString(16),
            map: null,
            hoverCityId: null,
            hoverRegionId: null,
            activeCityId: null,
            activeRegionId: null,
            cityFeatures: [],
        };
    },

    props: {
        cities: {
            type: Array,
            required: true,
        },
        regions: {
            type: Array,
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

    created() {
        mapboxgl.accessToken = this.$env.MAPBOX_API_TOKEN;
    },

    mounted() {

        this.map = new mapboxgl.Map({
            container: this.$refs.mapContainer.id,
            style: 'mapbox://styles/mapbox/empty-v9',
            center: [
                8.24245129900188,
                46.94058782182699
            ],
            zoom: 6,
        });

        this.map.on('load', () => {

            this.map.addSource('cities', {
                type: 'geojson',
                data: '/api/v1/regions/geojson/'+this.regionType+'/cities/de.json',
                generateId: true,
            });

            this.map.addLayer({
                id: 'cities',
                type: 'fill',
                source: 'cities',
                paint: {
                    'fill-color': '#F3F3F3',
                },
            });

            this.map.addLayer({
                id: 'regions',
                type: 'fill',
                source: 'cities',
                paint: {
                    'fill-color': [
                        'to-color',
                        ['feature-state', 'regionColor'],
                        'transparent',
                    ],
                },
            });

            this.map.addLayer({
                id: 'regions-active',
                type: 'fill',
                source: 'cities',
                paint: {
                    'fill-color': [
                        'case',
                        ['boolean', ['feature-state', 'regionActive'], false],
                        '#d2d2d2',
                        'transparent',
                    ],
                },
            });

            this.map.addLayer({
                id: 'regions-hover',
                type: 'fill',
                source: 'cities',
                paint: {
                    'fill-color': [
                        'case',
                        ['boolean', ['feature-state', 'regionHover'], false],
                        '#B4BE00',
                        'transparent',
                    ],
                },
            });

            this.map.addLayer({
                id: 'cities-active',
                type: 'line',
                source: 'cities',
                paint: {
                    'line-width': 3,
                    'line-color': [
                        'case',
                        ['boolean', ['feature-state', 'cityActive'], false],
                        '#000000',
                        'transparent',
                    ],
                },
            });

            this.map.addLayer({
                id: 'cities-hover',
                type: 'line',
                source: 'cities',
                paint: {
                    'line-width': 2,
                    'line-color': [
                        'case',
                        ['boolean', ['feature-state', 'cityHover'], false],
                        '#000000',
                        'transparent',
                    ],
                },
            });

            this.map.on('mousemove', (event) => {

                this.map.getCanvasContainer().style.cursor = null;

                let features = this.map.queryRenderedFeatures(event.point, {
                    layers: ['cities'],
                });

                let feature = features?.find(feature => feature.source === 'cities');

                if(!feature && this.hoverCityId) {
                    this.clearCityHoverState(this.hoverCityId);
                    this.hoverCityId = null;
                }

                if(!feature && this.hoverRegionId) {
                    this.clearRegionHoverState(this.hoverRegionId);
                    this.hoverRegionId = null;
                }

                if(!feature) {
                    return;
                }

                let cityId = this.featureCityMapping[feature.id]?.id;

                if(!cityId && this.hoverCityId) {
                    this.clearCityHoverState(this.hoverCityId);
                    this.hoverCityId = null;
                }

                if(!cityId && this.hoverRegionId) {
                    this.clearRegionHoverState(this.hoverRegionId);
                    this.hoverRegionId = null;
                }

                if(this.hoverCityId && this.hoverCityId !== cityId) {
                    this.clearCityHoverState(this.hoverCityId);
                    this.hoverCityId = null;
                }

                let regionId = this.cityRegionMapping[cityId]?.id;

                if(!regionId && this.hoverRegionId) {
                    this.clearRegionHoverState(this.hoverRegionId);
                    this.hoverRegionId = null;
                }

                if(this.hoverRegionId && this.hoverRegionId !== regionId) {
                    this.clearRegionHoverState(this.hoverRegionId);
                    this.hoverRegionId = null;
                }

                if(cityId && this.hoverCityId !== cityId) {
                    this.setCityHoverState(cityId);
                    this.hoverCityId = cityId;
                }

                if(regionId && this.hoverRegionId !== regionId) {
                    this.setRegionHoverState(regionId);
                    this.hoverRegionId = regionId;
                }

                this.map.getCanvasContainer().style.cursor = 'pointer';

            });

            this.map.on('click', 'cities', (event) => {

                let features = this.map.queryRenderedFeatures(event.point, {
                    layers: ['cities'],
                });

                let feature = features?.find(feature => feature.source === 'cities');

                if(!feature) {
                    return;
                }

                let city = this.featureCityMapping[feature.id];

                if(!city) {
                    return;
                }

                this.$emit('clickCity', city);

                let region = this.cityRegionMapping[city.id];

                if(!region) {
                    return;
                }

                this.$emit('clickRegion', region);

            });

            this.map.once('idle', () => {

                let bounds = new mapboxgl.LngLatBounds();

                let features = this.map.querySourceFeatures('cities');

                features
                    .forEach(feature => feature.geometry.coordinates
                        .forEach(coordinates => coordinates
                            .forEach(coordinate => coordinate?.length === 2 ? bounds.extend(coordinate) : null)
                        )
                    );

                this.map.fitBounds(bounds, {
                    padding: 20,
                });

                this.updateFeatures();

                if(this.activeCity) {
                    this.setCityActiveState(this.activeCity.id);
                }

            });

            /*this.updateRegionsLayer();*/

            /*this.map.on('mousemove', 'regions', (e) => {
                //console.log(this.map.getLayer('regions').features);
                console.log(this.map.getSource(this.map.getLayer('regions').source));
            });*/
        });

    },

    beforeUnmount() {
        this.map.remove();
    },

    computed: {

        typeRegions () {

            const colorScale = chroma.scale(['#8e9867', '#616721']);

            return this.regions
                .filter(region => region.type === this.regionType)
                .map((region) => {
                    return {
                        ...region,
                        color: region.color || colorScale(Math.random()).hex(),
                    };
                });

        },

        regionCityMapping () {

            let groups = {};

            for(let city of this.cities) {

                let regionId = this.typeRegions.find(region => region.cities.find(c => c.id === city.id))?.id;

                if(!regionId) {
                    continue;
                }

                if(!groups[regionId]) {
                    groups[regionId] = [];
                }

                groups[regionId].push(city);

            }

            return groups;

        },

        cityRegionMapping () {

            let mapping = {};

            for(let city of this.cities) {
                mapping[city.id] = this.typeRegions.find(region => region.cities.find(c => c.id === city.id));
            }

            return mapping;

        },

        cityFeatureMapping () {

            let mapping = {};

            for(let feature of this.cityFeatures) {

                let city = this.cities.find(city => city.municipalNumber === feature.properties.GMDNR);

                if(!city) {
                    continue;
                }

                mapping[city.id] = feature;

            }

            return mapping;

        },

        featureCityMapping () {

            let mapping = {};

            for(let feature of this.cityFeatures) {

                let city = this.cities.find(city => city.municipalNumber === feature.properties.GMDNR);

                mapping[feature.id] = city;

            }

            return mapping;

        },

    },

    methods: {

        async updateFeatures() {

            let features = this.map.querySourceFeatures('cities');

            for(let feature of features) {

                let region = this.typeRegions
                    .find(region => region.id === feature.properties.regionId);

                console.log(feature.properties);

                this.map.setFeatureState(
                    {
                        source: 'cities',
                        id: feature.id,
                    },
                    {
                        regionId: region?.id,
                        regionColor: region?.color,
                        //regionHover: false,
                        //regionActive: false,
                        cityId: feature.properties.cityId,
                        //cityHover: false,
                        //cityActive: false,
                    },
                );

            }

            this.hoverCityId = null;
            this.hoverRegionId = null;
            this.cityFeatures = [
                ...features,
            ];

        },

        setCityHoverState(cityId) {

            if(!this.cityFeatureMapping[cityId]?.id) {
                return;
            }

            this.map.setFeatureState(
                {
                    source: 'cities',
                    id: this.cityFeatureMapping[cityId].id,
                },
                {
                    cityHover: true,
                },
            );

        },

        clearCityHoverState(cityId) {

            if(!this.cityFeatureMapping[cityId]?.id) {
                return;
            }

            this.map.setFeatureState(
                {
                    source: 'cities',
                    id: this.cityFeatureMapping[cityId].id,
                },
                {
                    cityHover: false,
                },
            );

        },

        setCityActiveState(cityId) {

            if(!this.cityFeatureMapping[cityId]?.id) {
                return;
            }

            this.map.setFeatureState(
                {
                    source: 'cities',
                    id: this.cityFeatureMapping[cityId].id,
                },
                {
                    cityActive: true,
                },
            );

        },

        clearCityActiveState(cityId) {

            if(!this.cityFeatureMapping[cityId]?.id) {
                return;
            }

            this.map.setFeatureState(
                {
                    source: 'cities',
                    id: this.cityFeatureMapping[cityId].id,
                },
                {
                    cityActive: false,
                },
            );

        },

        setRegionHoverState(regionId) {

            let cities = this.regionCityMapping[regionId];

            if(!cities) {
                return;
            }

            for(let city of cities) {

                if(!this.cityFeatureMapping[city.id]?.id) {
                    continue;
                }

                this.map.setFeatureState(
                    {
                        source: 'cities',
                        id: this.cityFeatureMapping[city.id].id,
                    },
                    {
                        regionHover: true,
                    },
                );

            }

        },

        clearRegionHoverState(regionId) {

            let cities = this.regionCityMapping[regionId];

            if(!cities) {
                return;
            }

            for(let city of cities) {

                if(!this.cityFeatureMapping[city.id]?.id) {
                    continue;
                }

                this.map.setFeatureState(
                    {
                        source: 'cities',
                        id: this.cityFeatureMapping[city.id].id,
                    },
                    {
                        regionHover: false,
                    },
                );

            }

        },

    },

    watch: {

        regionType () {
            this.updateFeatures();
        },

        activeCity (newVal) {
            if(this.activeCityId) {
                this.clearCityActiveState(this.activeCityId);
            }
            if(newVal) {
                this.activeCityId = newVal.id;
                this.setCityActiveState(newVal.id);
            }
        },

        activeRegion () {
            //this.updateFeatures();
        },

    },

};
</script>