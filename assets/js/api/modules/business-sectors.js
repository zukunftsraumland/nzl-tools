import axios from 'axios';

const endpoint = process.env.HOST+'/api/v1/business-sectors';

export default {

    getAll() {
        return axios.get(endpoint);
    },

    get(id) {
        return axios.get(endpoint+'/'+id);
    }

};