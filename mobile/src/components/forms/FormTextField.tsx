import React from 'react';
import { TextInput, StyleSheet } from 'react-native';
import { FormTextFieldProps } from '../types/formTypes';
import { FormFieldBase } from './FormFieldBase';
import { theme } from '../theme';

export const FormTextField: React.FC<FormTextFieldProps> = ({
  name,
  label,
  value,
  onChangeText,
  error,
  touched,
  required,
  inputProps,
}) => {
  return (
    <FormFieldBase
      name={name}
      label={label}
      error={error}
      touched={touched}
      required={required}
    >
      <TextInput
        style={[
          styles.input,
          touched && error && styles.inputError,
          inputProps?.style,
        ]}
        value={value}
        onChangeText={onChangeText}
        {...inputProps}
      />
    </FormFieldBase>
  );
};

const styles = StyleSheet.create({
  input: {
    height: 48,
    borderWidth: 1,    borderColor: theme.colors.text,
    borderRadius: 8,
    paddingHorizontal: 12,
    fontSize: 16,
    color: theme.colors.text,
    backgroundColor: theme.colors.background,
  },
  inputError: {
    borderColor: theme.colors.error,
  },
});
