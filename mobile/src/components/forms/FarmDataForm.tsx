import React, { useState } from 'react';
import { View, StyleSheet, ScrollView } from 'react-native';
import { 
  TextInput,
  HelperText,
  Button,
  Text,
  useTheme,
  Portal,
  Modal,
  List
} from 'react-native-paper';
import { useTranslation } from 'react-i18next';
import DateTimePicker from '@react-native-community/datetimepicker';
import * as Location from 'react-native-geolocation-service';
import { FarmData, validateFarmData } from '../../utils/validation';

interface Props {
  onSave: (data: FarmData) => void;
  initialData?: Partial<FarmData>;
  loading?: boolean;
}

const SOIL_TYPES = [
  'clay',
  'sandy',
  'loam',
  'silt',
  'peat',
  'chalk',
];

const FARMING_PRACTICES = [
  'conventional',
  'organic',
  'conservation',
  'integrated',
  'precision',
];

const IRRIGATION_METHODS = [
  'drip',
  'sprinkler',
  'flood',
  'center_pivot',
  'manual',
];

export const FarmDataForm: React.FC<Props> = ({ 
  onSave, 
  initialData = {}, 
  loading = false 
}) => {
  const { t } = useTranslation();
  const theme = useTheme();
  const [formData, setFormData] = useState<Partial<FarmData>>(initialData);
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [showDatePicker, setShowDatePicker] = useState<{
    show: boolean;
    field: 'plantingDate' | 'expectedHarvestDate' | null;
  }>({ show: false, field: null });
  const [showPicker, setShowPicker] = useState<{
    show: boolean;
    field: 'soilType' | 'farmingPractice' | 'irrigationMethod' | null;
    options: string[];
  }>({ show: false, field: null, options: [] });

  const handleChange = (field: keyof FarmData, value: string) => {
    setFormData(prev => ({ ...prev, [field]: value }));
    if (errors[field]) {
      setErrors(prev => {
        const newErrors = { ...prev };
        delete newErrors[field];
        return newErrors;
      });
    }
  };

  const handleDateChange = (field: 'plantingDate' | 'expectedHarvestDate', date?: Date) => {
    setShowDatePicker({ show: false, field: null });
    if (date) {
      handleChange(field, date.toISOString().split('T')[0]);
    }
  };

  const getCurrentLocation = async () => {
    try {
      const granted = await Location.requestAuthorization('whenInUse');
      if (granted === 'granted') {
        const position = await new Promise<Location.GeoPosition>((resolve, reject) => {
          Location.getCurrentPosition(
            resolve,
            reject,
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 10000 }
          );
        });

        handleChange('latitude', position.coords.latitude.toString());
        handleChange('longitude', position.coords.longitude.toString());
      }
    } catch (error) {
      console.error('Location error:', error);
    }
  };

  const handleSubmit = () => {
    const validation = validateFarmData(formData);
    if (!validation.isValid) {
      setErrors(validation.errors);
      return;
    }
    onSave(formData as FarmData);
  };

  return (
    <ScrollView style={styles.container}>
      <TextInput
        label={t('farmName')}
        value={formData.farmName}
        onChangeText={value => handleChange('farmName', value)}
        error={!!errors.farmName}
        style={styles.input}
        disabled={loading}
      />
      <HelperText type="error" visible={!!errors.farmName}>
        {errors.farmName}
      </HelperText>

      <TextInput
        label={t('crop')}
        value={formData.crop}
        onChangeText={value => handleChange('crop', value)}
        error={!!errors.crop}
        style={styles.input}
        disabled={loading}
      />
      <HelperText type="error" visible={!!errors.crop}>
        {errors.crop}
      </HelperText>

      <TextInput
        label={t('area')}
        value={formData.area}
        onChangeText={value => handleChange('area', value)}
        error={!!errors.area}
        style={styles.input}
        keyboardType="numeric"
        disabled={loading}
      />
      <HelperText type="error" visible={!!errors.area}>
        {errors.area}
      </HelperText>

      <View style={styles.locationContainer}>
        <TextInput
          label={t('latitude')}
          value={formData.latitude}
          disabled
          style={[styles.input, styles.locationInput]}
        />
        <TextInput
          label={t('longitude')}
          value={formData.longitude}
          disabled
          style={[styles.input, styles.locationInput]}
        />
        <Button
          mode="outlined"
          onPress={getCurrentLocation}
          disabled={loading}
          style={styles.locationButton}
        >
          {t('getLocation')}
        </Button>
      </View>

      {/* Soil Type Picker */}
      <Button
        mode="outlined"
        onPress={() => setShowPicker({
          show: true,
          field: 'soilType',
          options: SOIL_TYPES
        })}
        style={styles.pickerButton}
      >
        {formData.soilType ? t(`soilTypes.${formData.soilType}`) : t('selectSoilType')}
      </Button>

      {/* Farming Practice Picker */}
      <Button
        mode="outlined"
        onPress={() => setShowPicker({
          show: true,
          field: 'farmingPractice',
          options: FARMING_PRACTICES
        })}
        style={styles.pickerButton}
      >
        {formData.farmingPractice 
          ? t(`farmingPractices.${formData.farmingPractice}`) 
          : t('selectFarmingPractice')}
      </Button>

      {/* Irrigation Method Picker */}
      <Button
        mode="outlined"
        onPress={() => setShowPicker({
          show: true,
          field: 'irrigationMethod',
          options: IRRIGATION_METHODS
        })}
        style={styles.pickerButton}
      >
        {formData.irrigationMethod 
          ? t(`irrigationMethods.${formData.irrigationMethod}`) 
          : t('selectIrrigationMethod')}
      </Button>

      {/* Date Pickers */}
      <Button
        mode="outlined"
        onPress={() => setShowDatePicker({
          show: true,
          field: 'plantingDate'
        })}
        style={styles.pickerButton}
      >
        {formData.plantingDate 
          ? new Date(formData.plantingDate).toLocaleDateString() 
          : t('selectPlantingDate')}
      </Button>

      <Button
        mode="outlined"
        onPress={() => setShowDatePicker({
          show: true,
          field: 'expectedHarvestDate'
        })}
        style={styles.pickerButton}
      >
        {formData.expectedHarvestDate 
          ? new Date(formData.expectedHarvestDate).toLocaleDateString() 
          : t('selectHarvestDate')}
      </Button>

      <TextInput
        label={t('fertilizerUsed')}
        value={formData.fertilizerUsed}
        onChangeText={value => handleChange('fertilizerUsed', value)}
        style={styles.input}
        disabled={loading}
        multiline
      />

      <TextInput
        label={t('pesticidesUsed')}
        value={formData.pesticidesUsed}
        onChangeText={value => handleChange('pesticidesUsed', value)}
        style={styles.input}
        disabled={loading}
        multiline
      />

      <TextInput
        label={t('notes')}
        value={formData.notes}
        onChangeText={value => handleChange('notes', value)}
        style={styles.input}
        disabled={loading}
        multiline
        numberOfLines={4}
      />

      <Button
        mode="contained"
        onPress={handleSubmit}
        disabled={loading}
        loading={loading}
        style={styles.submitButton}
      >
        {t('save')}
      </Button>

      {/* Date Picker Modal */}
      {showDatePicker.show && showDatePicker.field && (
        <DateTimePicker
          value={formData[showDatePicker.field] 
            ? new Date(formData[showDatePicker.field]!) 
            : new Date()}
          mode="date"
          onChange={(_, date) => handleDateChange(showDatePicker.field!, date)}
        />
      )}

      {/* Options Picker Modal */}
      <Portal>
        <Modal
          visible={showPicker.show}
          onDismiss={() => setShowPicker({ show: false, field: null, options: [] })}
          contentContainerStyle={styles.modalContent}
        >
          <ScrollView>
            {showPicker.options.map(option => (
              <List.Item
                key={option}
                title={t(`${showPicker.field}.${option}`)}
                onPress={() => {
                  if (showPicker.field) {
                    handleChange(showPicker.field, option);
                  }
                  setShowPicker({ show: false, field: null, options: [] });
                }}
              />
            ))}
          </ScrollView>
        </Modal>
      </Portal>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    padding: 16,
  },
  input: {
    marginBottom: 4,
  },
  locationContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 16,
  },
  locationInput: {
    flex: 1,
    marginRight: 8,
  },
  locationButton: {
    marginLeft: 8,
  },
  pickerButton: {
    marginVertical: 8,
  },
  submitButton: {
    marginTop: 16,
    marginBottom: 32,
  },
  modalContent: {
    backgroundColor: 'white',
    padding: 16,
    margin: 16,
    borderRadius: 8,
    maxHeight: '80%',
  },
});
