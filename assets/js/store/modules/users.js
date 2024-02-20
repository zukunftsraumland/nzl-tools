import api from '../../api';

// initial state
const state = () => ({
    all: [],
    user: {},
    me: {},
});

// getters
const getters = {

    getById: (state) => (id) => {
        return state.all.find(item => item.id === id);
    },

    hasRole: (state) => (role) => {
        return state.me.roles && state.me.roles.indexOf(role) !== -1;
    },

};

// actions
const actions = {

    loadMe ({ commit }) {
        commit('loaders/showLoader', 'users/me', { root: true });
        return api.users.getMe().then((response) => {
            commit('loaders/hideLoader', 'users/me', { root: true });
            commit('setMe', response.data);
            return response.data;
        });
    },

    loadAll ({ commit }) {
        commit('loaders/showLoader', 'users', { root: true });
        return api.users.getAll().then((response) => {
            commit('loaders/hideLoader', 'users', { root: true });
            commit('setAll', response.data);
        });
    },

    load ({ commit }, id) {
        commit('loaders/showLoader', 'users/'+id, { root: true });
        return api.users.get(id).then((response) => {
            commit('loaders/hideLoader', 'users/'+id, { root: true });
            commit('set', response.data);
            return response.data;
        });
    },

    create ({ commit }, payload) {
        commit('loaders/showLoader', 'users/create', { root: true });
        return api.users.create(payload).then((response) => {
            commit('loaders/hideLoader', 'users/create', { root: true });
            commit('insert', response.data);
            commit('set', response.data);
        });
    },

    update ({ commit }, payload) {
        commit('loaders/showLoader', 'users/'+payload.id, { root: true });
        return api.users.update(payload.id, payload).then((response) => {
            commit('loaders/hideLoader', 'users/'+payload.id, { root: true });
            commit('update', response.data);
            commit('set', response.data);
        });
    },

    delete ({ commit }, id) {
        commit('loaders/showLoader', 'users/'+id, { root: true });
        return api.users.delete(id).then((response) => {
            commit('loaders/hideLoader', 'users/'+id, { root: true });
            commit('remove', id);
        });
    },

};

// mutations
const mutations = {

    setAll (state, users) {
        state.all = users;
    },

    set (state, user) {
        if(user) {
            user = {
                ...user,
                translations: typeof user.translations === 'object' && user.translations !== null && !Array.isArray(user.translations) ? user.translations : {},
            };
        }
        state.user = user;
    },

    setMe (state, user) {
        state.me = user;
    },

    insert (state, user) {
        state.all = [...state.all, user];
    },

    update (state, user) {
        let existingUser = state.all.find(p => p.id === user.id);
        if(existingUser) {
            state.all[state.all.indexOf(existingUser)] = user;
        }
    },

    remove (state, id) {
        state.all = state.all.filter((user) => {
            return parseInt(user.id) !== parseInt(id);
        });
    },

};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};