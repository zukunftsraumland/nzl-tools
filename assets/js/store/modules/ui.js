// initial state
const state = () => ({
    isSidebarCollapsed: JSON.parse(window.localStorage.getItem('pv.ui.isSidebarCollapsed') || 'false'),
});

// getters
const getters = {};

// actions
const actions = {};

// mutations
const mutations = {

    setIsSidebarCollapsed (state, isSidebarCollapsed) {
        state.isSidebarCollapsed = isSidebarCollapsed;
        window.localStorage.setItem('pv.ui.isSidebarCollapsed', JSON.stringify(isSidebarCollapsed));
    },

};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};