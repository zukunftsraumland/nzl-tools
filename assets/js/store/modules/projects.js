import api from '../../api';

// initial state
const state = () => ({
    all: [],
    filtered: [],
    project: {},
});

// getters
const getters = {

    getById: (state) => (id) => {
        return state.all.find(item => item.id === id);
    },

};

// actions
const actions = {

    loadAll ({ commit }) {
        commit('loaders/showLoader', 'projects', { root: true });
        return api.projects.getAll().then((response) => {
            commit('loaders/hideLoader', 'projects', { root: true });
            commit('setAll', response.data);
            return response.data;
        });
    },

    loadFiltered ({ commit }, params) {
        commit('loaders/showLoader', 'projects', { root: true });
        return api.projects.getFiltered(params).then((response) => {
            commit('loaders/hideLoader', 'projects', { root: true });
            commit('setFiltered', response.data);
            return response.data;
        });
    },

    load ({ commit }, id) {
        commit('loaders/showLoader', 'projects/'+id, { root: true });
        return api.projects.get(id).then((response) => {
            commit('loaders/hideLoader', 'projects/'+id, { root: true });
            commit('set', response.data);
            return response.data;
        });
    },

    create ({ commit }, payload) {
        commit('loaders/showLoader', 'projects/create', { root: true });
        return api.projects.create(payload).then((response) => {
            commit('loaders/hideLoader', 'projects/create', { root: true });
            if(payload.addToInbox) {
                if(payload.inboxId) {
                    commit('inbox/update', response.data, { root: true });
                } else {
                    commit('inbox/insert', response.data, { root: true });
                }
            } else {
                commit('insert', response.data);
                commit('set', response.data);
            }
        });
    },

    update ({ commit }, payload) {
        commit('loaders/showLoader', 'projects/'+payload.id, { root: true });
        return api.projects.update(payload.id, payload).then((response) => {
            commit('loaders/hideLoader', 'projects/'+payload.id, { root: true });
            if(payload.addToInbox) {
                if(payload.inboxId) {
                    commit('inbox/update', response.data, { root: true });
                } else {
                    commit('inbox/insert', response.data, { root: true });
                }
            } else {
                commit('update', response.data);
                commit('set', response.data);
            }
        });
    },

    delete ({ commit }, id) {
        commit('loaders/showLoader', 'projects/'+id, { root: true });
        return api.projects.delete(id).then((response) => {
            commit('loaders/hideLoader', 'projects/'+id, { root: true });
            commit('remove', id);
        });
    },

};

// mutations
const mutations = {

    setAll (state, projects) {
        state.all = projects;
    },

    setFiltered (state, filtered) {
        state.filtered = filtered;
    },

    set (state, project) {
        if(project) {
            project = {
                ...project,
                translations: typeof project.translations === 'object' && project.translations !== null && !Array.isArray(project.translations) ? project.translations : {},
            };
        }
        state.project = project;
    },

    insert (state, project) {
        state.all = [...state.all, project];
    },

    update (state, project) {
        let existingProject = state.all.find(p => p.id === project.id);
        if(existingProject) {
            state.all[state.all.indexOf(existingProject)] = project;
        }
        existingProject = state.filtered.find(p => p.id === project.id);
        if(existingProject) {
            state.filtered[state.filtered.indexOf(existingProject)] = project;
        }
    },

    remove (state, id) {
        state.all = state.all.filter((project) => {
            return parseInt(project.id) !== parseInt(id);
        });
        state.filtered = state.filtered.filter((project) => {
            return parseInt(project.id) !== parseInt(id);
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