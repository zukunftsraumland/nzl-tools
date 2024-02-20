import axios from 'axios';

const endpoint = process.env.HOST+'/api/v1/event-collections';

export default {

    getAll() {
        return axios.get(endpoint);
    },

    get(id) {
        return axios.get(endpoint+'/'+id);
    },

    create(payload) {
        return axios.post(endpoint, payload);
    },

    update(id, payload) {
        return axios.put(endpoint+'/'+id, payload);
    },

    delete(id) {
        return axios.delete(endpoint+'/'+id);
    },

    getPublic(id) {
        return axios.get(endpoint+'/public/'+id);
    },

};