import React, { createContext, useContext, useState, useEffect } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { PublicClientApplication, AuthenticationResult } from '@azure/msal-react-native';
import Config from 'react-native-config';

interface AuthContextType {
  isAuthenticated: boolean;
  user: any;
  login: () => Promise<void>;
  logout: () => Promise<void>;
  getToken: () => Promise<string | null>;
}

const msalConfig = {
  auth: {
    clientId: Config.AZURE_CLIENT_ID,
    authority: `https://login.microsoftonline.com/${Config.AZURE_TENANT_ID}`,
  },
  cache: {
    cacheLocation: "localStorage",
  },
};

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [user, setUser] = useState<any>(null);
  const [msalInstance, setMsalInstance] = useState<PublicClientApplication | null>(null);

  useEffect(() => {
    const initializeMsal = async () => {
      try {
        const instance = new PublicClientApplication(msalConfig);
        await instance.initialize();
        setMsalInstance(instance);

        const accounts = await instance.getAccounts();
        if (accounts.length > 0) {
          setUser(accounts[0]);
          setIsAuthenticated(true);
        }
      } catch (error) {
        console.error('MSAL initialization failed:', error);
      }
    };

    initializeMsal();
  }, []);

  const login = async () => {
    try {
      if (!msalInstance) throw new Error('MSAL not initialized');

      const result: AuthenticationResult = await msalInstance.acquireToken({
        scopes: ['User.Read']
      });

      setUser(result.account);
      setIsAuthenticated(true);
      await AsyncStorage.setItem('user', JSON.stringify(result.account));
    } catch (error) {
      console.error('Login failed:', error);
      throw error;
    }
  };

  const logout = async () => {
    try {
      if (!msalInstance) throw new Error('MSAL not initialized');

      await msalInstance.logout();
      setUser(null);
      setIsAuthenticated(false);
      await AsyncStorage.removeItem('user');
    } catch (error) {
      console.error('Logout failed:', error);
      throw error;
    }
  };

  const getToken = async () => {
    try {
      if (!msalInstance || !user) return null;

      const result = await msalInstance.acquireTokenSilent({
        scopes: ['User.Read'],
        account: user
      });

      return result.accessToken;
    } catch (error) {
      console.error('Token acquisition failed:', error);
      return null;
    }
  };

  return (
    <AuthContext.Provider 
      value={{ 
        isAuthenticated, 
        user, 
        login, 
        logout,
        getToken
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
