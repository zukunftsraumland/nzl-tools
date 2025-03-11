// initial state
const state = () => ({
    notifications: []
});

// getters
const getters = {
    all: state => state.notifications
};

// actions
const actions = {
    add({ commit }, notification) {
        // Generate a unique ID for the notification
        const id = Date.now();
        commit('add', { ...notification, id });
        
        // Auto-remove notifications after 5 seconds
        setTimeout(() => {
            commit('remove', id);
        }, 5000);
    },
    
    remove({ commit }, id) {
        commit('remove', id);
    }
};

// mutations
const mutations = {
    add(state, notification) {
        state.notifications.push(notification);
    },
    
    remove(state, id) {
        state.notifications = state.notifications.filter(notification => notification.id !== id);
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}; 