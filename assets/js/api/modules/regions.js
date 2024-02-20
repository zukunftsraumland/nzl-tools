import axios from 'axios';

const endpoint = process.env.HOST+'/api/v1/regions';

export default {

    getAll() {
        return axios.get(endpoint);
    },

    getFiltered(params) {
        return axios.get(endpoint, {
            params,
        });
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

    regions(type) {
        return axios.get(endpoint+'/'+type+'/geojson/de.json');
    },

    cities() {
        return axios.get(endpoint+'/geojson/cities/de.json');
    },

};