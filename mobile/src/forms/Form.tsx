import React, { useState, useCallback } from 'react';
import { View, StyleSheet } from 'react-native';
import { FormProps, FormState, FormField, ValidationError } from './types';
import { validateField } from './validation';
import FormTextField from '../components/FormTextField';
import FormDateField from '../components/FormDateField';
import FormSelectField from '../components/FormSelectField';
import FormGPSField from '../components/FormGPSField';
import FormPhotoField from '../components/FormPhotoField';

const Form: React.FC<FormProps> = ({
  formData,
  initialValues = {},
  onSubmit,
  onSaveDraft,
}) => {
  const [formState, setFormState] = useState<FormState>({
    values: initialValues,
    errors: {},
    touched: {},
    isValid: false,
    isSubmitting: false,
  });

  const handleChange = useCallback((fieldName: string, value: any) => {
    setFormState((prev) => ({
      ...prev,
      values: {
        ...prev.values,
        [fieldName]: value,
      },
      touched: {
        ...prev.touched,
        [fieldName]: true,
      },
    }));
  }, []);

  const handleBlur = useCallback((field: FormField) => {
    if (!field.validationRules) return;

    const result = validateField(formState.values[field.name], field.validationRules);
    
    setFormState((prev) => ({
      ...prev,
      errors: {
        ...prev.errors,
        [field.name]: result.isValid ? '' : result.errors[0]?.message,
      },
    }));
  }, [formState.values]);

  const renderField = (field: FormField) => {
    const commonProps = {
      field,
      value: formState.values[field.name],
      error: formState.errors[field.name],
      touched: formState.touched[field.name],
      onChange: (value: any) => handleChange(field.name, value),
      onBlur: () => handleBlur(field),
    };

    switch (field.type) {
      case 'text':
      case 'number':
      case 'email':
        return <FormTextField {...commonProps} />;
      case 'date':
        return <FormDateField {...commonProps} />;
      case 'select':
        return <FormSelectField {...commonProps} />;
      case 'gps':
        return <FormGPSField {...commonProps} />;
      case 'photo':
        return <FormPhotoField {...commonProps} />;
      default:
        return null;
    }
  };

  const handleSubmit = async () => {
    let hasErrors = false;
    const newErrors: Record<string, string> = {};

    formData.sections.forEach(section => {
      section.fields.forEach(field => {
        if (field.validationRules) {
          const result = validateField(formState.values[field.name], field.validationRules);
          if (!result.isValid) {
            hasErrors = true;
            newErrors[field.name] = result.errors[0]?.message || '';
          }
        }
      });
    });

    if (hasErrors) {
      setFormState(prev => ({
        ...prev,
        errors: newErrors,
        isValid: false,
      }));
      return;
    }

    setFormState(prev => ({ ...prev, isSubmitting: true }));
    
    try {
      await onSubmit(formState.values);
      setFormState(prev => ({
        ...prev,
        isSubmitting: false,
        isValid: true,
      }));
    } catch (error) {
      setFormState(prev => ({
        ...prev,
        isSubmitting: false,
        isValid: false,
      }));
    }
  };

  return (
    <View style={styles.container}>
      {formData.sections.map(section => (
        <View key={section.id} style={styles.section}>
          {section.fields.map(field => (
            <View key={field.name} style={styles.field}>
              {renderField(field)}
            </View>
          ))}
        </View>
      ))}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 16,
  },
  section: {
    marginBottom: 24,
  },
  field: {
    marginBottom: 16,
  },
});

export default Form;
