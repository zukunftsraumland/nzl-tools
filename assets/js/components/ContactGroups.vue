<template>

    <div class="contact-groups-component">

        <div class="contact-groups-component-title">

            <h2>Kontaktgruppen</h2>

            <transition name="fade" mode="out-in">
                <div class="loading-indicator" v-if="isLoading('contactGroups')"></div>
            </transition>

            <div class="contact-groups-component-title-actions">
                <router-link :to="'/contact-groups/add'" class="button primary">Neuen Eintrag erstellen</router-link>
            </div>

        </div>

        <div class="contact-groups-component-filter">

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
                            <select id="status" class="form-control" @change="addFilter({type: 'status', value: $event.target.value}); $event.target.value = null;">
                                <option></option>
                                <option :value="'public'">Öffentlich</option>
                                <option :value="'draft'">Entwurf</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="contacts-component-filter-tags">
                <div class="tag" v-for="filter of filters" @click="removeFilter({type: filter.type, value: filter.value})">
                    <strong v-if="filter.type === 'status'">Status:</strong>
                    <template v-if="['status'].includes(filter.type)">
                        &nbsp;{{ filter.value === 'public' ? 'Öffentlich' : 'Entwurf' }}
                    </template>
                    <template v-else>
                        {{filter.value}}
                    </template>
                </div>
            </div>

        </div>

        <div class="contact-groups-component-content">

            <div class="contact-groups-component-tree-structure">

                <draggable :list="contactGroupsElements" :tag="'ul'" class="contact-groups-component-tree-structure-level-parent" item-key="position" @change="changeSort" :disabled="!isDraggableEnabled">
                    <template #item="{ element, index }">
                        <li :class="{ draft: !element.group.isPublic, clickable: isDraggableEnabled }">

                            <div class="contact-groups-component-tree-structure-container">

                                <h2 class="contact-groups-component-tree-structure-container-label" @click="clickContactGroup(element.group)">
                                    <span class="material-icons">folder</span>
                                    {{ element.group.name }}
                                </h2>

                                <div class="contact-groups-component-tree-structure-container-actions">
                                    <a class="button" :href="getExportURL(element.group.id)" download v-if="getContactSubGroups(element.group.id)?.length && selectedElements[element.group.id]?.length"><span class="material-icons">file_download</span></a>
                                    <span class="material-icons" @click.stop="clickContactGroup(element.group)">edit</span>
                                </div>

                            </div>

                            <draggable :list="element.children" :tag="'ul'" class="contact-groups-component-tree-structure-level-child" item-key="id" @change="changeSort" v-if="element.children?.length" :disabled="!isDraggableEnabled">
                                <template #item="{ element: childElement, index: childIndex }">

                                    <li :class="{ clickable: isDraggableEnabled }">

                                        <div class="contact-groups-component-tree-structure-container child">

                                            <h3 class="contact-groups-component-tree-structure-container-label" @click="clickContactGroup(childElement)">
                                                <span class="material-icons">groups</span>
                                                {{ childElement.name }}
                                            </h3>

                                            <div class="contact-groups-component-tree-structure-container-contact-actions">
                                                <input id="active" type="checkbox" :class="{ disabled: !childElement.contacts?.length }"
                                                       :checked="selectedElements[element.group.id]?.find(group => group.id === childElement.id)" @change="clickToggleSelected($event, childElement, element.group.id, childElement.id)">
                                                <span class="material-icons" @click.stop="clickContactGroup(childElement)">edit</span>
                                                <!--span class="material-icons clear" @click="clickDeleteContactGroup(childElement)">clear</span-->
                                                <i v-if="childElement.contacts?.length">[ Mitglieder: {{ childElement.contacts.length }} ]</i>
                                                <i v-else>[ Keine Mitglieder ]</i>
                                            </div>

                                        </div>

                                    </li>

                                </template>
                            </draggable>

                        </li>
                    </template>
                </draggable>

            </div>

        </div>

        <transition name="fade" mode="in-out">

            <div class="context-bar" v-if="isSortChanged">
                <div class="context-bar-content">
                    <p v-if="!isLoading('contactGroups/*')">Sortierung geändert. Möchten Sie die Änderungen speichern?</p>
                    <p v-else>{{ sortChangeProgress }} von {{ contactGroupsAll.length }} Positionen gespeichert...</p>
                </div>
                <template v-if="!isLoading('contactGroups/*')">
                    <a class="button warning" @click="clickRestoreSort()">Zurücksetzen</a>
                    <a class="button success" @click="clickSaveSort()">Speichern</a>
                </template>
            </div>

        </transition>

    </div>

</template>

<script>
import { mapGetters, mapState } from 'vuex';
import moment from 'moment';
import draggable from "vuedraggable";

export default {
    data () {
        return {
            position: 10000,
            term: '',
            filters: [],
            selectedElements: {},
            isSortChanged: false,
            sortChangeProgress: 0,
            contactGroupsElements: [],
        };
    },
    components: {
        draggable,
    },
    computed: {
        ...mapState({
            contactGroups: state => state.contactGroups.filtered,
            contactGroupsAll: state => state.contactGroups.all,
            contacts: state => state.contacts.all,
        }),
        ...mapGetters({
            isLoading: 'loaders/isLoading',
            getContactById: 'contacts/getById',
        }),
        isDraggableEnabled() {
            return !this.filters?.length && !this.term;
        },
    },
    methods: {
        sortByPosition(a, b) {
            let  contactGroupA = a.position;
            let  contactGroupB = b.position;
            return (contactGroupA < contactGroupB) ? -1 : (contactGroupA > contactGroupB) ? 1 : 0;
        },
        changeForm () {
            this.saveFilter();
            this.reloadContactGroups();
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

            params.orderBy = ['position', 'name'];
            params.orderDirection = ['ASC', 'ASC'];

            return params;
        },
        reloadContactGroups () {
            this.isSortChanged = false;

            this.$store.dispatch('contactGroups/loadFiltered', this.getFilterParams()).then((contactGroups) => {

                this.contactGroupsElements = [];
                let contactGroupsParents = contactGroups.filter(group => !group.parent);

                for(let contactGroupParent of contactGroupsParents) {
                    let contactGroupsChildren = this.getContactSubGroups(contactGroupParent.id);

                    this.contactGroupsElements.push({
                        group: contactGroupParent,
                        children: contactGroupsChildren,
                    })
                }
            });
        },
        getContactSubGroups(contactGroupId) {
            return this.contactGroupsAll.filter(group => group.parent?.id === contactGroupId)?.sort(this.sortByPosition) || [];
        },
        clickToggleSelected(event, element, groupIndex, subgroupIndex) {
            let isSelected = event.target.checked;

            delete element.activeContactGroup;

            if(!this.selectedElements[groupIndex]) {
                this.selectedElements[groupIndex] = [];
            }

            if(!isSelected) {
                let elementIndex = this.selectedElements[groupIndex].indexOf(element);

                if(elementIndex !== null) {
                    this.selectedElements[groupIndex].splice(elementIndex, 1);
                }

                return;
            }

            element.activeContactGroup = subgroupIndex;

            this.selectedElements[groupIndex].push(element);
        },
        validateSelectedElements(groupIndex) {
            if(!this.selectedElements[groupIndex]?.length) {
                alert('Wählen Sie eine oder mehrere Kontaktgruppen zur weiteren Bearbeitung.');
                return false;
            }

            return true;
        },
        clickContact (contact) {
            this.$router.push({
                path: '/contacts/'+contact.type+'/'+contact.id+'/edit'
            });
        },
        clickContactGroup (contactGroup) {
            this.$router.push({
                path: '/contact-groups/'+contactGroup.id+'/edit'
            });
        },
        /*clickDeleteContactGroup (contactGroup) {
            if(window.confirm('Sind Sie sicher, dass Sie die Kontaktgruppe "ID '+contactGroup.id+': '+contactGroup.name+'" löschen möchten?')) {
                this.$store.dispatch('contactGroups/delete', contactGroup.id);
            }
        },
        clickRemoveContactGroups (parentGroup) {
            if(!this.validateSelectedElements(parentGroup.id)) {
                return;
            }

            if(window.confirm('Sind Sie sicher, dass Sie die markierten Kontaktgruppen aus der Kontaktgruppe "ID '+parentGroup.id+': '+parentGroup.name+'" entfernen möchten?')) {
                for(let contactGroup of this.selectedElements[parentGroup.id]) {
                    contactGroup.parent = null;
                    this.$store.dispatch('contactGroups/update', contactGroup);
                }
            }
        },*/
        getExportURL(groupIndex) {
            if(!this.validateSelectedElements(groupIndex)) {
                return;
            }

            if(!this.selectedElements[groupIndex]?.length) {
                return;
            }

            let filterParams = [];

            for(let element of this.selectedElements[groupIndex]) {

                if(element.contacts?.length) {

                    for(let contact of element.contacts) {

                        if(!filterParams.includes('contactGroupIds[]='+element.activeContactGroup)) {
                            filterParams.push('contactGroupIds[]='+element.activeContactGroup);
                        }
                    }
                }
            }

            return '/api/v1/contacts.xlsx/contact-groups?'+filterParams.join('&');
        },
        formatOneToMany (items, getter) {
            let result = [];
            items.forEach((item) => {
                result.push(getter(item.id)?.name);
            });

            return result.join(', ');
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
            window.sessionStorage.setItem('regiosuisse.contactGroups.filters', JSON.stringify(this.filters));
            window.sessionStorage.setItem('regiosuisse.contactGroups.term', this.term);

            this.selectedElements = {};
        },
        loadFilter () {
            this.filters = JSON.parse(window.sessionStorage.getItem('regiosuisse.contactGroups.filters') || '[]');
            this.term = window.sessionStorage.getItem('regiosuisse.contactGroups.term') || '';
        },
        changeSort() {
            this.isSortChanged = true;
        },
        async clickSaveSort() {
            this.sortChangeProgress = 0;

            for(let key in this.contactGroupsElements) {
                await this.$store.dispatch('contactGroups/update', {
                    ...this.contactGroupsElements[key].group,
                    position: key,
                });
                this.sortChangeProgress++;

                for(let childKey in this.contactGroupsElements[key].children) {

                    await this.$store.dispatch('contactGroups/update', {
                        ...this.contactGroupsElements[key].children[childKey],
                        position: childKey,
                    });
                    this.sortChangeProgress++;
                }
            }

            this.isSortChanged = false;

            this.reloadContactGroups();
        },
        clickRestoreSort() {
            this.isSortChanged = false;
            this.reloadContactGroups();
        },
    },
    created () {
        this.loadFilter();
        this.reloadContactGroups();
        this.$store.dispatch('contacts/loadAll');
    },
}
</script>