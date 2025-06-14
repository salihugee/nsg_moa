import React, { createContext, useContext, useState } from 'react';
import { FormContextType, FormValues, FormErrors, FormTouched } from '../types/formTypes';

const FormContext = createContext<FormContextType | undefined>(undefined);

interface FormProviderProps {
  initialValues: FormValues;
  onSubmit: (values: FormValues) => void | Promise<void>;
  validate?: (values: FormValues) => FormErrors;
  children: React.ReactNode;
}

export const FormProvider: React.FC<FormProviderProps> = ({
  initialValues,
  onSubmit,
  validate,
  children,
}) => {
  const [values, setValues] = useState<FormValues>(initialValues);
  const [errors, setErrors] = useState<FormErrors>({});
  const [touched, setTouched] = useState<FormTouched>({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleChange = (name: string) => (value: any) => {
    setValues((prev) => ({ ...prev, [name]: value }));
    
    if (validate) {
      const validationErrors = validate({ ...values, [name]: value });
      setErrors((prev) => ({ ...prev, [name]: validationErrors[name] }));
    }
  };

  const handleBlur = (name: string) => () => {
    setTouched((prev) => ({ ...prev, [name]: true }));
  };

  const handleSubmit = async () => {
    const allTouched = Object.keys(values).reduce(
      (acc, key) => ({ ...acc, [key]: true }),
      {}
    );
    setTouched(allTouched);

    if (validate) {
      const validationErrors = validate(values);
      setErrors(validationErrors);

      if (Object.keys(validationErrors).length > 0) {
        return;
      }
    }

    setIsSubmitting(true);
    try {
      await onSubmit(values);
    } finally {
      setIsSubmitting(false);
    }
  };

  const contextValue: FormContextType = {
    values,
    errors,
    touched,
    handleChange,
    handleBlur,
    handleSubmit,
    isSubmitting,
  };

  return (
    <FormContext.Provider value={contextValue}>
      {children}
    </FormContext.Provider>
  );
};

export const useForm = () => {
  const context = useContext(FormContext);
  if (!context) {
    throw new Error('useForm must be used within a FormProvider');
  }
  return context;
};
