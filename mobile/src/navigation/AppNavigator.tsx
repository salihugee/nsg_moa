import React from 'react';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { useAuth } from '../contexts/AuthContext';
import { useTranslation } from 'react-i18next';

// Screens
import HomeScreen from '../screens/HomeScreen';
import DataCollectionScreen from '../screens/DataCollectionScreen';
import WeatherScreen from '../screens/WeatherScreen';
import ProfileScreen from '../screens/ProfileScreen';
import SettingsScreen from '../screens/SettingsScreen';
import LoginScreen from '../screens/LoginScreen';
import NewRecordScreen from '../screens/NewRecordScreen';
import OfflineDataScreen from '../screens/OfflineDataScreen';

const Tab = createBottomTabNavigator();
const Stack = createNativeStackNavigator();

const AuthenticatedNavigator = () => {
  const { t } = useTranslation();

  return (
    <Tab.Navigator>
      <Tab.Screen 
        name="Home" 
        component={HomeScreen} 
        options={{ title: t('home') }} 
      />
      <Tab.Screen 
        name="DataCollection" 
        component={DataCollectionScreen} 
        options={{ title: t('dataCollection') }} 
      />
      <Tab.Screen 
        name="Weather" 
        component={WeatherScreen} 
        options={{ title: t('weather') }} 
      />
      <Tab.Screen 
        name="Profile" 
        component={ProfileScreen} 
        options={{ title: t('profile') }} 
      />
    </Tab.Navigator>
  );
};

const AppNavigator = () => {
  const { isAuthenticated } = useAuth();
  const { t } = useTranslation();

  return (
    <Stack.Navigator>
      {!isAuthenticated ? (
        <Stack.Screen 
          name="Login" 
          component={LoginScreen} 
          options={{ headerShown: false }} 
        />
      ) : (
        <>
          <Stack.Screen 
            name="MainTabs" 
            component={AuthenticatedNavigator} 
            options={{ headerShown: false }} 
          />
          <Stack.Screen 
            name="Settings" 
            component={SettingsScreen} 
            options={{ title: t('settings') }} 
          />
          <Stack.Screen 
            name="NewRecord" 
            component={NewRecordScreen} 
            options={{ title: t('newRecord') }} 
          />
          <Stack.Screen 
            name="OfflineData" 
            component={OfflineDataScreen} 
            options={{ title: t('offlineData') }} 
          />
        </>
      )}
    </Stack.Navigator>
  );
};

export default AppNavigator;
