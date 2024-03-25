<template>

    <div class="contact-group-component">

        <div class="contact-group-component-form">

            <div class="contact-group-component-form-header">

                <h3>Eintrag erstellen</h3>

                <div class="contact-group-component-form-header-actions">
                    <a class="button waxrning" @click="contactGroup.isPublic = true" v-if="!contactGroup.isPublic">Privat</a>
                    <a class="button success" @click="contactGroup.isPublic = false" v-if="contactGroup.isPublic">Öffentlich</a>
                    <a @click="locale = 'de'" class="button" :class="{primary: locale === 'de'}">DE</a>
                    <a @click="locale = 'fr'" class="button" :class="{primary: locale === 'fr'}">FR</a>
                    <a @click="locale = 'it'" class="button" :class="{primary: locale === 'it'}">IT</a>
                    <a class="button error" @click="clickDelete()" v-if="contactGroup.id">Löschen</a>
                    <a class="button warning" @click="clickCancel()">Abbrechen</a>
                    <a class="button primary" @click="clickSave()">Speichern</a>
                </div>

            </div>

            <div class="contact-component-form-section" v-if="formErrors.length">
                <ul class="errors">
                    <li class="error" v-for="error in formErrors">{{ error.message }}</li>
                </ul>
            </div>

            <div class="contact-group-component-form-section">

                <div class="row">
                    <div class="col-md-4" v-if="locale === 'de'">
                        <label for="title">Bezeichnung</label>
                        <input id="title" type="text" class="form-control" v-model="contactGroup.name" :placeholder="translate('name',contactGroup)">
                    </div>
                    <div class="col-md-4" v-else>
                        <label for="title">Bezeichnung (Übersetzung {{ locale.toUpperCase() }})</label>
                        <input id="title" type="text" class="form-control" v-model="contactGroup.translations[locale].name" :placeholder="translate('name',contactGroup)">
                    </div>
                </div>

                <div class="row" v-if="!isParent">
                    <div class="col-md-4">
                        <label for="parent">Übergruppe</label>
                        <div class="select-wrapper">
                            <select class="form-control" @change="contactGroup.parent = getContactGroupById(parseInt($event.target.value)) || null" :value="contactGroup.parent?.id">
                                <option :value="null"></option>
                                <option v-for="option of availableContactGroups"
                                        :value="option.id">{{ option.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label>&nbsp;</label>
                        <tag-selector id="contacts" :model="contactGroup.contacts" :showSelection="false" @change="loadContact($event)"
                                      :options="contactsAll" :searchType="'text'"></tag-selector>
                    </div>
                </div>

            </div>

            <div class="contact-group-component-form-section">

                <label for="contacts">Kontakt hinzufügen</label>

                <table class="table">
                    <thead>
                    <tr>
                        <th><input id="active" type="checkbox" :checked="contacts?.length && selectedElements.length === contacts.length" @change="clickToggleSelected" @click.stop></th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>E-Mail</th>
                        <th>Telefon</th>
                        <th>Hauptadresse (opt.)</th>
                    </tr>
                    </thead>
                    <tbody v-if="isLoading">
                    <tr>
                        <td colspan="11"><em>Einträge werden geladen...</em></td>
                    </tr>
                    </tbody>
                    <tbody v-else>
                    <tr v-for="contact in contacts"
                        class="clickable"
                        :class="{'warning': !contact.isPublic}"
                        @click="clickContact(contact)">
                        <td><input id="active" type="checkbox" :checked="selectedElements.find(person => person.id === contact.id)" @change="clickToggleSelected($event, contact)" @click.stop></td>
                        <td>{{ contact.id }}</td>
                        <td v-if="contact.type === 'company'">{{ contact.name }}</td>
                        <td v-else>{{ contact.firstName }} {{ contact.lastName }}</td>
                        <td>{{ contact.email }}</td>
                        <td>{{ contact.phone }}</td>
                        <td>
                            <div class="select-wrapper" v-if="contact.type === 'person' && contact.employments?.length" @click.stop>
                                <select class="form-control" :value="officialEmployments[contact.id]?.id || null" @change="setOfficialEmployment($event.target.value, contact.id)">
                                    <option :value="null"></option>
                                    <option v-for="option of contact.employments"
                                            :value="option.id">{{ option?.company?.name }}</option>
                                </select>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <br><a @click="clickLoadMore()" class="button" v-if="!isLoadedFully && contacts.length && $route.params.id">Mehr Kontakte laden</a>

            </div>

            <div class="contact-group-component-form-section">

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="action">Aktionen</label>
                            <div class="select-wrapper">
                                <select id="action" class="form-control" v-model="action" @change="clickSelectAction">
                                    <option value="null"></option>
                                    <option value="remove">Aus Gruppe entfernen</option>
                                    <option value="export">Exportieren</option>
                                    <!--option value="delete">Löschen</option-->
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <transition name="fade">
            <Modal v-if="modal" :config="modal"></Modal>
        </transition>

    </div>

</template>

<script>
import { mapGetters, mapState } from 'vuex';
import draggable from 'vuedraggable';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import Modal from './Modal';
import TagSelector from "./TagSelector.vue";

export default {
    data() {
        return {
            isLoading: false,
            isLoadedFully: false,
            locale: 'de',
            contactGroup: {
                position: 10000,
                name: '',
                parent: null,
                children: [],
                contacts: [],
                employments: [],
                translations: {
                    fr: {},
                    it: {},
                },
                isPublic: false,
            },
            contacts: [],
            employments: [],
            officialEmployments: {},
            limit: 30,
            offset: 0,
            modal: null,
            editor: ClassicEditor,
            editorConfig: {
                basicEntities: false,
                toolbar: {
                    items: [
                        'heading',
                        '|',
                        'bold',
                        'italic',
                        'link',
                        '|',
                        'numberedList',
                        'bulletedList',
                        'insertTable',
                        '|',
                        'undo',
                        'redo',
                    ]
                }
            },
            selectedElements: [],
            action: '',
            formErrors: [],
        };
    },
    components: {
        TagSelector,
        draggable,
        Modal,
    },
    computed: {
        ...mapState({
            selectedContactGroup: state => state.contactGroups.contactGroup,
            contactGroups: state => state.contactGroups.all,
            contactsAll: state => state.contacts.all,
        }),
        ...mapGetters({
            getContactGroupById: 'contactGroups/getById',
            getContactById: 'contacts/getById',
        }),
        availableContactGroups() {
            if(!this.contactGroups?.length) {
                return [];
            }

            return this.contactGroups.filter(contactGroup => (!contactGroup.context || contactGroup.context === 'contact') && !contactGroup.parent && contactGroup.id !== this.contactGroup.id);
        },
        isParent() {
           return this.contactGroups.filter(contactGroup => this.contactGroup.id && contactGroup.parent?.id === this.contactGroup?.id)?.length;
        },
    },
    methods: {
        getFilterParamsContacts() {
            let params = {};

            if(this.selectedContactGroup.id) {
                params.contactGroup = [this.selectedContactGroup.id];
            } else {
                params.ids = this.contactGroup.contacts.map(contact => {
                    return contact.id;
                })
            }

            params.limit = this.limit;
            params.offset = this.offset;
            params.orderBy = ['type', 'lastName', 'firstName', 'companyName'];
            params.orderDirection = ['DESC', 'ASC', 'ASC', 'ASC'];

            return params;
        },
        clickDelete () {
            this.modal = {
                title: 'Eintrag löschen',
                description: 'Sind Sie sicher dass Sie diesen Eintrag unwiderruflich löschen möchten?',
                actions: [
                    {
                        label: 'Endgültig löschen',
                        class: 'error',
                        onClick: () => {
                            this.$store.dispatch('contactGroups/delete', this.contactGroup.id).then(() => {
                                this.$router.push('/contact-groups');
                            });
                        }
                    },
                    {
                        label: 'Abbrechen',
                        class: 'warning',
                        onClick: () => {
                            this.modal = null;
                        }
                    }
                ],
            };
        },
        clickCancel () {
            this.$router.push('/contact-groups');
        },
        clickSave() {

            this.contactGroup.employments = [];

            for(let employment of Object.values(this.officialEmployments)) {
                this.contactGroup.employments.push(employment);
            }

            if(this.contactGroup.id) {
                return this.$store.dispatch('contactGroups/update', this.contactGroup).then(() => {
                    this.$router.push('/contact-groups');
                });
            }

            if(!this.contactGroup.id) {

                this.$store.dispatch('contactGroups/create', this.contactGroup)
                    .then((response) => {
                        this.$store.dispatch('contacts/load', response.data);
                        this.$router.push('/contact-groups');

                    }, (error) => {
                        this.formErrors = error.response.data ? error.response.data.errors : [{message: 'Ein unbekannter Fehler ist aufgetreten.'}];
                    })
            }
        },
        clickContact (contact) {
            this.$router.push({
                path: '/contacts/'+contact.type+'/'+contact.id+'/edit'
            });
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
                /*case 'delete':
                    if(window.confirm('Sind Sie sicher, dass Sie die markierten Kontakte löschen möchten?')) {

                        for(let contact of this.selectedElements) {
                            this.$store.dispatch('contacts/delete', contact.id).then(() => {
                                this.action = '';
                            });
                        }

                        this.reloadContacts();
                    }
                    break;*/
                case 'remove':
                    if(window.confirm('Sind Sie sicher, dass Sie die markierten Kontakte aus der Kontaktgruppe entfernen möchten?')) {

                        for(let contact of this.selectedElements) {
                            let targetContact = this.contacts.find(targetContact => targetContact.id === contact.id);
                            let contactIndex = this.contacts.indexOf(targetContact);
                            let groupContact = this.contactGroup.contacts.find(groupContact => groupContact.id === contact.id);
                            let groupContactIndex = this.contactGroup.contacts.indexOf(groupContact);

                            this.contacts.splice(contactIndex, 1);
                            this.contactGroup.contacts.splice(groupContactIndex, 1);
                        }

                        this.selectedElements = [];
                    }
                    break;
                case 'export':
                    let contactGroup = this.contactGroup.id || null;

                    let filterParams = [];

                    filterParams.push('contactGroupIds[]='+contactGroup);

                    for(let element of this.selectedElements) {
                        filterParams.push('ids[]='+element.id);
                    }

                    window.location.href='/api/v1/contacts.xlsx/contact-groups?'+filterParams.join('&');
                    break;
            }

            this.action = '';
        },
        async setOfficialEmployment(employmentId, contactId) {
            let officialEmployment = this.employments.find(employment => employment.id === parseInt(employmentId)) || null;

            if(!officialEmployment) {
                return delete this.officialEmployments[contactId];
            }

            this.officialEmployments[contactId] = this.employments.find(employment => employment.id === parseInt(employmentId)) || null;
        },
        reload() {
            if(this.$route.params.id) {
                this.isLoading = true;

                this.$store.dispatch('contactGroups/load', this.$route.params.id).then(() => {
                    this.contactGroup = {...this.selectedContactGroup};
                    this.reloadContacts();
                })
            }
        },
        async reloadContacts() {
            this.isLoadedFully = false;
            this.offset = 0;

            await this.loadContactsData(this.getFilterParamsContacts(), 'reload');

            this.isLoading = false;
        },
        async loadContact(contacts) {
            this.isLoading = true;

            let contactId = contacts[contacts.length - 1]?.id;
            let filter = { ids: [contactId] };

            let contactsData = await this.loadContactsData(filter, 'update');
            this.contacts.unshift(contactsData.contacts[0]);

            this.isLoading = false;
        },
        async clickLoadMore () {
            this.isLoadingMore = true;
            this.offset += this.limit;
            let currentCount = this.contacts.length;

            await this.loadContactsData(this.getFilterParamsContacts(), 'loadMore');

            if (currentCount >= this.contacts.length || this.contacts.length < this.limit) {
                this.isLoadedFully = true;
            }

            this.isLoadingMore = false;
        },
        async loadContactsData(filter, action = 'reload') {

            let contactsData = await this.$store.dispatch('contactsData/loadFiltered', {
                contactType: 'employee',
                params: filter,
            });

            contactsData.contacts.map(contact => {
                return contact.employments = contactsData.employments.filter(employment => employment.employee.id === contact.id);
            })

            if(action === 'reload') {

                this.contacts = [
                    ...contactsData.contacts,
                ];

                this.employments = [
                    ...contactsData.employments,
                ];

            } else {

                let employments = contactsData.employments.filter(employment => this.employments.every(emp => emp.id !== employment));

                this.employments = [
                    ...this.employments,
                    ...employments,
                ];
            }

            if(action === 'loadMore') {

                this.contacts = [
                    ...this.contacts,
                    ...contactsData.contacts,
                ];
            }

            for(let employment of contactsData.employments) {

                if(employment.contactGroups.find(contactGroup => contactGroup.id === this.selectedContactGroup.id)) {
                    this.officialEmployments[employment.employee?.id] = employment;
                }

                if(action === 'update' && contactsData.contacts[0]?.officialEmployment) {
                    if(employment.id === contactsData.contacts[0].officialEmployment.id) {
                        this.officialEmployments[employment.employee?.id] = employment;
                    }
                }
            }

            return contactsData;
        },
        translate(property, context) {
            if(this.locale === 'de') {
                return context[property] || context.translations.fr[property] || context.translations.it[property];
            }
            if(this.locale === 'fr') {
                return context.translations.fr[property] || context[property] || context.translations.it[property];
            }
            if(this.locale === 'it') {
                return context.translations.it[property] || context.translations.fr[property] || context[property];
            }
            return context[property];
        },
    },
    created () {
        this.$store.commit('contactGroups/set', {});
        this.$store.dispatch('contacts/loadAll');
        this.reload();
    }
}
</script>