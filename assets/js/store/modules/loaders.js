// initial state
const state = () => ({
    all: [],
});

// getters
const getters = {

    isLoading: (state) => (identifier = '*') => {
        if(identifier === '*') {
            return !!state.all.length;
        }
        if(identifier && identifier.slice(-1) === '*') {
            let result = false;
            state.all.forEach((activeLoader) => {
                if(activeLoader.startsWith(identifier.slice(0, -1))) {
                    result = true;
                }
            });
            return result;
        }
        return state.all.indexOf(identifier) !== -1;
    },

};

// actions
const actions = {};

// mutations
const mutations = {

    showLoader (state, identifier = 'default') {
        state.all.push(identifier);
    },

    hideLoader (state, identifier = 'default') {
        state.all.splice(state.all.indexOf(identifier), 1);
    },

    clearLoader (state, identifier = 'default') {
        state.all = state.all.filter(loader => loader !== identifier);
    },

    toggleLoader (state, identifier = 'default') {
        if(!getters.isLoading(state)(identifier)) {
            return this.showLoader(state, identifier);
        }
        this.clearLoader(state, identifier);
    },

};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};