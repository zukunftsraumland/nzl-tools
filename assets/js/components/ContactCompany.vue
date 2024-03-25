<template>

    <div class="contact-component">

        <div class="contact-component-form">

            <div class="contact-component-form-header">

                <h3>Eintrag erstellen</h3>

                <div class="contact-component-form-header-actions">
                    <a class="button" @click="showPreview = true">Vorschau</a>
                    <a class="button warning" @click="contact.isPublic = true" v-if="!contact.isPublic">Privat</a>
                    <a class="button success" @click="contact.isPublic = false" v-if="contact.isPublic">Öffentlich</a>
                    <a @click="locale = 'de'" class="button" :class="{primary: locale === 'de'}">DE</a>
                    <a @click="locale = 'fr'" class="button" :class="{primary: locale === 'fr'}">FR</a>
                    <a @click="locale = 'it'" class="button" :class="{primary: locale === 'it'}">IT</a>
                    <a class="button error" @click="clickDelete()" v-if="contact.id">Löschen</a>
                    <a class="button warning" @click="clickCancel()">Abbrechen</a>
                    <a class="button primary" @click="clickSave()">Speichern</a>
                </div>

            </div>

            <div class="contact-component-form-section" v-if="formErrors.length">
                <ul class="errors">
                    <li class="error" v-for="error in formErrors">{{ error.message }}</li>
                </ul>
            </div>

            <div class="contact-component-form-section">

                <div class="row">
                    <div class="col-md-3" v-if="locale === 'de'">
                        <label for="companyName">Name</label>
                        <input id="companyName" type="text" class="form-control" v-model="contact.companyName" :placeholder="translate('companyName', contact)">
                    </div>
                    <div class="col-md-4" v-else>
                        <label for="companyName">Name (Übersetzung {{ locale.toUpperCase() }})</label>
                        <input id="companyName" type="text" class="form-control" v-model="contact.translations[locale].companyName" :placeholder="translate('companyName', contact)">
                    </div>
                    <div class="col-md-3" v-if="locale === 'de'">
                        <label for="specification">Zusatzangabe Fa./Inst.</label>
                        <input id="specification" type="text" class="form-control" v-model="contact.specification" :placeholder="translate('specification', contact)">
                    </div>
                    <div class="col-md-4" v-else>
                        <label for="specification">Zusatzangabe Fa./Inst. (Übersetzung {{ locale.toUpperCase() }})</label>
                        <input id="specification" type="text" class="form-control" v-model="contact.translations[locale].specification" :placeholder="translate('specification', contact)">
                    </div>
                </div>

                <div class="row" v-if="contacts.length">
                    <div class="col-md-4">
                        <label for="parent">Übergeordnete Organisation</label>
                        <div class="select-wrapper">
                            <select class="form-control" @change="contact.parent = getContactById(parseInt($event.target.value)) || null" :value="contact.parent?.id" >
                                <option :value="null"></option>
                                <option v-for="option in availableContacts"
                                        :value="option.id">{{ option.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <label for="street">Strasse</label>
                        <input id="street" type="text" class="form-control" v-model="contact.street" :placeholder="translate('street', contact)">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-1">
                        <label for="zipCode">PLZ</label>
                        <input id="zipCode" type="text" class="form-control" v-model="contact.zipCode" :placeholder="translate('zipCode', contact)">
                    </div>
                    <div class="col-md-3" v-if="locale === 'de'">
                        <label for="city">Ort</label>
                        <input id="city" type="text" class="form-control" v-model="contact.city" :placeholder="translate('city', contact)">
                    </div>
                    <div class="col-md-3" v-else>
                        <label for="city">Ort (Übersetzung {{ locale.toUpperCase() }})</label>
                        <input id="city" type="text" class="form-control" v-model="contact.translations[locale].city" :placeholder="translate('city', contact)">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <label for="country">Land</label>
                        <div class="select-wrapper">
                            <select class="form-control" @change="contact.country = getCountryById(parseInt($event.target.value)) || null; contact.state = null" :value="contact.country?.id">
                                <option :value="null"></option>
                                <option v-for="country in countries" :value="country.id">{{ country.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3" v-if="contact.country?.id === 1">
                        <label for="province">Kanton</label>
                        <div class="select-wrapper">
                            <select class="form-control" @change="contact.state = getStateById(parseInt($event.target.value)) || null" :value="contact.state?.id">
                                <option :value="null"></option>
                                <option v-for="state in states" :value="state.id">{{ state.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <label for="language">Sprache</label>
                        <div class="select-wrapper">
                            <select class="form-control" @change="contact.language = getLanguageById(parseInt($event.target.value)) || null" :value="contact.language?.id">
                                <option :value="null"></option>
                                <option v-for="language in languages" :value="language.id">{{ language.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <label for="email">E-Mail</label>
                        <input id="email" type="text" class="form-control" v-model="contact.email" :placeholder="translate('email', contact)">
                    </div>
                    <div class="col-md-3">
                        <label for="phone">Telefon</label>
                        <input id="phone" type="text" class="form-control" v-model="contact.phone" :placeholder="translate('phone', contact)">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3" v-if="locale === 'de'">
                        <label for="website">Website</label>
                        <input id="website" type="text" class="form-control" v-model="contact.website" :placeholder="translate('website', contact)">
                    </div>
                    <div class="col-md-3" v-else>
                        <label for="website">Website (Übersetzung {{ locale.toUpperCase() }})</label>
                        <input id="website" type="text" class="form-control" v-model="contact.translations[locale].website" :placeholder="translate('website', contact)">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8" v-if="locale === 'de'">
                        <label for="text">Beschreibung</label>
                        <ckeditor id="text" :editor="editor" :config="editorConfig"
                                  v-model="contact.description" :placeholder="translate('description', contact)"></ckeditor>
                    </div>
                    <div class="col-md-8" v-else>
                        <label for="text">Beschreibung (Übersetzung {{ locale.toUpperCase() }})</label>
                        <ckeditor id="text" :editor="editor" :config="editorConfig"
                                  v-model="contact.translations[locale].description" :placeholder="translate('description', contact)"></ckeditor>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label for="contactGroups">Kontaktgruppen</label>
                        <tag-selector id="contactGroups" :model="contact.contactGroups"
                                      :options="contactGroupOptions" :searchType="'select'" :isOptGroup="true"></tag-selector>
                    </div>
                </div>

                <div class="contact-component-form-section-group" style="position: relative">

                    <div class="row">
                        <div class="col-md-12">
                            <label>Angestellte</label>

                            <div class="contact-group-component-form-section-group" v-for="(employment, index) in contact.employments">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="employee">Angestellte(r)</label>
                                        <single-selector id="employee" :value="employment.employee" @update="employment.employee = $event || {}"
                                                         :options="contactsAll.filter(contact => contact.type === 'person')"></single-selector>
                                    </div>
                                    <div class="col-md-2" v-if="locale === 'de'">
                                        <label for="employment">Funktion</label>
                                        <input id="employment" type="text" class="form-control" v-model="employment.role" :placeholder="translate('role', contact)">
                                    </div>
                                    <div class="col-md-2" v-else>
                                        <label for="employment">Funktion (Übersetzung {{ locale.toUpperCase() }})</label>
                                        <input id="employment" type="text" class="form-control" v-model="employment.translations[locale].role" :placeholder="translate('role', contact)">
                                    </div>
                                    <div class="col-md-2">
                                        <label>&nbsp;</label>
                                        <div class="button warning" @click="clickRemoveEmployment(index)">Angestellte(n) entfernen</div>
                                    </div>
                                </div>
                            </div>
                            <div class="contact-group-component-form-section-group">
                                <div class="button success" @click="clickAddEmployment()">Angestellte(n) hinzufügen</div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <div class="contact-component-overlay" v-if="showPreview" @click="showPreview = false">

            <EmbedContactsView @click.stop @clickClose="showPreview = false"
                               :contact="contact" :locale="locale"></EmbedContactsView>

        </div>

        <transition name="fade">
            <Modal v-if="modal" :config="modal"></Modal>
        </transition>

    </div>

</template>

<script>
import { mapState, mapGetters } from 'vuex';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import TagSelector from './TagSelector';
import SingleSelector from './SingleSelector.vue';
import Modal from './Modal';
import EmbedContactsView from "./EmbedContactsView.vue";

export default {
    data() {
        return {
            locale: 'de',
            contact: {
                type: 'company',
                companyName: '',
                specification: '',
                gender: '',
                academicTitle: '',
                firstName: '',
                lastName: '',
                street: '',
                zipCode: '',
                city: '',
                country: null,
                state: null,
                language: null,
                email: '',
                phone: '',
                website: '',
                description: '',
                officialEmployment: null,
                parent: null,
                children: [],
                employments: [],
                companies: [],
                employees: [],
                contactGroups: [],
                translations: {
                    fr: {},
                    it: {},
                },
                isPublic: false,
            },
            showPreview: false,
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
            formErrors: [],
        };
    },
    components: {
        EmbedContactsView,
        TagSelector,
        SingleSelector,
        Modal,
    },
    computed: {
        ...mapState({
            selectedContact: state => state.contacts.contact,
            contacts: state => state.contacts.filtered,
            contactsAll: state => state.contacts.all,
            contactGroups: state => state.contactGroups.all,
            countries: state => state.countries.all,
            states: state => state.states.all,
            languages: state => state.languages.all,
        }),
        ...mapGetters({
            getContactById: 'contacts/getById',
            getCountryById: 'countries/getById',
            getStateById: 'states/getById',
            getLanguageById: 'languages/getById',
        }),
        availableContacts() {
            if(!this.contacts?.length) {
                return [];
            }

            return this.contacts.filter(contact => (!contact.context || contact.context === 'contact') && contact.id !== this.contact.id && contact.type === 'company');
        },
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
        clickDelete () {
            this.modal = {
                title: 'Eintrag löschen',
                description: 'Sind Sie sicher dass Sie diesen Eintrag unwiderruflich löschen möchten?',
                actions: [
                    {
                        label: 'Endgültig löschen',
                        class: 'error',
                        onClick: () => {
                            this.$store.dispatch('contacts/delete', this.contact.id).then(() => {
                                this.$router.push('/contacts/company');
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
            this.$router.push('/contacts/company');
        },
        async clickSave() {
            if(this.contact.id) {
                this.$store.dispatch('contacts/update', this.contact)
                    .then((response) => {
                        this.$router.push('/contacts/company');

                    }, (error) => {
                        this.formErrors = error.response.data ? error.response.data.errors : [{message: 'Ein unbekannter Fehler ist aufgetreten.'}];
                    });
            } else {
                this.$store.dispatch('contacts/create', this.contact)
                    .then(() => {
                        this.$router.push('/contacts/company');

                    }, (error) => {
                        this.formErrors = error.response.data ? error.response.data.errors : [{message: 'Ein unbekannter Fehler ist aufgetreten.'}];
                    });
            }
        },
        clickAddEmployment() {
            let employment = {
                position: null,
                company: null,
                employee: null,
                role: null,
                contactGroups: [],
                translations: {
                    fr: {
                        role: null
                    },
                    it: {
                        role: null
                    }
                },
            }

            this.contact.employments.push(employment);
        },
        clickRemoveEmployment(index) {
            let employment = this.contact.employments.splice(index, 1)[0];
        },
        async reload() {
            if(this.$route.params.id) {
                this.$store.commit('contacts/set', {});

                let contactsData = await this.$store.dispatch('contactsData/loadFiltered', {
                    contactType: 'company',
                    params: {ids: [this.$route.params.id]},
                });

                this.contact = {...contactsData.contacts[0]};

                this.contact.employments = contactsData.employments;
            }
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
        this.$store.dispatch('contacts/loadFiltered', { type: ['company'] });
        this.$store.dispatch('contacts/loadAll');
        this.reload();
    }
}
</script>