import { t } from 'i18next';

export interface ValidationResult {
  isValid: boolean;
  errors: Record<string, string>;
}

export interface FarmData {
  farmName: string;
  crop: string;
  area: string;
  latitude?: string;
  longitude?: string;
  soilType?: string;
  farmingPractice?: string;
  plantingDate?: string;
  expectedHarvestDate?: string;
  irrigationMethod?: string;
  fertilizerUsed?: string;
  pesticidesUsed?: string;
  notes?: string;
}

export const validateFarmData = (data: Partial<FarmData>): ValidationResult => {
  const errors: Record<string, string> = {};

  if (!data.farmName?.trim()) {
    errors.farmName = t('validation.farmNameRequired');
  }

  if (!data.crop?.trim()) {
    errors.crop = t('validation.cropRequired');
  }

  if (!data.area?.trim()) {
    errors.area = t('validation.areaRequired');
  } else if (isNaN(Number(data.area))) {
    errors.area = t('validation.areaInvalid');
  }

  if (data.plantingDate && !isValidDate(data.plantingDate)) {
    errors.plantingDate = t('validation.invalidDate');
  }

  if (data.expectedHarvestDate && !isValidDate(data.expectedHarvestDate)) {
    errors.expectedHarvestDate = t('validation.invalidDate');
  }

  return {
    isValid: Object.keys(errors).length === 0,
    errors
  };
};

const isValidDate = (dateString: string): boolean => {
  const date = new Date(dateString);
  return date instanceof Date && !isNaN(date.getTime());
};
