import React from 'react';
import { View, Text, TextInput, StyleSheet } from 'react-native';
import { FieldProps } from '../forms/types';

const FormTextField: React.FC<FieldProps> = ({
  field,
  value,
  error,
  touched,
  onChange,
  onBlur,
}) => {
  return (
    <View style={styles.container}>
      <Text style={styles.label}>{field.label}</Text>
      <TextInput
        style={[styles.input, error && touched && styles.inputError]}
        value={value}
        onChangeText={onChange}
        onBlur={onBlur}
        placeholder={field.placeholder}
        keyboardType={field.type === 'number' ? 'numeric' : 'default'}
        autoCapitalize="none"
        editable={!field.disabled}
      />
      {error && touched && <Text style={styles.errorText}>{error}</Text>}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    marginBottom: 16,
  },
  label: {
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 8,
    color: '#333',
  },
  input: {
    height: 48,
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    paddingHorizontal: 16,
    fontSize: 16,
    backgroundColor: '#fff',
  },
  inputError: {
    borderColor: '#ff3b30',
  },
  errorText: {
    color: '#ff3b30',
    fontSize: 14,
    marginTop: 4,
  },
});

export default FormTextField;
