<template>
        <router-view v-slot="{ Component }">
            <Notifications />

            <component :is="Component"></component>
        </router-view>
</template>

<script>
import Notifications from './Notifications.vue';

export default {
    components: {
        Notifications
    }
}
</script>