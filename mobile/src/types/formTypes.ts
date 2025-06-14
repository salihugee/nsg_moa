import { TextInputProps } from 'react-native';

export interface FormFieldProps {
  name: string;
  label: string;
  error?: string;
  touched?: boolean;
  required?: boolean;
}

export interface FormTextFieldProps extends FormFieldProps {
  value: string;
  onChangeText: (value: string) => void;
  inputProps?: Partial<TextInputProps>;
}

export interface FormValues {
  [key: string]: any;
}

export interface FormErrors {
  [key: string]: string;
}

export interface FormTouched {
  [key: string]: boolean;
}

export interface FormContextType {
  values: FormValues;
  errors: FormErrors;
  touched: FormTouched;
  handleChange: (name: string) => (value: any) => void;
  handleBlur: (name: string) => () => void;
  handleSubmit: () => void;
  isSubmitting: boolean;
}
