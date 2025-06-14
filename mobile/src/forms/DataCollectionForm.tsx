import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TextInput,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Platform,
} from 'react-native';
import DateTimePicker from '@react-native-community/datetimepicker';
import Geolocation from 'react-native-geolocation-service';
import { FormField, Location, ValidationError } from '../types';
import { validateForm, required, isValidGPS, isValidDate } from './validation';
import { theme } from '../theme';

interface DataCollectionFormProps {
  onSubmit: (formData: any) => void;
  initialData?: any;
  fields: FormField[];
}

export const DataCollectionFormComponent: React.FC<DataCollectionFormProps> = ({
  onSubmit,
  initialData = {},
  fields,
}) => {
  const [formData, setFormData] = useState<any>(initialData);
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [showDatePicker, setShowDatePicker] = useState<string | null>(null);

  useEffect(() => {
    requestLocationPermission();
  }, []);

  const requestLocationPermission = async () => {
    try {
      if (Platform.OS === 'android') {
        const granted = await Geolocation.requestAuthorization('whenInUse');
        if (granted === 'granted') {
          getCurrentLocation();
        }
      } else {
        getCurrentLocation();
      }
    } catch (error) {
      console.error('Error requesting location permission:', error);
    }
  };

  const getCurrentLocation = () => {
    Geolocation.getCurrentPosition(
      position => {
        const location: Location = {
          latitude: position.coords.latitude,
          longitude: position.coords.longitude,
          timestamp: position.timestamp,
        };
        setFormData(prev => ({ ...prev, location }));
      },
      error => {
        console.error('Error getting location:', error);
        setErrors(prev => ({
          ...prev,
          location: 'Failed to get location',
        }));
      },
      { enableHighAccuracy: true, timeout: 15000, maximumAge: 10000 }
    );
  };

  const handleInputChange = (name: string, value: any) => {
    setFormData(prev => ({ ...prev, [name]: value }));
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: '' }));
    }
  };

  const handleDateChange = (fieldName: string, event: any, selectedDate?: Date) => {
    setShowDatePicker(null);
    if (selectedDate) {
      handleInputChange(fieldName, selectedDate);
    }
  };

  const handleSubmit = () => {
    const validationRules = fields.reduce((acc, field) => {
      const rules = [];
      if (field.required) {
        rules.push(required(field.label));
      }
      if (field.type === 'date') {
        rules.push(isValidDate());
      }
      if (field.type === 'location') {
        rules.push(isValidGPS());
      }
      if (rules.length > 0) {
        acc[field.name] = rules;
      }
      return acc;
    }, {} as Record<string, any>);

    const validationResult = validateForm(formData, validationRules);

    if (!validationResult.isValid) {
      const newErrors: Record<string, string> = {};
      validationResult.errors.forEach((error: ValidationError) => {
        if (error.field) {
          newErrors[error.field] = error.message;
        }
      });
      setErrors(newErrors);
      return;
    }

    onSubmit({
      ...formData,
      lastModified: new Date(),
      status: 'draft',
    });
  };

  const renderField = (field: FormField) => {
    switch (field.type) {
      case 'date':
        return (
          <TouchableOpacity
            style={styles.input}
            onPress={() => setShowDatePicker(field.name)}
          >
            <Text>
              {formData[field.name]
                ? formData[field.name].toLocaleDateString()
                : 'Select date'}
            </Text>
          </TouchableOpacity>
        );
      case 'location':
        return (
          <View style={styles.locationContainer}>
            <Text style={styles.locationText}>
              {formData.location
                ? `Lat: ${formData.location.latitude.toFixed(6)}\nLong: ${formData.location.longitude.toFixed(6)}`
                : 'Getting location...'}
            </Text>
            <TouchableOpacity
              style={styles.refreshButton}
              onPress={getCurrentLocation}
            >
              <Text style={styles.refreshButtonText}>Refresh</Text>
            </TouchableOpacity>
          </View>
        );
      default:
        return (
          <TextInput
            style={styles.input}
            value={formData[field.name]}
            onChangeText={(value) => handleInputChange(field.name, value)}
            placeholder={field.label}
            keyboardType={field.type === 'number' ? 'numeric' : 'default'}
          />
        );
    }
  };

  return (
    <ScrollView style={styles.container}>
      {fields.map((field) => (
        <View key={field.name} style={styles.fieldContainer}>
          <Text style={styles.label}>
            {field.label}
            {field.required && <Text style={styles.required}> *</Text>}
          </Text>
          {renderField(field)}
          {errors[field.name] && (
            <Text style={styles.error}>{errors[field.name]}</Text>
          )}
        </View>
      ))}

      {showDatePicker && (
        <DateTimePicker
          value={formData[showDatePicker] || new Date()}
          mode="date"
          display="default"
          onChange={(event, date) => handleDateChange(showDatePicker, event, date)}
        />
      )}

      <TouchableOpacity style={styles.submitButton} onPress={handleSubmit}>
        <Text style={styles.submitButtonText}>Save</Text>
      </TouchableOpacity>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 16,
  },
  fieldContainer: {
    marginBottom: 16,
  },
  label: {
    fontSize: 16,
    marginBottom: 8,
    color: theme.colors.text,
  },
  required: {
    color: theme.colors.error,
  },
  input: {
    borderWidth: 1,
    borderColor: theme.colors.border,
    borderRadius: 8,
    padding: 12,
    backgroundColor: theme.colors.surface,
  },
  error: {
    color: theme.colors.error,
    fontSize: 12,
    marginTop: 4,
  },
  locationContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: 12,
    backgroundColor: theme.colors.surface,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: theme.colors.border,
  },
  locationText: {
    flex: 1,
  },
  refreshButton: {
    backgroundColor: theme.colors.primary,
    padding: 8,
    borderRadius: 4,
    marginLeft: 8,
  },
  refreshButtonText: {
    color: theme.colors.surface,
  },
  submitButton: {
    backgroundColor: theme.colors.primary,
    padding: 16,
    borderRadius: 8,
    alignItems: 'center',
    marginTop: 24,
    marginBottom: 32,
  },
  submitButtonText: {
    color: theme.colors.surface,
    fontSize: 16,
    fontWeight: 'bold',
  },
});
