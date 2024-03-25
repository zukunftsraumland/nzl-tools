import axios from 'axios';

const endpoint = process.env.HOST+'/api/v1/contacts-data';

export default {

    getFiltered(payload) {
        return axios.get(endpoint+'/'+payload.contactType, payload);
    },

};