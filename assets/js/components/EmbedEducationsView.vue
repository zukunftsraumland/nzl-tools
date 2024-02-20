<template>

    <div class="embed-educations-view" :class="$env.INSTANCE_ID+'-educations-view'">

        <div class="embed-educations-view-header">

            <h1 class="embed-educations-view-header-title">{{ translateField(education, 'name', locale) }}</h1>

            <a class="embed-educations-view-header-close" @click="clickClose()"></a>

        </div>

        <div class="embed-educations-view-content">

            <div class="embed-educations-view-content-text" v-html="translateField(education, 'text', locale)"></div>

            <div class="embed-educations-view-content-gallery" v-if="(translateField(education, 'images', locale) || []).length > 1">

                <div class="embed-educations-view-content-gallery-image" v-for="image in (translateField(education, 'images', locale) || []).slice(1)">
                    <a @click="clickShowImage(image)">
                        <img :src="$env.HOST+'/api/v1/files/view/'+ image.id +'.' + image.extension" alt="">
                    </a>
                </div>

            </div>

        </div>

        <div class="embed-educations-view-sidebar">

            <div class="embed-educations-view-sidebar-image" v-for="image in (translateField(education, 'images', locale) || []).slice(0, 1)">
                <a @click="clickShowImage(image)">
                    <img :src="$env.HOST+'/api/v1/files/view/'+ image.id +'.' + image.extension" alt="">
                </a>
            </div>

            <template v-if="education.location">
                <h3>{{ $t('Terminort', locale) }}</h3>
                <p>{{ translateField(education, 'location', locale) }}</p>
            </template>

            <template v-if="education.organizer">
                <h3>{{ $t('Veranstalter', locale) }}</h3>
                <p>{{ translateField(education, 'organizer', locale) }}</p>
            </template>

            <template v-if="contactHTML">
                <h3>{{ $t('Kontakt', locale) }}</h3>
                <p v-html="contactHTML"></p>
            </template>

            <template v-if="linksHTML">
                <h3>{{ $t('Links', locale) }}</h3>
                <p v-html="linksHTML"></p>
            </template>

            <template class="embed-educations-view-content-downloads" v-if="translateField(education, 'files', locale)?.length">

                <h4>{{ $t('Downloads', locale) }}</h4>

                <div class="embed-educations-view-content-downloads-download" v-for="(file, index) in translateField(education, 'files', locale)">

                    <a :href="$env.HOST+'/api/v1/files/download/'+file.id+'.'+file.extension" download>
                        {{ file.name || 'Datei '+(index+1) }}
                    </a>

                </div>

            </template>

        </div>

        <transition name="embed-educations-view-lightbox" mode="out-in">
        
            <div class="embed-educations-view-lightbox" v-if="lightboxImage" @click="clickHideImage()">

                <div class="embed-educations-view-lightbox-content"
                     :style="{backgroundImage: 'url('+$env.HOST+'/api/v1/files/view/'+ lightboxImage.id +'.' + lightboxImage.extension+')'}">
                </div>

                <div class="embed-educations-view-lightbox-description" @click.stop v-if="lightboxImage.description || lightboxImage.copyright">
                    <template v-if="lightboxImage.description">{{ lightboxImage.description }}</template>
                    <template v-if="lightboxImage.description && lightboxImage.copyright"> | </template>
                    <template v-if="lightboxImage.copyright">© {{ lightboxImage.copyright }}</template>
                </div>

                <a class="embed-educations-view-lightbox-prev" @click.stop="clickPrevImage()">
                    <span class="embed-educations-view-lightbox-prev-icon"></span>
                </a>

                <a class="embed-educations-view-lightbox-next" @click.stop="clickNextImage()">
                    <span class="embed-educations-view-lightbox-next-icon"></span>
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
        education: {
            type: Object,
            required: true,
        },
    },

    emits: [
        'clickClose',
    ],

    computed: {
        ...mapState({
            educationTypes: state => state.educationTypes.all,
            languages: state => state.languages.all,
            locations: state => state.locations.all,
        }),
        ...mapGetters({
            getEducationTypeById: 'educationTypes/getById',
            getLanguageById: 'languages/getById',
            getLocationById: 'locations/getById',
        }),
        contactHTML () {

            let result = translateField(this.education, 'contact', this.locale).split('\n');

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

            translateField(this.education, 'links', this.locale).forEach((item) => {

                let url = item.value.split('://').length > 1 ? item.value : 'http://'+item.value;

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
            let images = this.translateField(this.education, 'images', this.locale);
            let index = images.findIndex(i => i.id === this.lightboxImage.id);

            this.lightboxImage = images[index-1] || images[images.length-1];
        },

        clickNextImage() {
            let images = this.translateField(this.education, 'images', this.locale);
            let index = images.findIndex(i => i.id === this.lightboxImage.id);

            this.lightboxImage = images[index+1] || images[0];
        },

    },

    created() {

    },

};

</script>