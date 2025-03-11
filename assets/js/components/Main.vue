<template>
    <div>
        <Notifications />
        <router-view v-slot="{ Component }">
            <component :is="Component"></component>
        </router-view>
    </div>
</template>

<script>
import Notifications from './Notifications.vue';

export default {
    components: {
        Notifications
    }
}
</script>