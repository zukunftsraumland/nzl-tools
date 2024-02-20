import api from '../../api';

// initial state
const state = () => ({
    all: [],
    filtered: [],
    job: {},
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
        commit('loaders/showLoader', 'jobs', { root: true });
        return api.jobs.getAll().then((response) => {
            commit('loaders/hideLoader', 'jobs', { root: true });
            commit('setAll', response.data);
            return response.data;
        });
    },

    loadFiltered ({ commit }, params) {
        commit('loaders/showLoader', 'jobs', { root: true });
        return api.jobs.getFiltered(params).then((response) => {
            commit('loaders/hideLoader', 'jobs', { root: true });
            commit('setFiltered', response.data);
            return response.data;
        });
    },

    load ({ commit }, id) {
        commit('loaders/showLoader', 'jobs/'+id, { root: true });
        return api.jobs.get(id).then((response) => {
            commit('loaders/hideLoader', 'jobs/'+id, { root: true });
            commit('set', response.data);
            return response.data;
        });
    },

    create ({ commit }, payload) {
        commit('loaders/showLoader', 'jobs/create', { root: true });
        return api.jobs.create(payload).then((response) => {
            commit('loaders/hideLoader', 'jobs/create', { root: true });
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
        commit('loaders/showLoader', 'jobs/'+payload.id, { root: true });
        return api.jobs.update(payload.id, payload).then((response) => {
            commit('loaders/hideLoader', 'jobs/'+payload.id, { root: true });
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
        commit('loaders/showLoader', 'jobs/'+id, { root: true });
        return api.jobs.delete(id).then((response) => {
            commit('loaders/hideLoader', 'jobs/'+id, { root: true });
            commit('remove', id);
        });
    },

};

// mutations
const mutations = {

    setAll (state, jobs) {
        state.all = jobs;
    },

    setFiltered (state, filtered) {
        state.filtered = filtered;
    },

    set (state, job) {
        if(job) {
            job = {
                ...job,
                translations: typeof job.translations === 'object' && job.translations !== null && !Array.isArray(job.translations) ? job.translations : {},
            };
        }
        state.job = job;
    },

    insert (state, job) {
        state.all = [...state.all, job];
    },

    update (state, job) {
        let existingJob = state.all.find(p => p.id === job.id);
        if(existingJob) {
            state.all[state.all.indexOf(existingJob)] = job;
        }
    },

    remove (state, id) {
        state.all = state.all.filter((job) => {
            return parseInt(job.id) !== parseInt(id);
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