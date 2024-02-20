import '../styles/embed-event-collection.scss';

import { createApp } from 'vue';
import env from './utils/env';
import i18n from './common/i18n';
import helpers from './utils/helpers';
import store from './store';

import EmbedEventCollection from './components/EmbedEventCollection';

const app = createApp(EmbedEventCollection);

app.use(env);
app.use(i18n);
app.use(helpers);
app.use(store);

const init = (selector, collectionId, clientOptions = {}) => {

    app.use({
        install: (app, options) => {
            app.config.globalProperties.$clientOptions = {
                ...clientOptions,
                collectionId,
            };
        }
    });

    app.mount(selector);

};

window[process.env.INSTANCE_ID+'EventCollection'] = init;