import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';

const resources = {
  en: {
    translation: {
      // Common
      loading: 'Loading...',
      error: 'An error occurred',
      retry: 'Retry',
      save: 'Save',
      cancel: 'Cancel',
      delete: 'Delete',
      edit: 'Edit',
      
      // Auth
      login: 'Login',
      logout: 'Logout',
      loginWithMicrosoft: 'Login with Microsoft',
      
      // Navigation
      home: 'Home',
      dataCollection: 'Data Collection',
      weather: 'Weather',
      profile: 'Profile',
      settings: 'Settings',
      
      // Data Collection
      newRecord: 'New Record',
      offlineData: 'Offline Data',
      sync: 'Sync',
      syncComplete: 'Sync Complete',
      syncError: 'Sync Failed',
      
      // Weather
      temperature: 'Temperature',
      humidity: 'Humidity',
      windSpeed: 'Wind Speed',
      forecast: 'Forecast',
      
      // Settings
      language: 'Language',
      darkMode: 'Dark Mode',
      notifications: 'Notifications',
      about: 'About',
    },
  },
  ha: {
    translation: {
      // Common
      loading: 'Ana lodi...',
      error: 'Kuskure ya faru',
      retry: 'Sake gwadawa',
      save: 'Ajiye',
      cancel: 'Soke',
      delete: 'Share',
      edit: 'Gyara',
      
      // Auth
      login: 'Shiga',
      logout: 'Fita',
      loginWithMicrosoft: 'Shiga da Microsoft',
      
      // Navigation
      home: 'Gida',
      dataCollection: 'Tattara Bayani',
      weather: 'Yanayi',
      profile: 'Bayani',
      settings: 'Saituna',
      
      // Data Collection
      newRecord: 'Sabon Bayani',
      offlineData: 'Bayanin Offline',
      sync: 'Daidaita',
      syncComplete: 'An Daidaita',
      syncError: 'Daidaitawa ya Baci',
      
      // Weather
      temperature: 'Zafi',
      humidity: 'Ruwa a Iska',
      windSpeed: 'Guguwar Iska',
      forecast: 'Hasashen Yanayi',
      
      // Settings
      language: 'Harshe',
      darkMode: 'Yanayin Duhu',
      notifications: 'Sanarwa',
      about: 'Game da Mu',
    },
  },
};

i18n
  .use(initReactI18next)
  .init({
    resources,
    lng: 'en',
    fallbackLng: 'en',
    interpolation: {
      escapeValue: false,
    },
  });

export default i18n;
