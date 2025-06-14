import React, { useState } from 'react';
import { View, StyleSheet, ScrollView } from 'react-native';
import { 
  Button, 
  Text, 
  TextInput, 
  HelperText,
  Snackbar,
  useTheme,
  ProgressBar
} from 'react-native-paper';
import { useNavigation } from '@react-navigation/native';
import { useTranslation } from 'react-i18next';
import * as Location from 'react-native-geolocation-service';
import { Picker } from '@react-native-picker/picker';
import { databaseService } from '../services/DatabaseService';

interface FormData {
  type: 'farm_data' | 'weather_data';
  farmName?: string;
  crop?: string;
  area?: string;
  latitude?: string;
  longitude?: string;
  notes?: string;
}

const NewRecordScreen = () => {
  const { t } = useTranslation();
  const navigation = useNavigation();
  const theme = useTheme();
  const [formData, setFormData] = useState<FormData>({
    type: 'farm_data'
  });
  const [errors, setErrors] = useState<Partial<Record<keyof FormData, string>>>({});
  const [loading, setLoading] = useState(false);
  const [snackbar, setSnackbar] = useState({ visible: false, message: '' });

  const validateForm = (): boolean => {
    const newErrors: Partial<Record<keyof FormData, string>> = {};

    if (formData.type === 'farm_data') {
      if (!formData.farmName?.trim()) {
        newErrors.farmName = t('farmNameRequired');
      }
      if (!formData.crop?.trim()) {
        newErrors.crop = t('cropRequired');
      }
      if (!formData.area?.trim()) {
        newErrors.area = t('areaRequired');
      }
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
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

        setFormData(prev => ({
          ...prev,
          latitude: position.coords.latitude.toString(),
          longitude: position.coords.longitude.toString()
        }));
      }
    } catch (error) {
      console.error('Location error:', error);
      setSnackbar({
        visible: true,
        message: t('locationError')
      });
    }
  };

  const handleSave = async () => {
    if (!validateForm()) return;

    setLoading(true);
    try {
      await databaseService.saveRecord({
        type: formData.type,
        data: formData,
        status: 'pending',
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString()
      });

      setSnackbar({
        visible: true,
        message: t('recordSaved')
      });

      // Navigate back after successful save
      setTimeout(() => {
        navigation.goBack();
      }, 1500);
    } catch (error) {
      console.error('Save error:', error);
      setSnackbar({
        visible: true,
        message: t('saveError')
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      <ScrollView style={styles.form}>
        <Picker
          selectedValue={formData.type}
          onValueChange={(value) => setFormData(prev => ({ ...prev, type: value }))}
        >
          <Picker.Item label={t('farmData')} value="farm_data" />
          <Picker.Item label={t('weatherData')} value="weather_data" />
        </Picker>

        {formData.type === 'farm_data' && (
          <>
            <TextInput
              mode="outlined"
              label={t('farmName')}
              value={formData.farmName}
              onChangeText={(text) => setFormData(prev => ({ ...prev, farmName: text }))}
              error={!!errors.farmName}
            />
            <HelperText type="error" visible={!!errors.farmName}>
              {errors.farmName}
            </HelperText>

            <TextInput
              mode="outlined"
              label={t('crop')}
              value={formData.crop}
              onChangeText={(text) => setFormData(prev => ({ ...prev, crop: text }))}
              error={!!errors.crop}
            />
            <HelperText type="error" visible={!!errors.crop}>
              {errors.crop}
            </HelperText>

            <TextInput
              mode="outlined"
              label={t('area')}
              value={formData.area}
              onChangeText={(text) => setFormData(prev => ({ ...prev, area: text }))}
              error={!!errors.area}
              keyboardType="numeric"
            />
            <HelperText type="error" visible={!!errors.area}>
              {errors.area}
            </HelperText>
          </>
        )}

        <View style={styles.locationContainer}>
          <Text>{t('location')}</Text>
          <Button 
            mode="contained" 
            onPress={getCurrentLocation}
            style={styles.locationButton}
          >
            {t('getCurrentLocation')}
          </Button>
          {(formData.latitude && formData.longitude) && (
            <Text style={styles.coordinates}>
              {t('coordinates', { 
                lat: formData.latitude, 
                lon: formData.longitude 
              })}
            </Text>
          )}
        </View>

        <TextInput
          mode="outlined"
          label={t('notes')}
          value={formData.notes}
          onChangeText={(text) => setFormData(prev => ({ ...prev, notes: text }))}
          multiline
          numberOfLines={4}
          style={styles.notes}
        />

        {loading && <ProgressBar indeterminate />}

        <View style={styles.buttons}>
          <Button 
            mode="outlined" 
            onPress={() => navigation.goBack()}
            style={styles.button}
          >
            {t('cancel')}
          </Button>
          <Button 
            mode="contained" 
            onPress={handleSave}
            style={styles.button}
            loading={loading}
            disabled={loading}
          >
            {t('save')}
          </Button>
        </View>
      </ScrollView>

      <Snackbar
        visible={snackbar.visible}
        onDismiss={() => setSnackbar({ visible: false, message: '' })}
        duration={3000}
      >
        {snackbar.message}
      </Snackbar>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#fff',
  },
  form: {
    padding: 16,
  },
  locationContainer: {
    marginVertical: 16,
  },
  locationButton: {
    marginTop: 8,
  },
  coordinates: {
    marginTop: 8,
    color: '#666',
  },
  notes: {
    marginTop: 16,
  },
  buttons: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: 24,
    marginBottom: 16,
  },
  button: {
    flex: 1,
    marginHorizontal: 8,
  },
});

export default NewRecordScreen;
