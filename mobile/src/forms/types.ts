import { ValidationRule } from './validation';

export interface FormField {
  name: string;
  label: string;
  type: 'text' | 'number' | 'email' | 'date' | 'select' | 'gps' | 'photo' | 'checkbox' | 'radio';
  required?: boolean;
  defaultValue?: any;
  options?: Array<{
    label: string;
    value: any;
  }>;
  validationRules?: ValidationRule[];
  placeholder?: string;
  disabled?: boolean;
  hidden?: boolean;
}

export interface FormSection {
  id: string;
  title: string;
  fields: FormField[];
}

export interface FormData {
  id?: string;
  sections: FormSection[];
  createdAt?: Date;
  updatedAt?: Date;
  status: 'draft' | 'completed' | 'synced';
  userId?: string;
}

export interface FormState {
  values: Record<string, any>;
  errors: Record<string, string>;
  touched: Record<string, boolean>;
  isValid: boolean;
  isSubmitting: boolean;
}

export interface FormProps {
  formData: FormData;
  initialValues?: Record<string, any>;
  onSubmit: (values: Record<string, any>) => Promise<void>;
  onSaveDraft?: (values: Record<string, any>) => Promise<void>;
}

export interface FieldProps {
  field: FormField;
  value: any;
  error?: string;
  touched?: boolean;
  onChange: (value: any) => void;
  onBlur: () => void;
}
