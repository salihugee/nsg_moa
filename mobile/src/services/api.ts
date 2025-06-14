import axios from 'axios';
import Config from 'react-native-config';
import { useAuth } from '../contexts/AuthContext';

const api = axios.create({
  baseURL: Config.API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
  },
});

api.interceptors.request.use(async (config) => {
  const auth = useAuth();
  const token = await auth.getToken();
  
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  
  return config;
});

api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      // Handle token expiration
      // You might want to refresh the token or redirect to login
    }
    return Promise.reject(error);
  }
);

export { api };
