<template>

    <div class="user-component">

        <div class="user-component-form">

            <div class="user-component-form-header">

                <h3>Benutzer erstellen</h3>

                <div class="user-component-form-header-actions">
                    <a class="button error" @click="clickDelete()" v-if="user.id">Löschen</a>
                    <a class="button warning" @click="clickCancel()">Abbrechen</a>
                    <a class="button primary" @click="clickSave()">Speichern</a>
                </div>

            </div>

            <div class="user-component-form-section">

                <div class="row">
                    <div class="col-md-6">
                        <label for="email">E-Mail</label>
                        <input id="email" type="email" class="form-control" v-model="user.email">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label for="password">Passwort</label>
                        <input id="password" type="password" class="form-control" v-model="user.password" :placeholder="user.id ? 'Passwort ändern' : ''">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <table class="table">
                            <tr>
                                <th width="20%">Rolle</th>
                                <th width="60%">Beschreibung</th>
                                <th>Status</th>
                            </tr>
                            <tr>
                                <td>Redakteur</td>
                                <td>Inhalte erstellen, bearbeiten und löschen.</td>
                                <td>
                                    <a class="button success"
                                       :class="{error: !user.roles.includes('ROLE_EDITOR')}"
                                       @click="!user.roles.includes('ROLE_EDITOR') ? user.roles.push('ROLE_EDITOR') : user.roles.splice(user.roles.indexOf('ROLE_EDITOR'), 1)">
                                        <span v-if="user.roles.includes('ROLE_EDITOR')">Aktiv</span>
                                        <span v-else>Inaktiv</span>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>Admin</td>
                                <td>Administrative Einstellungen und Benutzerverwaltung.</td>
                                <td>
                                    <a class="button success"
                                       :class="{error: !user.roles.includes('ROLE_ADMIN')}"
                                       @click="!user.roles.includes('ROLE_ADMIN') ? user.roles.push('ROLE_ADMIN') : user.roles.splice(user.roles.indexOf('ROLE_ADMIN'), 1)">
                                        <span v-if="user.roles.includes('ROLE_ADMIN')">Aktiv</span>
                                        <span v-else>Inaktiv</span>
                                    </a>
                                </td>
                            </tr>
                            <tr v-if="hasRole('ROLE_SUPER_ADMIN')">
                                <td>Super-Admin</td>
                                <td>Vollständiger Zugriff auf das System.</td>
                                <td>
                                    <a class="button success"
                                       :class="{error: !user.roles.includes('ROLE_SUPER_ADMIN')}"
                                       @click="!user.roles.includes('ROLE_SUPER_ADMIN') ? user.roles.push('ROLE_SUPER_ADMIN') : user.roles.splice(user.roles.indexOf('ROLE_SUPER_ADMIN'), 1)">
                                        <span v-if="user.roles.includes('ROLE_SUPER_ADMIN')">Aktiv</span>
                                        <span v-else>Inaktiv</span>
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <table class="table">
                            <tr>
                                <th width="20%">Benachrichtigung</th>
                                <th width="60%">Beschreibung</th>
                                <th>Status</th>
                            </tr>
                            <tr>
                                <td>CHMOS Posteingang</td>
                                <td>Benachrichtigen bei neuen Projekten aus der CHMOS Schnittstelle.</td>
                                <td>
                                    <a class="button success"
                                       :class="{error: !user.notifications.includes('CHMOS_INBOX')}"
                                       @click="!user.notifications.includes('CHMOS_INBOX') ? user.notifications.push('CHMOS_INBOX') : user.notifications.splice(user.notifications.indexOf('CHMOS_INBOX'), 1)">
                                        <span v-if="user.notifications.includes('CHMOS_INBOX')">Aktiv</span>
                                        <span v-else>Inaktiv</span>
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

            </div>

        </div>

        <transition name="fade">
            <Modal v-if="modal" :config="modal"></Modal>
        </transition>

    </div>

</template>

<script>
    import Modal from './Modal';
    import {mapGetters} from 'vuex';

    export default {
        data() {
            return {
                user: {
                    email: '',
                    password: '',
                    notifications: [],
                    roles: [
                        'ROLE_USER',
                    ],
                },
                modal: null,
            };
        },
        components: {
            Modal,
        },
        computed: {
            ...mapGetters({
                hasRole: 'users/hasRole',
            }),
        },
        methods: {
            clickDelete () {
                this.modal = {
                    title: 'Benutzer löschen',
                    description: 'Sind Sie sicher dass Sie diesen Benutzer unwiderruflich löschen möchten?',
                    actions: [
                        {
                            label: 'Endgültig löschen',
                            class: 'error',
                            onClick: () => {
                                this.$store.dispatch('users/delete', this.user.id).then(() => {
                                    this.$router.push('/settings/users');
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
                this.$router.push('/settings/users');
            },
            clickSave() {

                if(this.user.id) {
                    return this.$store.dispatch('users/update', this.user).then(() => {
                        this.$router.push('/settings/users');
                    });
                }

                this.$store.dispatch('users/create', this.user).then(() => {
                    this.$router.push('/settings/users');
                });

            },
        },
        created () {
            if(!this.$route.params.id) {
                return;
            }

            this.$store.dispatch('users/load', this.$route.params.id)
                .then((user) => {
                    this.user = {...user}
                });
        }
    }
</script>