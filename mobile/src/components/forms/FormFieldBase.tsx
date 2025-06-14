import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { FormFieldProps } from '../types/formTypes';
import { theme } from '../theme';

export const FormFieldBase: React.FC<FormFieldProps & { children: React.ReactNode }> = ({
  label,
  error,
  touched,
  required,
  children,
}) => {
  const showError = touched && error;

  return (
    <View style={styles.container}>
      {label && (
        <Text style={styles.label}>
          {label}
          {required && <Text style={styles.required}> *</Text>}
        </Text>
      )}
      {children}
      {showError && <Text style={styles.error}>{error}</Text>}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    marginBottom: 16,
  },
  label: {
    fontSize: 16,
    marginBottom: 8,
    color: theme.colors.text,
  },  required: {
    color: theme.colors.error,
  },
  error: {
    color: theme.colors.error,
    fontSize: 14,
    marginTop: 4,
  },
});
