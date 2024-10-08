import axios from 'axios';

const endpoint = process.env.HOST + '/api/le-structure';

export default {
    getAll() {
        return axios.get(endpoint); 
    },
};
