import '../styles/embed-projects.scss';

import { createApp } from 'vue';
import env from './utils/env';
import i18n from './common/i18n';
import helpers from './utils/helpers';
import store from './store';

import EmbedProjects from './components/EmbedProjects';

const app = createApp(EmbedProjects);

app.use(env);
app.use(i18n);
app.use(helpers);
app.use(store);

const init = (selector, clientOptions = {}) => {

    app.use({
        install: (app, options) => {
            app.config.globalProperties.$clientOptions = {
                ...clientOptions,
            };
        }
    });

    app.mount(selector);

};

window[process.env.INSTANCE_ID+'Projects'] = init;