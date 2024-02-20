<template>

    <div class="modal-component">
        <div class="modal-component-modal">
            <h1>{{ config.title }}</h1>
            <p>{{ config.description }}</p>
            <div class="modal-component-modal-actions">
                <a class="button" v-for="action in config.actions" :class="action.class" @click="action.onClick">
                    {{ action.label }}
                </a>
            </div>
        </div>
    </div>

</template>

<script>
    export default {
        props: {
            config: {
                type: Object,
                default: {},
            },
        },
        data () {
            return {};
        },
        methods: {},
        created () {},
    }
</script>