<template>

    <div class="financial-supports-component">

        <div class="financial-supports-component-title">

            <h2>Finanzhilfen</h2>

            <transition name="fade" mode="out-in">
                <div class="loading-indicator" v-if="isLoading('financialSupports')"></div>
            </transition>

            <div class="financial-supports-component-title-actions">
                <router-link :to="'/financial-supports/add'" class="button primary">Neuen Eintrag erstellen</router-link>
            </div>

        </div>

        <div class="financial-supports-component-content">

            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Bezeichnung</th>
                        <th>Förderstellen</th>
                        <th>Laufzeit</th>
                    </tr>
                </thead>
                <tbody v-if="!financialSupports.length && isLoading('financialSupports')">
                    <tr>
                        <td colspan="11"><em>Einträge werden geladen...</em></td>
                    </tr>
                </tbody>
                <draggable v-else :list="financialSupports" :tag="'tbody'" item-key="id" @change="changeSort">
                    <template #item="{element}">
                        <tr class="clickable"
                            @click="clickFinancialSupport(element)"
                            :class="{'warning': !element.isPublic}">
                            <td>{{ element.id }}</td>
                            <td>{{ translateField(element, 'name', 'de') }}</td>
                            <td>{{ formatOneToMany(element.authorities, getAuthorityById) }}</td>
                            <td>{{ $helpers.formatDate(element.startDate) }} - {{ $helpers.formatDate(element.endDate) }}</td>
                        </tr>
                    </template>
                </draggable>
            </table>

        </div>

        <transition name="fade" mode="in-out">

            <div class="context-bar" v-if="isSortChanged">
                <div class="context-bar-content">
                    <p v-if="!isLoading('financialSupports/*')">Sortierung geändert. Möchten Sie die Änderungen speichern?</p>
                    <p v-else>{{ sortChangeProgress }} von {{ financialSupports.length }} Positionen gespeichert...</p>
                </div>
                <template v-if="!isLoading('financialSupports/*')">
                    <a class="button warning" @click="clickRestoreSort()">Zurücksetzen</a>
                    <a class="button success" @click="clickSaveSort()">Speichern</a>
                </template>
            </div>

        </transition>

    </div>

</template>

<script>
    import { mapState, mapGetters } from 'vuex';
    import { translateField } from '../utils/filters';
    import draggable from 'vuedraggable';

    export default {
        data () {
            return {
                term: '',
                filters: [],
                isSortChanged: false,
                sortChangeProgress: 0,
            };
        },
        components: {
            draggable,
        },
        computed: {
            ...mapState({
                financialSupports: state => state.financialSupports.all,
            }),
            ...mapGetters({
                isLoading: 'loaders/isLoading',
                getAuthorityById: 'authorities/getById',
                getBeneficiaryById: 'beneficiaries/getById',
                getTopicById: 'topics/getById',
                getProjectTypeById: 'projectTypes/getById',
                getInstrumentById: 'instruments/getById',
                getGeographicRegionById: 'geographicRegions/getById',
            }),
        },
        methods: {
            translateField,
            reloadFinancialSupports () {
                return this.$store.dispatch('financialSupports/loadAll');
            },
            clickFinancialSupport (financialSupport) {
                this.$router.push({
                    path: '/financial-supports/'+financialSupport.id+'/edit'
                });
            },
            formatOneToMany (items, getter, limit = 3) {
                let result = [];
                items.forEach((item) => {
                    result.push(getter(item.id)?.name);
                });

                return result.join(', ');
            },
            changeSort() {
                this.isSortChanged = true;
            },
            async clickSaveSort() {
                this.sortChangeProgress = 0;
                for(let key in this.financialSupports) {
                    await this.$store.dispatch('financialSupports/update', {
                        ...this.financialSupports[key],
                        position: key,
                    });
                    this.sortChangeProgress++;
                }
                this.isSortChanged = false;
                this.reloadFinancialSupports();
            },
            clickRestoreSort() {
                this.isSortChanged = false;
                this.reloadFinancialSupports();
            },
        },
        created () {
            this.reloadFinancialSupports();
        },
    }
</script>