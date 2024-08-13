import axios from 'axios';

const instance = axios.create({
  baseURL: '/api/internals/',
  headers: {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
  withCredentials: true,
});

instance.get('/sanctum/csrf-cookie', { baseURL: '' });
export const useAxios = () => {
  return instance;
};
