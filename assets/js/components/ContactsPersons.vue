<template>

    <div class="contacts-component">

        <div class="contacts-component-title">

            <h2>Personen</h2>

            <transition name="fade" mode="out-in">
                <div class="loading-indicator" v-if="isLoading('contacts')"></div>
            </transition>

            <div class="contacts-component-title-actions">
                <a href="/api/v1/contacts.xlsx?type[]=person" class="button" download>XLSX</a>
                <router-link :to="'/contacts/person/add'" class="button primary">Neuen Eintrag erstellen</router-link>
            </div>

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
                        <label for="status">Status</label>
                        <div class="select-wrapper">
                            <select id="status" class="form-control" @change="addFilter({ type: 'status', value: $event.target.value }); $event.target.value = null;">
                                <option></option>
                                <option :value="'public'">Öffentlich</option>
                                <option :value="'draft'">Privat</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="contact-group">Kontaktgruppe</label>
                        <div class="select-wrapper">
                            <select id="contact-group" class="form-control" @change="addFilter({ type: 'contactGroup', value: $event.target.value, label: $event.target.options[$event.target.selectedIndex].dataset.label }); $event.target.value = null;">
                                <option value="null"></option>
                                <optgroup :label="optGroup.name" v-for="optGroup in contactGroupOptions">
                                    <option v-for="option in optGroup.children" :value="option.id" :data-label="optGroup.name+': '+option.name">{{ option.name }}</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="country">Land</label>
                        <div class="select-wrapper">
                            <select id="country" class="form-control" @change="addFilter({ type: 'country', value: $event.target.value }); $event.target.value = null;">
                                <option></option>
                                <option v-for="country in countries.filter(country => !country.context || country.context === 'contact')">{{ country.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="language">Sprache</label>
                        <div class="select-wrapper">
                            <select id="language" class="form-control" @change="addFilter({ type: 'language', value: $event.target.value }); $event.target.value = null;">
                                <option></option>
                                <option v-for="language in languages.filter(language => !language.context || language.context === 'contact')">{{ language.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="contacts-component-filter-tags">
                <div class="tag" v-for="filter of filters" @click="removeFilter({ type: filter.type, value: filter.value })">
                    <strong v-if="filter.type === 'status'">Status:</strong>
                    <strong v-if="filter.type === 'contactGroup'">Kontaktgruppe:</strong>
                    <strong v-if="filter.type === 'country'">Land:</strong>
                    <strong v-if="filter.type === 'language'">Sprache:</strong>
                    <template v-if="['status'].includes(filter.type)">
                        &nbsp;{{ filter.value === 'public' ? 'Öffentlich' : 'Privat' }}
                    </template>
                    <template v-else-if="['contactGroup'].includes(filter.type)">
                        &nbsp;{{ filter.label }}
                    </template>
                    <template v-else>
                        &nbsp;{{ filter.value }}
                    </template>
                </div>
            </div>

        </div>

        <div class="contacts-component-content">
            <table class="table">
                <thead>
                <tr>
                    <th><input id="active" type="checkbox" :checked="selectedElements.length > 0 && selectedElements.length === contacts.length" @change="clickToggleSelected" @click.stop></th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>E-Mail</th>
                    <th>Telefon</th>
                    <th>Organisation</th>
                    <th>Kontaktgruppe</th>
                    <th>Erstellt</th>
                    <th>Geändert</th>
                </tr>
                </thead>
                <tbody v-if="!contacts.length && isLoading('contacts')">
                <tr>
                    <td colspan="11"><em>Einträge werden geladen...</em></td>
                </tr>
                </tbody>
                <tbody v-else >
                <tr class="clickable" v-for="contact in contacts"
                    :class="{'warning': !contact.isPublic}"
                    @click="clickContact(contact)">
                    <td><input id="active" type="checkbox" :checked="selectedElements.find(person => person.id === contact.id)" @change="clickToggleSelected($event, contact)" @click.stop></td>
                    <td>{{ contact.id }}</td>
                    <td>{{ contact.firstName }} {{ contact.lastName }}</td>
                    <td>{{ contact.email }}</td>
                    <td>{{ contact.phone }}</td>
                    <td><template v-for="company of getCompanies(contact)">{{ company.name }}<br></template></td>
                    <td v-html="formatOneToManyGroups(contact.contactGroups, getContactGroupById)"></td>
                    <td>{{ formatDateTime(contact.createdAt) }}</td>
                    <td>{{ formatDateTime(contact.updatedAt) }}</td>
                </tr>
                </tbody>
            </table>

            <br><a @click="clickLoadMore()" class="button" v-if="!isLoadedFully">Mehr Kontakte laden</a>

        </div>

        <div class="contacts-component-content">

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="action">Aktionen</label>
                        <div class="select-wrapper">
                            <select id="action" class="form-control" v-model="action" @change="clickSelectAction" :class="{ disabled: !this.targetGroup }">
                                <option value="null"></option>
                                <option value="add-to-group">Zu Kontaktgruppe hinzufügen</option>
                                <option value="export">Exportieren</option>
                                <option value="delete">Löschen</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3" v-if="action === 'add-to-group'">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="select-wrapper">
                            <select id="target-contact-group" class="form-control" v-model="targetGroup" @change="clickAddContactsToContactGroup">
                                <option value="null"></option>
                                <option :value="contactGroup.id"
                                    v-for="contactGroup in contactGroups.filter(contactGroup => !contactGroup.context || contactGroup.context === 'contact')">
                                    {{ contactGroup.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</template>

<script>
import {mapGetters, mapState} from 'vuex';
import moment from 'moment';

export default {
    data () {
        return {
            contacts: [],
            employments: [],
            term: '',
            filters: [],
            limit: 200,
            offset: 0,
            isLoadedFully: false,
            selectedElements: [],
            targetGroup: null,
            action: '',
        };
    },
    computed: {
        ...mapState({
            contactGroups: state => state.contactGroups.all,
            countries: state => state.countries.all,
            languages: state => state.languages.all,
        }),
        ...mapGetters({
            isLoading: 'loaders/isLoading',
            getContactGroupById: 'contactGroups/getById',
            getContactById: 'contacts/getById',
        }),
        contactGroupOptions() {
            let contactGroupOptions = [];

            let parentGroups = this.contactGroups.filter(group => !group.parent);

            for(let parentGroup of parentGroups) {
                parentGroup.children = this.contactGroups.filter(group => group.parent?.id === parentGroup.id);

                if(parentGroup.children.length) {
                    contactGroupOptions.push(parentGroup);
                }
            }

            return contactGroupOptions;
        },
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

            params.type = ['person'];
            params.limit = this.limit;
            params.offset = this.offset;
            params.orderBy = ['lastName', 'firstName'];
            params.orderDirection = ['ASC', 'ASC'];

            return params;
        },
        async reloadContacts () {
            this.isLoadedFully = false;
            this.offset = 0;

            let contactsData = await this.$store.dispatch('contactsData/loadFiltered', {
                contactType: 'employee',
                params: this.getFilterParams(),
            });

            this.contacts = [
                ...contactsData.contacts,
            ];

            this.employments = [
                ...contactsData.employments,
            ];
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
            this.$router.push({
                path: '/contacts/person/'+contact.id+'/edit'
            });
        },
        getCompanies(contact) {
            if(!this.employments.length) {
                return [];
            }

            let employments = this.employments.filter(employment => employment.employee?.id === contact.id) || [];
            return employments.map(employment => employment.company) || [];
        },
        clickToggleSelected(event, element = null) {
            let isSelected = event.target.checked;

            if(!element) {
                this.selectedElements = [];

                if(isSelected) {
                    this.selectedElements = [...this.contacts];
                }

                return;
            }

            if(!isSelected) {
                let elementIndex = this.selectedElements.indexOf(element);

                if(elementIndex !== null) {
                    this.selectedElements.splice(elementIndex, 1);
                }

                return;
            }

            this.selectedElements.push(element);
        },
        validateSelectedElements() {
            if(!this.selectedElements?.length) {
                alert('Wählen Sie einen oder mehrere Kontakte zur weiteren Bearbeitung.');
                this.action = '';
                return false;
            }

            return true;
        },
        clickSelectAction() {
            if(!this.validateSelectedElements()) {
                return;
            }

            switch(this.action) {
                case 'export':
                    this.action = '';
                    /*let filterParams = [];
                    for(let filter of this.filters) {
                        filterParams.push(filter.type+'[]='+encodeURIComponent(filter.value.toLowerCase()));
                    }

                    window.location.href='/api/v1/contacts.xlsx?type[]=person&term=' + encodeURIComponent(this.term)+'&'+filterParams.join('&');*/

                    let filterParams = [];
                    for(let element of this.selectedElements) {
                        filterParams.push('ids[]='+element.id);
                    }

                    window.location.href='/api/v1/contacts.xlsx?'+filterParams.join('&');
                    break;

                case 'delete':
                    this.action = '';
                    if(window.confirm('Sind Sie sicher, dass Sie die markierten Kontakte löschen möchten?')) {
                        for(let contact of this.selectedElements) {
                            this.$store.dispatch('contacts/delete', contact.id)
                                .then(() => {
                                    this.reloadContacts();
                                }).catch((err) => {
                                    alert('Der Eintrag mit der ID '+contact.id+' konnte nicht gelöscht werden.');
                                });
                        }
                    }
            }
        },
        clickAddContactsToContactGroup() {
            this.action = '';
            let contactGroup = this.getContactGroupById(this.targetGroup);

            if(!this.validateSelectedElements()) {
                return this.targetGroup = null;
            }

            if(!contactGroup) {
                return;
            }

            if(window.confirm('Sind Sie sicher, dass Sie die markierten Kontakte zu der Kontaktgruppe "ID '+contactGroup.id+': '+contactGroup.name+'" hinzufügen möchten?')) {
                contactGroup.contacts = [...this.selectedElements];
                this.$store.dispatch('contactGroups/update', contactGroup).then(() => {
                    this.reloadContacts();
                    this.targetGroup = null;
                });
            }
        },
        formatOneToMany (items, getter) {
            let result = [];
            items.forEach((item) => {
                result.push(getter(item?.id)?.name);
            });

            return result.join(', ');
        },
        formatOneToManyGroups (items, getter) {
            let result = [];
            items.forEach((item) => {
                let group = (getter(item?.id)?.parent?.name ? getter(item.id).parent.name + ': ' : '') + getter(item.id)?.name;
                result.push(group);
            });

            return result.join('<br>');
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
            window.sessionStorage.setItem('regiosuisse.contacts.persons.filters', JSON.stringify(this.filters));
            window.sessionStorage.setItem('regiosuisse.contacts.persons.term', this.term);

            this.selectedElements = [];
        },
        loadFilter () {
            this.filters = JSON.parse(window.sessionStorage.getItem('regiosuisse.contacts.persons.filters') || '[]');
            this.term = window.sessionStorage.getItem('regiosuisse.contacts.persons.term') || '';
        },
    },
    created () {
        this.loadFilter();
        this.reloadContacts();
    },
}
</script>