<template>

    <div class="interactive-graphics-component">

        <div class="interactive-graphics-component-form">

            <div class="interactive-graphics-component-form-header">

                <h3>Interaktive Grafiken</h3>

                <div class="interactive-graphics-component-form-header-actions">
                    <router-link :to="'/interactive-graphics/add'" class="button primary">Neue Grafik erstellen</router-link>
                </div>

            </div>

        </div>

        <div class="interactive-graphics-component-list">

            <router-link class="interactive-graphics-component-list-item"
                         v-for="interactiveGraphic in interactiveGraphics"
                         tag="div"
                         :to="'/interactive-graphics/'+interactiveGraphic.id+'/edit'"
            >

                <div class="interactive-graphics-component-list-item-name">
                    <h3>{{ interactiveGraphic.title }} <span class="material-icons" v-if="interactiveGraphic.isDynamic">auto_fix_high</span></h3>
                    <p v-if="interactiveGraphic.description">{{ interactiveGraphic.description }}</p>
                </div>

                <div class="interactive-graphics-component-list-item-image" v-html="interactiveGraphic.svg"></div>

            </router-link>

        </div>

    </div>

</template>

<script>
    import { mapState, mapGetters } from 'vuex';

    export default {
        data () {
            return {};
        },
        computed: {
            ...mapState({
                interactiveGraphics: state => state.interactiveGraphics.all,
            }),
            ...mapGetters({
                getInteractiveGraphicById: 'interactiveGraphics/getById',
            }),
        },
        created () {
            this.$store.dispatch('interactiveGraphics/loadAll');
        },
        methods: {
        },
    }
</script>