import api from '../../api';

// initial state
const state = () => ({
    all: [], // Store for LE structure
});

// getters
const getters = {
    // Find LE Period by ID
    getPeriodById: (state) => (id) => {
        return state.all.find(period => period.id === id);
    },

    // Find LE Category by ID within a specific period
    getCategoryById: (state) => (id) => {
        let foundCategory = null;
        state.all.forEach(period => {
            const category = period.categories.find(cat => cat.id === id);
            if (category) {
                foundCategory = category;
            }
        });
        return foundCategory;
    },

    // Find LE Article by ID within a specific category
    getArticleById: (state) => (id) => {
        let foundArticle = null;
        state.all.forEach(period => {
            period.categories.forEach(category => {
                const article = category.articles.find(art => art.id === id);
                if (article) {
                    foundArticle = article;
                }
            });
        });
        return foundArticle;
    },

    // Find LE Method by ID within a specific article
    getMethodById: (state) => (id) => {
        let foundMethod = null;
        state.all.forEach(period => {
            period.categories.forEach(category => {
                category.articles.forEach(article => {
                    const method = article.methods.find(meth => meth.id === id);
                    if (method) {
                        foundMethod = method;
                    }
                });
            });
        });
        return foundMethod;
    },
};

// actions
const actions = {
    loadAll({ commit }) {
        return api.leStructure.getAll().then((response) => {
            commit('setAll', response.data); // Load all LE structure and commit to the store
        });
    },
};

// mutations
const mutations = {
    setAll(state, leStructure) {
        state.all = leStructure; // Mutate the state with the LE structure data
    },
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
};
