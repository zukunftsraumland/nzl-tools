<template>

    <div class="contacts-component">

        <div class="contacts-component-title">

            <h2>Kontakte</h2>

            <transition name="fade" mode="out-in">
                <div class="loading-indicator" v-if="isLoading('contacts')"></div>
            </transition>

            <!--<div class="contacts-component-title-actions">
                <router-link :to="'/contacts/add'" class="button primary">Neuen Eintrag erstellen</router-link>
            </div>-->

        </div>

        <div class="contacts-component-filter">

            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="term">Suchbegriff</label>
                        <input id="term" type="text" class="form-control" v-model="term" @change="changeForm()">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="type">Typ</label>
                        <div class="select-wrapper">
                            <select id="type" class="form-control" @change="addFilter({type: 'type', value: $event.target.value}); $event.target.value = null;">
                                <option></option>
                                <option :value="'company'">Unternehmen</option>
                                <option :value="'person'">Person</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="contacts-component-filter-tags">
                <div class="tag" v-for="filter of filters" @click="removeFilter({type: filter.type, value: filter.value})">
                    <strong v-if="filter.type === 'type'">Typ:</strong>
                    {{filter.value}}
                </div>
            </div>

        </div>

        <div class="contacts-component-content">

            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Typ</th>
                        <th>Name</th>
                        <th>E-Mail</th>
                        <th>Telefon</th>
                        <th>Kontaktgruppe</th>
                        <!--<th>Erstellt am</th>
                        <th>Aktualisiert am</th>-->
                    </tr>
                </thead>
                <tbody v-if="!contacts.length && isLoading('contacts')">
                    <tr>
                        <td colspan="11"><em>Einträge werden geladen...</em></td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr v-for="contact in contacts"
                        :class="{'warning': !contact.isPublic}"
                        @click="clickContact(contact)">
                        <td>{{ contact.id }}</td>
                        <td v-if="contact.type === 'person'">Person</td>
                        <td v-else>Unternehmen</td>
                        <td v-if="contact.type === 'person'">{{ translateField(contact, 'firstName', 'de') }} {{ translateField(contact, 'lastName', 'de') }}</td>
                        <td v-else>{{ translateField(contact, 'companyName', 'de') }}</td>
                        <td>{{ translateField(contact, 'email', 'de') }}</td>
                        <td>{{ translateField(contact, 'phone', 'de') }}</td>
                        <td>
                            <template v-for="contactGroup in contact.contactGroups.map(contactGroup => contactGroups.find(cg => cg.id === contactGroup.id))">
                                {{ contactGroup.name }}<br>
                            </template>
                        </td>
                        <!--<td>{{ $helpers.formatDate(contact.createdAt) }}</td>
                        <td>{{ $helpers.formatDate(contact.updatedAt) }}</td>-->
                    </tr>
                </tbody>
            </table>

            <br><a @click="clickLoadMore()" class="button" v-if="!isLoadedFully">Mehr Kontakte laden</a>

        </div>

    </div>

</template>

<script>
import { mapState, mapGetters } from 'vuex';
import moment from 'moment';
import { translateField } from '../utils/filters';

export default {
    data () {
        return {
            contacts: [],
            term: '',
            filters: [],
            limit: 100,
            offset: 0,
            isLoadedFully: false,
        };
    },
    computed: {
        ...mapState({
            contactGroups: state => state.contactGroups.all,
        }),
        ...mapGetters({
            isLoading: 'loaders/isLoading',
            getContactById: 'contacts/getById',
        }),
    },
    methods: {
        changeForm () {
            this.saveFilter();
            this.reloadContacts();
        },
        getFilterParams () {
            let params = {};
            params.term = this.term;

            this.filters.forEach((filter) => {
                if(!params[filter.type]) {
                    params[filter.type] = [];
                }
                params[filter.type].push(filter.value);
            });

            params.limit = this.limit;
            params.offset = this.offset;
            params.orderBy = ['id'];
            params.orderDirection = ['DESC'];

            return params;
        },
        reloadContacts () {
            this.isLoadedFully = false;
            this.offset = 0;
            return this.$store.dispatch('contacts/loadFiltered', this.getFilterParams()).then((contacts) => {
                this.contacts = [
                    ...contacts,
                ];
            });
        },
        clickLoadMore () {
            this.offset += this.limit;
            this.$store.dispatch('contacts/loadFiltered', this.getFilterParams()).then((contacts) => {
                if(!contacts.length) {
                    this.isLoadedFully = true;
                }
                this.contacts = [
                    ...this.contacts,
                    ...contacts,
                ];
            });
        },
        clickContact (contact) {
            return;
            this.$router.push({
                path: '/contacts/'+contact.id+'/edit'
            });
        },
        formatDate(date, format = 'DD.MM.YYYY') {
            if(date && moment(date)) {
                return moment(date).format(format);
            }
        },
        formatDateTime(date) {
            return this.formatDate(date, 'DD.MM.YYYY HH:mm');
        },
        addFilter (filter) {
            if(!filter.value) {
                return;
            }
            if(this.filters.filter(f => f.type === filter.type).find(f => f.value === filter.value)) {
                return;
            }
            this.filters.push(filter);
            this.changeForm();
        },
        removeFilter (filter) {
            let f = this.filters.filter(f => f.type === filter.type).find(f => f.value === filter.value);
            if(f) {
                this.filters.splice(this.filters.indexOf(f), 1);
            }
            this.changeForm();
        },
        saveFilter () {
            window.sessionStorage.setItem('regiosuisse.contacts.filters', JSON.stringify(this.filters));
            window.sessionStorage.setItem('regiosuisse.contacts.term', this.term);
        },
        loadFilter () {
            this.filters = JSON.parse(window.sessionStorage.getItem('regiosuisse.contacts.filters') || '[]');
            this.term = window.sessionStorage.getItem('regiosuisse.contacts.term') || '';
        },
        translateField,
    },
    created () {
        this.loadFilter();
        this.reloadContacts();
    },
}
</script>