import axios from 'axios';

const endpoint = process.env.HOST + '/api/v1/le-structure';

export default {
    getAll() {
        return axios.get(endpoint); 
    },
};
