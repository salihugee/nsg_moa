import { ValidationError } from '../types';

export interface ValidationResult {
  isValid: boolean;
  errors: ValidationError[];
}

export interface ValidationRule {
  validate: (value: any) => boolean;
  errorMessage: string;
}

export const required = (fieldName: string): ValidationRule => ({
  validate: (value: any) => {
    if (Array.isArray(value)) {
      return value.length > 0;
    }
    return value !== undefined && value !== null && value !== '';
  },
  errorMessage: `${fieldName} is required`,
});

export const minLength = (length: number, fieldName: string): ValidationRule => ({
  validate: (value: string) => value?.length >= length,
  errorMessage: `${fieldName} must be at least ${length} characters long`,
});

export const isValidGPS = (): ValidationRule => ({
  validate: (value: { latitude: number; longitude: number }) => {
    if (!value) return false;
    const { latitude, longitude } = value;
    return (
      latitude >= -90 && latitude <= 90 && longitude >= -180 && longitude <= 180
    );
  },
  errorMessage: 'Invalid GPS coordinates',
});

export const isValidDate = (): ValidationRule => ({
  validate: (value: Date) => {
    if (!value) return false;
    return !isNaN(value.getTime());
  },
  errorMessage: 'Invalid date',
});

export const validateField = (
  value: any,
  rules: ValidationRule[]
): ValidationResult => {
  const errors: ValidationError[] = [];

  for (const rule of rules) {
    if (!rule.validate(value)) {
      errors.push({ message: rule.errorMessage });
    }
  }

  return {
    isValid: errors.length === 0,
    errors,
  };
};

export const validateForm = (
  formData: Record<string, any>,
  validationRules: Record<string, ValidationRule[]>
): ValidationResult => {
  const errors: ValidationError[] = [];

  for (const [field, rules] of Object.entries(validationRules)) {
    const { errors: fieldErrors } = validateField(formData[field], rules);
    errors.push(...fieldErrors.map(error => ({
      ...error,
      field,
    })));
  }

  return {
    isValid: errors.length === 0,
    errors,
  };
};
