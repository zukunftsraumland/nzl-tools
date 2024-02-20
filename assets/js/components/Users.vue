<template>

    <div class="users-component">

        <div class="users-component-title">

            <h2>Benutzerverwaltung</h2>

            <div class="users-component-title-actions">
                <router-link :to="'/settings/users/add'" class="button primary">Neuen Benutzer erstellen</router-link>
            </div>

        </div>

        <div class="users-component-content">

            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>E-Mail</th>
                        <th>Erstellt am</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users"
                        class="clickable"
                        @click="clickUser(user)">
                        <td>{{ user.id }}</td>
                        <td>{{ user.email }}</td>
                        <td>{{ formatDateTime(user.createdAt) }}</td>
                    </tr>
                </tbody>
            </table>

        </div>

    </div>

</template>

<script>
    import { mapState } from 'vuex';
    import moment from 'moment';

    export default {
        data () {
            return {
            };
        },
        computed: {
            ...mapState({
                users: state => state.users.all,
            }),
        },
        created () {
            this.$store.dispatch('users/loadAll');
        },
        methods: {
            clickUser (user) {
                this.$router.push({
                    path: '/settings/users/'+user.id+'/edit'
                });
            },
            formatDateTime(date) {
                if(date && moment(date)) {
                    return moment(date).format('DD.MM.YYYY HH:mm');
                }
            },
        },
    }
</script>