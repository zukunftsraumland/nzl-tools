import '../styles/app.scss';

import { createApp } from 'vue';
import env from './utils/env';
import i18n from './common/i18n';
import helpers from './utils/helpers';
import router from './common/router';
import store from './store';
import axios from 'axios';

import Main from './components/Main';
import CKEditor from '@ckeditor/ckeditor5-vue';

axios.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        if (401 === error.response.status) {
            window.location = '/';
        }

        return Promise.reject(error);
    }
);

const app = createApp(Main);

app.use(env);
app.use(i18n);
app.use(helpers);
app.use(router);
app.use(store);
app.use(CKEditor);

app.mount('#app');