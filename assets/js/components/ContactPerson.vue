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
                    <div class="col-md-1">
                        <label for="gender">Anrede</label>
                        <div class="select-wrapper">
                            <select class="form-control" v-model="contact.gender">
                                <option value="male">Herr</option>
                                <option value="female">Frau</option>
                                <option value="other">Keine Angabe</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="academicTitle">Titel</label>
                        <input id="academicTitle" type="text" class="form-control" v-model="contact.academicTitle" :placeholder="translate('academicTitle', contact)">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <label for="firstName">Vorname</label>
                        <input id="firstName" type="text" class="form-control" v-model="contact.firstName" :placeholder="translate('firstName', contact)">
                    </div>
                    <div class="col-md-3">
                        <label for="lastName">Nachname</label>
                        <input id="lastName" type="text" class="form-control" v-model="contact.lastName" :placeholder="translate('lastName', contact)">
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
                        <tag-selector id="contactGroups" :model="contact.contactGroups" @change="addContactGroups($event)"
                                      :options="contactGroupOptions" :searchType="'select'" :isOptGroup="true"></tag-selector>
                    </div>
                </div>

                <div class="contact-component-form-section-group">

                    <div class="row">
                        <div class="col-md-12">
                            <label>Anstellungen</label>
                            <div class="contact-group-component-form-section-group" v-for="(employment, index) in contact.employments">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="company">Organisation</label>
                                        <single-selector id="company" :value="employment.company" @update="employment.company = $event || {}"
                                                         :options="contacts"></single-selector>
                                    </div>
                                    <div class="col-md-2" v-if="locale === 'de'">
                                        <label for="employment">Funktion</label>
                                        <input id="employment" type="text" class="form-control" v-model="employment.role" :placeholder="translate('role', contact)">
                                    </div>
                                    <div class="col-md-2" v-else>
                                        <label for="employment">Funktion (Übersetzung {{ locale.toUpperCase() }})</label>
                                        <input id="employment" type="text" class="form-control" v-model="employment.translations[locale].role" :placeholder="translate('role', contact)">
                                    </div>
                                    <div class="col-md-1">
                                        <label>Hauptadresse</label>
                                        <input id="officialAddress" :name="'official-address-'+index" type="checkbox" :checked="contact.officialEmployment === employment || false" :true-value="employment" v-model="contact.officialEmployment">
                                    </div>
                                    <div class="col-md-2">
                                        <label>&nbsp;</label>
                                        <div class="button warning" @click="clickRemoveEmployment(employment.id, index)">Anstellung entfernen</div>
                                    </div>
                                </div>
                            </div>
                            <div class="contact-group-component-form-section-group">
                                <div class="button success" @click="clickAddEmployment()">Anstellung hinzufügen</div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <div class="contact-component-overlay" v-if="showPreview" @click="showPreview = false">

            <EmbedContactsView @click.stop @clickClose="showPreview = false"
                               :contact="contact" :officialEmployment="contact.officialEmployment || null" :locale="locale"></EmbedContactsView>

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
import SingleSelector from './SingleSelector';
import Modal from './Modal';
import EmbedContactsView from "./EmbedContactsView.vue";

export default {
    data() {
        return {
            locale: 'de',
            contact: {
                type: 'person',
                companyName: '',
                specification: '',
                gender: 'male',
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
            originalContactGroups: [],
            newContactGroups: [],
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
            getEmploymentById: 'employments/getById',
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
                                this.$router.push('/contacts/person');
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
        clickCancel() {
            this.$router.push('/contacts/person');
        },
        async clickSave() {
            let contact = { ...this.contact };

            if (this.contact.id) {
                contact = await this.$store.dispatch('contacts/update', this.contact)

            } else {
                contact = await this.$store.dispatch('contacts/create', this.contact)
            }

            if(contact.officialEmployment) {

                let officialEmployment = { ...this.contact.officialEmployment };
                officialEmployment.employee = contact;

                for(let contactGroup of contact.contactGroups) {
                    if(this.newContactGroups.find(group => group.id === contactGroup.id)) {

                        let group = this.contact.contactGroups.find(group => group.id === contactGroup.id);

                        group.employments.push(officialEmployment);
                        group.contacts.push(contact);

                        this.$store.dispatch('contactGroups/update', group);
                    }
                }
            }

            this.$router.push('/contacts/person')
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
        clickRemoveEmployment(employmentId, index) {
            let employment = this.contact.employments.splice(index, 1)[0];

            if (this.contact.officialEmployment?.id === employmentId) {
                this.contact.officialEmployment = null;
            }
        },
        clickSetOfficialEmployment(event) {
            if (event.target.checked) {
                this.contact.officialEmployment = this.getEmploymentById(event.target.value);
            } else {
                this.contact.officialEmployment = null;
            }
        },
        async reload() {
            if (this.$route.params.id) {
                this.$store.commit('contacts/set', {});

                let contactsData = await this.$store.dispatch('contactsData/loadFiltered', {
                    contactType: 'employee',
                    params: {ids: [this.$route.params.id]},
                });

                this.contact = { ...contactsData.contacts[0] };

                this.originalContactGroups = [ ...this.contact.contactGroups ];

                this.contact.employments = contactsData.employments;

                this.contact.officialEmployment = this.contact.employments.find(employment => employment.id === this.contact.officialEmployment?.id) || null;
            }
        },
        translate(property, context) {
            if (this.locale === 'de') {
                return context[property] || context.translations.fr[property] || context.translations.it[property];
            }
            if (this.locale === 'fr') {
                return context.translations.fr[property] || context[property] || context.translations.it[property];
            }
            if (this.locale === 'it') {
                return context.translations.it[property] || context.translations.fr[property] || context[property];
            }
            return context[property];
        },
        addContactGroups(groups) {
            this.newContactGroups = groups.filter(group => !this.originalContactGroups.map(originalGroup => originalGroup.id).includes(group.id));
        },
    },
    created() {
        this.reload();
        this.$store.dispatch('contacts/loadFiltered', {type: ['company']});
    }
}
</script>