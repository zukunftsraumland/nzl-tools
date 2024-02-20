<template>

    <div class="embed-events-view" :class="$env.INSTANCE_ID+'-events-view'">

        <div class="embed-events-view-header">

            <h1 class="embed-events-view-header-title">{{ translateField(event, 'title', locale) }}</h1>

            <h2 class="embed-events-view-header-subtitle" v-if="event.startDate && event.endDate">
                {{ $helpers.formatDateRange(event.startDate, event.endDate) }}
            </h2>

            <a class="embed-events-view-header-close" @click="clickClose()"></a>

        </div>

        <div class="embed-events-view-content">

            <div class="embed-events-view-content-text" v-html="translateField(event, 'text', locale)"></div>

            <div class="embed-events-view-content-gallery" v-if="(translateField(event, 'images', locale) || []).length > 1">

                <div class="embed-events-view-content-gallery-image" v-for="image in (translateField(event, 'images', locale) || []).slice(1)">
                    <a @click="clickShowImage(image)">
                        <img :src="$env.HOST+'/api/v1/files/view/'+ image.id +'.' + image.extension" alt="">
                    </a>
                </div>

            </div>

        </div>

        <div class="embed-events-view-sidebar">

            <div class="embed-events-view-sidebar-image" v-for="image in (translateField(event, 'images', locale) || []).slice(0, 1)">
                <a @click="clickShowImage(image)">
                    <img :src="$env.HOST+'/api/v1/files/view/'+ image.id +'.' + image.extension" alt="">
                </a>
            </div>

            <template v-if="event.location">
                <h3>{{ $t('Terminort', locale) }}</h3>
                <p>{{ translateField(event, 'location', locale) }}</p>
            </template>

            <template v-if="event.organizer">
                <h3>{{ $t('Veranstalter', locale) }}</h3>
                <p>{{ translateField(event, 'organizer', locale) }}</p>
            </template>

            <template v-if="contactHTML">
                <h3>{{ $t('Kontakt', locale) }}</h3>
                <p v-html="contactHTML"></p>
            </template>

            <template v-if="languagesHTML">
                <h3>{{ $t('Durchführungssprache', locale) }}</h3>
                <p v-html="languagesHTML"></p>
            </template>

            <template v-if="linksHTML">
                <h3>{{ $t('Links', locale) }}</h3>
                <p v-html="linksHTML"></p>
            </template>

            <template v-if="translateField(event, 'files', locale)?.length">
                <h3>{{ $t('Downloads', locale) }}</h3>
                <p>
                    <template v-for="(file, index) in translateField(event, 'files', locale)">
                        <a :href="$env.HOST+'/api/v1/files/download/'+file.id+'.'+file.extension" download>
                            {{ file.name || 'Datei '+(index+1) }}
                        </a><br>
                    </template>
                </p>
            </template>

            <div class="embed-events-view-sidebar-actions">

                <p v-if="translateField(event, 'registration', locale)">
                    <a :href="'mailto:'+event.registration" class="embed-events-view-button" v-if="/\S+@\S+\.\S+/.test(translateField(event, 'registration', locale))">{{ $t('Zur Anmeldung', locale) }}</a>
                    <a :href="translateField(event, 'registration', locale)" target="_blank" class="embed-events-view-button" v-else>{{ $t('Zur Anmeldung', locale) }}</a>
                </p>

                <p>
                    <a :href="$env.HOST+'/api/v1/events/'+locale+'/'+event.id+'.ics'" class="embed-events-view-button" download>{{ $t('In meine Agenda eintragen', locale) }}</a>
                </p>

            </div>

        </div>

        <transition name="embed-events-view-lightbox" mode="out-in">
        
            <div class="embed-events-view-lightbox" v-if="lightboxImage" @click="clickHideImage()">

                <div class="embed-events-view-lightbox-content"
                     :style="{backgroundImage: 'url('+$env.HOST+'/api/v1/files/view/'+ lightboxImage.id +'.' + lightboxImage.extension+')'}">
                </div>

                <div class="embed-events-view-lightbox-description" @click.stop v-if="lightboxImage.description || lightboxImage.copyright">
                    <template v-if="lightboxImage.description">{{ lightboxImage.description }}</template>
                    <template v-if="lightboxImage.description && lightboxImage.copyright"> | </template>
                    <template v-if="lightboxImage.copyright">© {{ lightboxImage.copyright }}</template>
                </div>

                <a class="embed-events-view-lightbox-prev" @click.stop="clickPrevImage()">
                    <span class="embed-events-view-lightbox-prev-icon"></span>
                </a>

                <a class="embed-events-view-lightbox-next" @click.stop="clickNextImage()">
                    <span class="embed-events-view-lightbox-next-icon"></span>
                </a>

            </div>

        </transition>

    </div>

</template>

<script>

import {mapGetters, mapState} from 'vuex';
import { translateField } from '../utils/filters';

export default {

    data() {
        return {
            lightboxImage: null,
        };
    },

    props: {
        locale: {
            type: String,
            default: 'de',
            required: false,
        },
        event: {
            type: Object,
            required: true,
        },
    },

    emits: [
        'clickClose',
    ],

    computed: {
        ...mapState({
            topics: state => state.topics.all,
            languages: state => state.languages.all,
            locations: state => state.locations.all,
        }),
        ...mapGetters({
            getTopicById: 'topics/getById',
            getLanguageById: 'languages/getById',
            getLocationById: 'locations/getById',
        }),
        languagesHTML () {
            let result = [];

            this.event.languages.forEach((item) => {

                let row = this.translateField(this.getLanguageById(item.id), 'name', this.locale);

                result.push(row);

            });

            return result.join(', ');
        },
        contactHTML () {

            let result = translateField(this.event, 'contact', this.locale).split('\n');

            for(let contactRowKey in result) {
                result[contactRowKey] = result[contactRowKey]
                    .replace(/([a-z0-9._-]+@[a-z0-9.-]+\.[a-z]{2,4})/ig, '<a href="mailto:$1">$1</a>');
            }

            for(let contactRowKey in result) {
                result[contactRowKey] = result[contactRowKey]
                    .replace(/((http:|https:)[^\s]+[\w])/g, '<a href="$1" target="_blank">Website</a>');
            }

            return result.join('<br>');
        },
        linksHTML () {
            let result = [];

            translateField(this.event, 'links', this.locale).forEach((item) => {

                let url = item.value.split('://').length > 1 ? item.value : 'http://'+item.url;

                let row = '<a href="'+url+'" target="_blank">'+item.label+'</a>';

                result.push(row);

            });

            return result.join('<br>');
        },
    },

    methods: {

        translateField,

        clickClose() {
            this.$emit('clickClose');
        },

        clickShowImage(image) {
            this.lightboxImage = image;
        },

        clickHideImage() {
            this.lightboxImage = null;
        },

        clickPrevImage() {
            let images = this.translateField(this.event, 'images', this.locale);
            let index = images.findIndex(i => i.id === this.lightboxImage.id);

            this.lightboxImage = images[index-1] || images[images.length-1];
        },

        clickNextImage() {
            let images = this.translateField(this.event, 'images', this.locale);
            let index = images.findIndex(i => i.id === this.lightboxImage.id);

            this.lightboxImage = images[index+1] || images[0];
        },

    },

    created() {

    },

};

</script>