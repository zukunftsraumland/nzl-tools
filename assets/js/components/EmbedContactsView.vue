<template>

    <div class="embed-contacts-view" :class="$env.INSTANCE_ID+'-contacts-view'">

        <template v-if="!contact.officialEmployment || officialEmployment?.id">

            <div class="embed-contacts-view-header">

                <h1 class="embed-contacts-view-header-title">{{ contact.academicTitle }} {{ translateField(contact, 'name', locale) }}</h1>
                <p v-if="contact.type === 'person' && officialEmployment?.role"><i>{{ translateField(officialEmployment, 'role', locale) }}</i></p>
                <p v-if="contact.type === 'company' && contact.specification"><i>{{ translateField(contact, 'specification', locale) }}</i></p>

                <template v-if="contact.type === 'person'&& officialEmployment?.id && officialEmployment?.company?.id">

                    <p>
                        <template v-if="contact.phone">
                            {{ $t('Tel.', locale) }}: <a :href="'tel:'+contact.phone">{{ contact.phone }}</a><br>
                        </template>
                        <template v-if="contact.email">
                            {{ $t('Mail', locale) }}: <a :href="'mailto:'+contact.email">{{ contact.email }}</a><br>
                        </template>
                    </p>

                </template>

                <a class="embed-contacts-view-header-close" @click="clickClose()"></a>

            </div>

            <div class="embed-contacts-view-content">

                <div class="embed-contacts-view-content-text" v-if="contact.description">
                    <div class="embed-contacts-view-content-description" v-html="translateField(contact, 'description', locale)"></div><br>
                </div>

                <div class="embed-contacts-view-content-text">

                    <template v-if="contact.type === 'person'&& officialEmployment?.id && officialEmployment?.company?.id">

                        <p>
                            <strong>{{ translateField(officialEmployment.company, 'companyName', locale) }}</strong><br>
                            <template v-if="officialEmployment.company.specification">
                                {{ translateField(officialEmployment.company, 'specification', locale) }}<br>
                            </template>
                            <br>
                            <template v-if="officialEmployment.company.street">
                                {{ officialEmployment.company.street }}<br>
                            </template>
                            <template v-if="officialEmployment.company.city || officialEmployment.company.state">
                                {{ officialEmployment.company.zipCode }} {{ translateField(officialEmployment.company, 'city', locale) }} {{ officialEmployment.company.state ? '('+translateField(getStateById(officialEmployment.company.state.id), 'name', locale)+')' : '' }}<br>
                            </template>
                            <template v-if="officialEmployment.company.country">
                                {{ translateField(officialEmployment.company.country, 'name', locale) || '' }}<br>
                            </template>
                        </p>
                        <p>
                            <template v-if="officialEmployment.company.phone">
                                {{ $t('Tel.', locale) }}: <a :href="'tel:'+officialEmployment.company.phone">{{ officialEmployment.company.phone }}</a><br>
                            </template>
                            <template v-if="officialEmployment.company.email">
                                {{ $t('Mail', locale) }}: <a :href="'tel:'+officialEmployment.company.email">{{ officialEmployment.company.email }}</a><br>
                            </template>
                            <template v-if="officialEmployment.company.website">
                                {{ $t('Website', locale) }}: <a target="_blank" :href="translateField(officialEmployment.company, 'website', locale)">{{ translateField(officialEmployment.company, 'website', locale) }}</a>
                            </template>
                        </p>
                    </template>

                    <template v-else>
                        <p v-if="contact.street || contact.zipCode || contact.city">
                            <template v-if="contact.street">
                                {{ contact.street }}<br>
                            </template>
                            <template v-if="contact.street || contact.state">
                                {{ contact.zipCode }} {{  translateField(contact, 'city', locale) }} {{ contact.state ? '('+translateField(contact.state, 'name', locale)+')' : '' }}<br>
                            </template>
                            <template v-if="contact.country">
                                {{  translateField(contact.country, 'name', locale) }}<br>
                            </template>
                        </p>
                        <p>
                            <template v-if="contact.phone">
                                {{ $t('Tel.', locale) }}: <a :href="'tel:'+contact.phone">{{ contact.phone }}</a><br>
                            </template>
                            <template v-if="contact.email">
                                {{ $t('Mail', locale) }}: <a :href="'mailto:'+contact.email">{{ contact.email }}</a><br>
                            </template>
                            <template v-if="contact.website">
                                {{ $t('Website', locale) }}: <a target="_blank" :href="translateField(contact, 'website', locale)">{{ translateField(contact, 'website', locale) }}</a>
                            </template>
                        </p>
                    </template>

                </div>

            </div>

            <div class="embed-contacts-view-sidebar"></div>

        </template>

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
        contact: {
            type: Object,
            required: true,
        },
        officialEmployment: {
            type: Object,
            required: false,
            default: null,
        },
    },

    emits: [
        'clickClose',
    ],

    computed: {
        ...mapState({
            states: state => state.states.all,
            employments: state => state.employments.all,
        }),
        ...mapGetters({
            getStateById: 'states/getById',
            getContactById: 'contacts/getById',
            getEmploymentById: 'employments/getById',
            getLanguageById: 'languages/getById',
        }),
        contactHTML () {

            let result = translateField(this.contact, 'contact', this.locale).split('\n');

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

            translateField(this.contact, 'links', this.locale).forEach((item) => {

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
    },

};

</script>