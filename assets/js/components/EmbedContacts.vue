<template>

    <div class="embed-contacts" :class="[$env.INSTANCE_ID+'-contacts', {'is-responsive': responsive}]" @click.stop="clickInside">

        <div class="embed-contacts-search">

            <div class="embed-contacts-search-input">
                <input type="text" :placeholder="$t('Suchbegriff', locale)" v-model="term"
                       :class="{'has-value': term}"
                       @change="changeSearchTerm()"
                       @keyup="$event.keyCode === 13 ? changeSearchTerm() : null">
                <div class="embed-contacts-search-input-icon" @click.stop="term = null; changeSearchTerm()"></div>
            </div>

        </div>

        <div class="embed-contacts-filters">

            <div class="embed-contacts-filters-select" data-filter-type="contactGroupsParents">

                <div class="embed-contacts-filters-select-label"
                     @click.stop="clickFilterSelect('contactGroupsParent')">{{ $t('Akteursebene', locale) }}</div>

                <div class="embed-contacts-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'contactGroupsParent'}"></div>

                <transition name="embed-contacts-filters-select-options" mode="out-in">

                    <div class="embed-contacts-filters-select-options" v-if="activeFilterSelect === 'contactGroupsParent'">

                        <div class="embed-contacts-filters-select-options-item"
                             v-for="contactGroup in contactGroupsParents"
                             :class="{ 'is-selected': isFilterSelected({ type: 'contactGroupsParent', entity: contactGroup }) }"
                             @click.stop="clickToggleFilter({ type: 'contactGroupsParent', entity: contactGroup })">
                            {{ translateField(contactGroup, 'name', locale) }}
                        </div>

                    </div>

                </transition>

            </div>

            <transition name="fade">

                <div class="embed-contacts-filters-select" data-filter-type="contactGroups" v-if="filters.find(e => e.type === 'contactGroupsParent')">

                    <div class="embed-contacts-filters-select-label"
                         @click.stop="clickFilterSelect('contactGroup')">{{ $t('Kontaktgruppe', locale) }}</div>

                    <div class="embed-contacts-filters-select-icon"
                         :class="{'is-active': activeFilterSelect === 'contactGroup'}"></div>

                    <transition name="embed-contacts-filters-select-options" mode="out-in">

                        <div class="embed-contacts-filters-select-options" v-if="activeFilterSelect === 'contactGroup'">

                            <div class="embed-contacts-filters-select-options-item"
                                 v-for="contactSubGroup in contactSubGroups"
                                 :class="{ 'is-selected': isFilterSelected({ type: 'contactGroup', entity: contactSubGroup }) }"
                                 @click.stop="clickToggleFilter({ type: 'contactGroup', entity: contactSubGroup })">
                                {{ translateField(contactSubGroup, 'name', locale) }}
                            </div>

                        </div>

                    </transition>

                </div>

            </transition>

            <div class="embed-contacts-filters-select" data-filter-type="states">

                <div class="embed-contacts-filters-select-label"
                     @click.stop="clickFilterSelect('state')">{{ $t('Kanton', locale) }}</div>

                <div class="embed-contacts-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'state'}"></div>

                <transition name="embed-contacts-filters-select-options" mode="out-in">

                    <div class="embed-contacts-filters-select-options" v-if="activeFilterSelect === 'state'">

                        <div class="embed-contacts-filters-select-options-item"
                             v-for="state in states"
                             :class="{ 'is-selected': isFilterSelected({ type: 'state', entity: state }) }"
                             @click.stop="clickToggleFilter({ type: 'state', entity: state })">
                            {{ translateField(state, 'name', locale) }}
                        </div>

                    </div>

                </transition>

            </div>

            <div class="embed-contacts-filters-list">

                <div class="embed-contacts-filters-list-item"
                     v-for="filter in filters"
                     @click.stop="clickToggleFilter(filter)">{{ translateField(filter.entity, 'name', locale) }}</div>

            </div>

        </div>

        <transition name="embed-contacts-list" mode="out-in">

            <div class="embed-contacts-list" v-if="!isLoading">

                <div class="embed-contacts-list-item"
                     v-for="contact in contacts" :id="'contact-'+contact.id"
                     :class="{'is-draft': contact.isPublic !== true}"
                     @click.stop="clickShowContact(contact)">

                    <div class="embed-contacts-list-item-content" :class="{ invisible: isLoadingMore }">

                        <h1 class="embed-contacts-list-item-content-title">
                            {{ contact.academicTitle }} {{ translateField(contact, 'name', locale) }}
                            <template v-if="contact.type === 'person' && contact.officialEmployment?.id && getOfficialEmployment(contact)?.company">
                                <br><span class="embed-contacts-list-item-content-subtitle">{{ translateField(getOfficialEmployment(contact)?.company, 'companyName', locale) }}</span>
                            </template>
                        </h1>

                        <div class="embed-contacts-list-item-content-description">

                            <template v-if="contact.type === 'person' && getOfficialEmployment(contact)?.id && getOfficialEmployment(contact)?.company.id">
                                <p v-if="getOfficialEmployment(contact).role"><i>{{ translateField(getOfficialEmployment(contact), 'role', locale) }}</i></p>
                                <p>
                                    <template v-if="getOfficialEmployment(contact).company.street">
                                        {{ getOfficialEmployment(contact).company.street }}<br>
                                    </template>
                                    <template v-if="getOfficialEmployment(contact).company.city">
                                        {{ getOfficialEmployment(contact).company.zipCode }} {{ translateField(getOfficialEmployment(contact).company, 'city', locale) }}
                                    </template>
                                </p>
                            </template>

                            <template v-else>
                                <p v-if="contact.specification"><i>{{ translateField(contact, 'specification', locale) }}</i></p>
                                <p v-if="contact.street || contact.zipCode || contact.city">
                                    <template v-if="contact.street">
                                        {{ contact.street }}<br>
                                    </template>
                                    <template v-if="contact.city">
                                        {{ contact.zipCode }} {{  translateField(contact, 'city', locale) }}<br>
                                    </template>
                                </p>
                            </template>

                        </div>

                        <!--div class="embed-contacts-list-item-content-tags">

                            <div class="embed-contacts-list-item-content-tags-item"
                                 v-for="contactGroup in contact.contactGroups?.filter(e => getContactGroupById(e.id))">
                                {{ translateField(getContactGroupById(contactGroup.id), 'name', locale) }}
                            </div>

                            <div class="embed-contacts-list-item-content-tags-item" v-if="contact.language">
                                {{ translateField(contact.language, 'name', locale) }}
                            </div>

                        </div-->

                    </div>

                </div>

            </div>

        </transition>

        <div class="embed-contacts-actions" v-if="!isLoading">

            <div class="embed-contacts-actions-item" v-if="!isLoadedFully">

                <a class="embed-contacts-button" @click="clickLoadMore()" v-if="!isLoadingMore">{{ $t('Mehr Kontakte laden', locale) }}</a>
                <a class="embed-contacts-button is-disabled" v-else>{{ $t('Kontakte werden geladen...', locale) }}</a>

            </div>

        </div>

        <transition name="embed-contacts-overlay" mode="out-in">

            <div class="embed-contacts-overlay" v-if="contact" @click="clickHideContact()">

                <EmbedContactsView :contact="contact" :officialEmployment="getOfficialEmployment(contact)" :locale="locale" @click.stop
                                   @clickClose="clickHideContact()"></EmbedContactsView>

            </div>

        </transition>

    </div>

</template>

<script>

import {mapGetters, mapState} from 'vuex';
import { translateField } from '../utils/filters';
import EmbedContactsView from './EmbedContactsView';
import {track, trackDevice, trackPageView} from '../utils/logger';

export default {

    components: {
        EmbedContactsView,
    },

    data() {
        return {
            isLoading: false,
            isLoadingMore: false,
            isLoadedFully: false,
            term: '',
            filters: [],
            limit: 45,
            offset: 0,
            activeFilterSelect: null,
            contacts: [],
            employments: [],
            contact: null,
            contactGroupsParent: null,
        };
    },

    computed: {
        locale () {
            return this.$clientOptions?.locale || 'de';
        },
        responsive () {
            return this.$clientOptions?.responsive ?? true;
        },
        fixedFilters () {
            return this.$clientOptions?.fixedFilters || [];
        },
        disableTelemetry () {
            return this.$clientOptions?.disableTelemetry || false;
        },
        history () {
            return this.$clientOptions?.history || false;
        },
        historyMode () {
            return this.$clientOptions?.history?.mode || 'query';
        },
        historyBase () {
            return this.$clientOptions?.history?.base || '';
        },
        contactSubGroups () {
            let contactGroup = this.filters.find(e => e.type === 'contactGroupsParent');

            if(!contactGroup) {
                return [];
            }

            return this.contactGroups.filter(contactSubGroup => contactSubGroup.parent.id === contactGroup.entity.id);
        },
        ...mapState({
            states: function (state) {
                return state.states.all
                    .filter(e => !e.context || e.context === 'contact')
                    .map(this.$clientOptions?.middleware?.mapStates || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterStates || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortStates || ((a, b) => translateField(a, 'name', this.locale).localeCompare(translateField(b, 'name', this.locale))));
            },
            contactGroupsParents: function (state) {
                return state.contactGroups.filtered
                    .filter(e => (!e.context || e.context === 'contact') && !e.parent)
                    .map(this.$clientOptions?.middleware?.mapContactGroups || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterContactGroups || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortContactGroups || ((a, b) => a.position - b.position));
            },
            contactGroups: function (state) {
                return state.contactGroups.filtered
                    .filter(e => (!e.context || e.context === 'contact') && e.parent)
                    .map(this.$clientOptions?.middleware?.mapContactGroups || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterContactGroups || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortContactGroups || ((a, b) => a.position - b.position));
            },
        }),
    },

    methods: {

        translateField,

        keyUp (event) {

            if(event.keyCode === 27) {
                this.activeFilterSelect = null;
                this.contact = null;
            }

        },

        clickOutside (event) {

            this.activeFilterSelect = null;

        },

        clickInside (event) {

            this.activeFilterSelect = null;

        },

        getFilterParams() {

            let params = {};
            params.term = this.term;

            let filters = [...this.filters, ...(this.fixedFilters || [])];

            for(let filter of filters) {
                params[filter.type] = [];
            }

            for(let filter of filters) {
                params[filter.type].push(filter.entity?.id || filter.entity?.name);
            }

            params.status = ['public'];
            params.limit = this.limit;
            params.offset = this.offset;
            params.orderBy = ['type', 'lastName', 'firstName', 'companyName'];
            params.orderDirection = ['DESC', 'ASC', 'ASC', 'ASC'];

            return params;

        },

        getAdaptedFilterParams() {
            let filterParams = this.getFilterParams();

            let contactGroupsParentFilter = filterParams.contactGroupsParent;
            let contactGroupFilter = filterParams.contactGroup;

            if(contactGroupsParentFilter) {

                if(!contactGroupFilter) {
                    filterParams.contactGroup = this.contactSubGroups.map(contactGroup => {
                        return contactGroup.id;
                    });
                }

                delete filterParams.contactGroupsParent;
            }

            if(!filterParams?.contactGroup?.length) {
                filterParams.contactGroup = this.contactGroups.filter(contactSubGroup => contactSubGroup.parent.id).map(c => c.id);
            }

            return filterParams;
        },

        clickFilterSelectContactGroup(name) {

            this.activeContactGroupParent = name;

            if(this.activeFilterSelect === name) {

                if(!this.disableTelemetry) {
                    track('Contact Filter', 'Hide', name);
                }

                return this.activeFilterSelect = null;
            }

            if(!this.disableTelemetry) {
                track('Contact Filter', 'Show', name);
            }

            this.activeFilterSelect = name;

        },

        clickFilterSelectContactSubGroup(name) {

            if(this.activeFilterSelect === name) {

                if(!this.disableTelemetry) {
                    track('Contact Filter', 'Hide', name);
                }

                return this.activeFilterSelect = null;
            }

            if(!this.disableTelemetry) {
                track('Contact Filter', 'Show', name);
            }

            this.activeFilterSelect = name;

        },

        clickFilterSelect(name) {

            if(this.activeFilterSelect === name) {

                if(!this.disableTelemetry) {
                    track('Contact Filter', 'Hide', name);
                }

                return this.activeFilterSelect = null;
            }

            if(!this.disableTelemetry) {
                track('Contact Filter', 'Show', name);
            }

            this.activeFilterSelect = name;

        },

        clickToggleFilter(filter) {
            this.activeFilterSelect = null;

            if(filter.type === 'contactGroupsParent' || filter.type === 'contactGroup') {
                let existingFilter = this.filters.find(e => e.type === filter.type && e.entity.id === filter.entity.id);
                let oldIndex = this.filters.findIndex(e => e.type === filter.type);

                if(oldIndex !== -1) {
                    this.filters.splice(oldIndex, 1);
                }

                if(filter.type === 'contactGroupsParent') {
                    let oldChildIndex = this.filters.findIndex(e => e.type === 'contactGroup');
                    if(oldChildIndex !== -1) {
                        this.filters.splice(oldChildIndex, 1);
                    }
                }

                if(existingFilter) {
                    this.reload();

                    if(this.history) {
                        window.history.replaceState(null, null, this.getHistoryQueryString());
                    }

                    return;
                }
            }

            let index = this.filters.findIndex(e => e.type === filter.type && e.entity.id === filter.entity.id);

            if(index !== -1) {
                this.filters.splice(index, 1);
                this.reload();

                if(this.history) {
                    window.history.replaceState(null, null, this.getHistoryQueryString());
                }

                if(!this.disableTelemetry) {
                    track('Contact Filter', 'Disable', {
                        type: filter.type,
                        id: filter.entity.id,
                    });
                }

                return;

            }

            this.filters.push(filter);

            this.reload();

            if(this.history) {
                window.history.replaceState(null, null, this.getHistoryQueryString());
            }

            if(!this.disableTelemetry) {
                track('Contact Filter', 'Enable', {
                    type: filter.type,
                    id: filter.entity.id,
                });
            }
        },

        isFilterSelected(filter) {
            return this.filters.find(e => e.type === filter.type && e.entity.id === filter.entity.id);
        },

        changeSearchTerm() {

            this.reload();

            if(this.history) {
                window.history.replaceState(null, null, this.getHistoryQueryString());
            }

            if(!this.disableTelemetry) {
                track('Contact Search', 'Change', this.term);
            }

        },

        clickShowContact(contact) {

            if(this.history) {
                window.history.pushState(null, null, this.getHistoryQueryString(contact));
            }

            if(!this.disableTelemetry) {
                track('Contact Navigation', 'Show Contact', {
                    id: contact.id,
                    name: translateField(contact, 'name', this.locale),
                });
            }

            this.contact = contact;

        },

        clickHideContact() {

            if(this.history) {
                window.history.pushState(null, null, this.getHistoryQueryString());
            }

            if(!this.disableTelemetry) {
                track('Contact Navigation', 'Hide Contact', {
                    id: this.contact.id,
                    name: translateField(this.contact, 'name', this.locale),
                });
            }

            this.contact = null;

        },

        popState(event) {

            this.contact = null;

            if(this.getUrlParams()['contact-id']) {
                this.$store.dispatch('contacts/load', this.getUrlParams()['contact-id']).then((contact) => {
                    this.contact = contact;
                });
            }

        },

        getUrlParams () {
            let queryString = window.location.search;

            if(this.historyMode === 'hash') {
                queryString = window.location.hash.substring(1);
            }

            let urlParams = new URLSearchParams(queryString);
            let result = {};

            for(const [key, value] of urlParams) {

                let k = key.split('[')[0];

                if(!['states', 'contactGroups', 'contactGroupsParents'].includes(k)) {
                    result[k] = value;
                    continue;
                }

                if(!result[k]) {
                    result[k] = [];
                }

                result[k].push(value);

            }

            return result;
        },

        getHistoryQueryString(contact) {

            let result = [];

            if(contact) {
                result.push('contact-id='+contact.id+'&name='+encodeURIComponent(translateField(contact, 'name', this.locale)));
            }

            if(this.term) {
                result.push('term='+encodeURIComponent(this.term));
            }

            for(let filter of this.filters) {
                result.push(filter.type+'s[]='+encodeURIComponent(translateField(filter.entity, 'name', this.locale)))
                //result.push(filter.type+'s[]='+encodeURIComponent(filter.entity.id)
            }

            result = result.join('&');

            if(!result) {
                return this.historyBase;
            }

            return this.historyBase + (this.historyMode === 'hash' ? '#' : '') + '?' + result;

        },

        applyFiltersFromUrlParameters() {

            this.term = this.getUrlParams()['term'];

            let filters = [];

            ['state', 'contactGroup', 'contactGroupsParent'].forEach((key) => {

                let collection = key+'s';

                if(!this.getUrlParams()[key+'s']) {
                    return;
                }

                this.getUrlParams()[key+'s'].forEach((f) => {

                    let entity = this[collection].find(e => e.id === f || e.name === f || translateField(e, 'name', this.locale) === f);

                    if(!entity) {
                        return;
                    }

                    filters.push({
                        type: key,
                        entity: entity,
                    });
                });

            });

            if(filters.length) {
                this.filters = filters;
                this.reload();
            }

        },

        getOfficialEmployment(contact) {

            let officialEmployment = null;

            let companyGroupFilter = this.getFilterParams()?.contactGroup;

            if(contact.officialEmployment?.id) {
                officialEmployment = this.employments.find(employment => employment.id === contact.officialEmployment.id);
            }

            if(companyGroupFilter) {
                let officialEmploymentGroup = this.employments.find(employment => {
                    return employment.employee.id === contact.id && employment.contactGroups.find((contactGroup => contactGroup.id === companyGroupFilter[0]));
                })

                officialEmployment = officialEmploymentGroup || officialEmployment;
            }

            return officialEmployment;
        },

        async reload() {
            this.isLoading = true;
            this.offset = 0;
            this.isLoadedFully = false;
            this.isLoadingMore = false;

            await this.loadContactsData();

            this.isLoading = false;
        },

        async clickLoadMore() {
            this.isLoadingMore = true;
            this.offset += this.limit;
            let currentCount = this.contacts.length;

            await this.loadContactsData('loadMore');

            if (currentCount >= this.contacts.length || this.contacts.length < this.limit) {
                this.isLoadedFully = true;
            }

            this.isLoadingMore = false;

            if (!this.disableTelemetry) {
                track('Contact Navigation', 'Load More', {
                    isLoadedFully: this.isLoadedFully,
                    limit: this.limit,
                    offset: this.offset,
                    count: this.contacts.length,
                });
            }
        },

        async loadContactsData(state = 'reload') {

            let filterParams = this.getAdaptedFilterParams();

            let contactsData = await this.$store.dispatch('contactsData/loadFiltered', {
                contactType: 'employee',
                params: filterParams
            });

            if(state === 'loadMore') {

                this.contacts = [
                    ...this.contacts,
                    ...contactsData.contacts,
                ];

                this.employments = [
                    ...this.employments,
                    ...contactsData.employments,
                ];

                return;
            }

            this.contacts = [
                ...contactsData.contacts,
            ];

            this.employments = [
                ...contactsData.employments,
            ];
        },

    },

    created() {
        this.isLoading = true;

        this.filters = this.$clientOptions?.defaultFilters || [];

        if(this.history && this.getUrlParams()['term']) {
            this.term = this.getUrlParams()['term'];
        }

        this.$store.dispatch('languages/loadAll');

        Promise.all([
            this.$store.dispatch('states/loadAll'),
            this.$store.dispatch('contactGroups/loadFiltered', { status: ['public'] }),
        ]).then(() => {

            this.filters = this.filters
                .filter((filter) => {
                    return ['state', 'contactGroup', 'contactGroupsParent'].includes(filter.type);
                })
                .map((filter) => {
                    return {
                        type: filter.type,
                        entity: {
                            ...this[filter.type+'s'].find(e => e.id === filter.entity.id),
                        },
                    }
                });

            if(this.history) {
                this.applyFiltersFromUrlParameters();
            }

            this.reload();

        });

    },

    mounted() {
        window.addEventListener('click', this.clickOutside);
        window.addEventListener('keyup', this.keyUp);

        if(this.history && this.getUrlParams()['contact-id']) {
            this.$store.dispatch('contacts/load', this.getUrlParams()['contact-id']).then((contact) => {
                this.contact = contact;
            });
        }

        if(this.history) {
            window.addEventListener('popstate', this.popState);
        }

        if(!this.disableTelemetry) {
            trackDevice();
            trackPageView();
        }
    },

    beforeUnmount() {
        window.removeEventListener('click', this.clickOutside);
        window.removeEventListener('keyup', this.keyUp);
        window.removeEventListener('popstate', this.popState);
    },

};

</script>