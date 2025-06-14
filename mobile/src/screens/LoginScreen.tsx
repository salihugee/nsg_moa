import React, { useState } from 'react';
import { View, StyleSheet } from 'react-native';
import { Button, Text } from 'react-native-paper';
import { useTranslation } from 'react-i18next';
import { useAuth } from '../contexts/AuthContext';

const LoginScreen = () => {
  const { t } = useTranslation();
  const { login } = useAuth();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleLogin = async () => {
    try {
      setLoading(true);
      setError(null);
      await login();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to login');
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      <View style={styles.content}>
        <Text style={styles.title}>NSG MOA</Text>
        <Text style={styles.subtitle}>
          Nasarawa State Ministry of Agriculture
        </Text>

        <Button
          mode="contained"
          onPress={handleLogin}
          loading={loading}
          style={styles.button}
        >
          {t('loginWithMicrosoft')}
        </Button>

        {error && (
          <Text style={styles.error}>
            {error}
          </Text>
        )}
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f3f4f6',
    justifyContent: 'center',
    padding: 20,
  },
  content: {
    alignItems: 'center',
  },
  title: {
    fontSize: 32,
    fontWeight: 'bold',
    marginBottom: 8,
    color: '#1f2937',
  },
  subtitle: {
    fontSize: 18,
    marginBottom: 32,
    color: '#4b5563',
    textAlign: 'center',
  },
  button: {
    width: '100%',
    maxWidth: 300,
    marginTop: 16,
  },
  error: {
    color: '#ef4444',
    marginTop: 16,
    textAlign: 'center',
  },
});

export default LoginScreen;
