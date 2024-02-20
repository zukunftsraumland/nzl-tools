<template>

    <div class="interactive-graphic-component">

        <div class="interactive-graphic-component-form">

            <div class="interactive-graphic-component-form-header">

                <h3>Interaktive Grafik erstellen</h3>

                <div class="interactive-graphic-component-form-header-actions">
                    <a class="button error" @click="clickDelete()" v-if="interactiveGraphic.id">Löschen</a>
                    <a class="button warning" @click="clickCancel()">Abbrechen</a>
                    <a class="button primary" @click="clickSave()">Speichern</a>
                </div>

            </div>

            <div class="interactive-graphic-component-form-section">

                <div class="row">
                    <div class="col-md-6">
                        <label for="title">Name</label>
                        <input id="title" type="text" class="form-control" v-model="interactiveGraphic.title">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label for="description">Beschreibung</label>
                        <textarea name="description" id="description" class="form-control" rows="4" v-model="interactiveGraphic.description"></textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <label for="copyright">Copyright</label>
                        <input id="copyright" type="text" class="form-control" v-model="interactiveGraphic.copyright">
                    </div>
                </div>

                <div class="row" v-if="interactiveGraphic.svg && interactiveGraphic.selector">
                    <div class="col-md-12">
                        <interactive-graphic-editor
                            :svg="interactiveGraphic.svg"
                            :selector="interactiveGraphic.selector"
                            :config="interactiveGraphic.config"
                            @onChangeConfig="onChangeConfig($event)"
                        ></interactive-graphic-editor>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <label for="svg">SVG Code</label>
                        <textarea name="svg" id="svg" class="form-control" rows="4" v-model="interactiveGraphic.svg"></textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label for="selector">CSS Selektor</label>
                        <input id="selector" type="text" class="form-control" v-model="interactiveGraphic.selector">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label for="start">Start Element (CSS Selektor)</label>
                        <input id="start" type="text" class="form-control" v-model="interactiveGraphic.start">
                    </div>
                </div>

            </div>

        </div>

    </div>

</template>

<script>
    import { mapState } from 'vuex';
    import draggable from 'vuedraggable';
    import InteractiveGraphicEditor from './InteractiveGraphicEditor';

    export default {
        data() {
            return {
                previewProject: null,
                term: '',
                interactiveGraphic: {
                    title: '',
                    description: '',
                    selector: '',
                    start: '',
                    copyright: '',
                    svg: '',
                    config: {},
                }
            };
        },
        components: {
            draggable,
            InteractiveGraphicEditor,
        },
        computed: {
            ...mapState({
                selectedInteractiveGraphic: state => state.interactiveGraphics.interactiveGraphic,
            }),
        },
        methods: {
            onChangeConfig (event) {
                this.interactiveGraphic.config = event;
            },
            clickDelete () {
                if(!confirm('Sind Sie sicher dass Sie diesen Eintrag unwiderruflich löschen möchten?')) {
                    return;
                }
                this.$store.dispatch('interactiveGraphics/delete', this.interactiveGraphic.id).then(() => {
                    this.$router.push('/interactive-graphics');
                });
            },
            clickCancel () {
                this.$router.push('/interactive-graphics');
            },
            clickSave() {
                if(!this.interactiveGraphic.title.trim()) {
                    return alert('Geben Sie bitte einen Titel an um die Kollektion zu speichern.');
                }
                if(!this.interactiveGraphic.svg.trim()) {
                    return alert('Fügen Sie bitte eine SVG Grafik ein.');
                }
                if(!this.interactiveGraphic.selector.trim()) {
                    return alert('Geben Sie bitte einen CSS Selektor an.');
                }
                if(!Object.keys(this.interactiveGraphic.config).length) {
                    return alert('Geben Sie bitte mindestens 1 interaktives Element an.');
                }

                if(this.interactiveGraphic.id) {
                    return this.$store.dispatch('interactiveGraphics/update', this.interactiveGraphic).then(() => {
                        this.$router.push('/interactive-graphics');
                    });
                }

                this.$store.dispatch('interactiveGraphics/create', this.interactiveGraphic).then(() => {
                    this.$router.push('/interactive-graphics');
                });
            },
            reload() {
                if(this.$route.params.id) {
                    this.$store.commit('interactiveGraphics/set', {});
                    this.$store.dispatch('interactiveGraphics/load', this.$route.params.id).then(() => {
                        this.interactiveGraphic = {...this.selectedInteractiveGraphic};
                    });
                }
            },
        },
        created () {
            this.reload();
        }
    }
</script>