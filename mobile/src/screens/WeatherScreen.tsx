import React, { useState, useEffect } from 'react';
import { View, StyleSheet, ScrollView, Image } from 'react-native';
import { 
  Card, 
  Text, 
  Button, 
  ActivityIndicator,
  useTheme
} from 'react-native-paper';
import { useTranslation } from 'react-i18next';
import * as Location from 'react-native-geolocation-service';
import { weatherService } from '../services/WeatherService';

interface WeatherState {
  loading: boolean;
  error: string | null;
  data: any | null;
  lastUpdated: string | null;
}

const WeatherScreen = () => {
  const { t } = useTranslation();
  const theme = useTheme();
  const [weather, setWeather] = useState<WeatherState>({
    loading: true,
    error: null,
    data: null,
    lastUpdated: null
  });

  useEffect(() => {
    loadWeatherData();
  }, []);

  const loadWeatherData = async () => {
    try {
      setWeather(prev => ({ ...prev, loading: true, error: null }));

      const granted = await Location.requestAuthorization('whenInUse');
      if (granted !== 'granted') {
        throw new Error(t('locationPermissionDenied'));
      }

      const position = await new Promise<Location.GeoPosition>((resolve, reject) => {
        Location.getCurrentPosition(
          resolve,
          reject,
          { enableHighAccuracy: true, timeout: 15000, maximumAge: 10000 }
        );
      });

      // Try to get offline data first
      const offlineData = await weatherService.getOfflineWeatherData(
        position.coords.latitude,
        position.coords.longitude
      );

      if (offlineData) {
        setWeather({
          loading: false,
          error: null,
          data: offlineData,
          lastUpdated: new Date().toISOString()
        });
      }

      // Get fresh data from API
      const data = await weatherService.getWeatherByCoordinates(
        position.coords.latitude,
        position.coords.longitude
      );

      setWeather({
        loading: false,
        error: null,
        data,
        lastUpdated: new Date().toISOString()
      });
    } catch (error) {
      setWeather(prev => ({
        ...prev,
        loading: false,
        error: error instanceof Error ? error.message : t('weatherError')
      }));
    }
  };

  if (weather.loading) {
    return (
      <View style={styles.centered}>
        <ActivityIndicator size="large" />
        <Text style={styles.loadingText}>{t('loadingWeather')}</Text>
      </View>
    );
  }

  if (weather.error) {
    return (
      <View style={styles.centered}>
        <Text style={styles.error}>{weather.error}</Text>
        <Button mode="contained" onPress={loadWeatherData}>
          {t('retry')}
        </Button>
      </View>
    );
  }

  if (!weather.data) return null;

  const { current, forecast, location } = weather.data;

  return (
    <ScrollView style={styles.container}>
      <Card style={styles.currentWeather}>
        <Card.Content>
          <Text style={styles.location}>{location.name}</Text>
          <View style={styles.currentMain}>
            <Image 
              source={{ 
                uri: `https://openweathermap.org/img/w/${current.icon}.png` 
              }}
              style={styles.weatherIcon}
            />
            <Text style={styles.temperature}>
              {Math.round(current.temp)}°C
            </Text>
          </View>
          <Text style={styles.description}>
            {current.description}
          </Text>
          <View style={styles.details}>
            <Text>{t('humidity')}: {current.humidity}%</Text>
            <Text>{t('windSpeed')}: {Math.round(current.windSpeed * 3.6)} km/h</Text>
          </View>
        </Card.Content>
      </Card>

      <Text style={styles.forecastTitle}>{t('forecast')}</Text>
      <ScrollView 
        horizontal 
        showsHorizontalScrollIndicator={false}
        style={styles.forecastContainer}
      >
        {forecast.map((day: any) => (
          <Card key={day.date} style={styles.forecastDay}>
            <Card.Content>
              <Text style={styles.date}>
                {new Date(day.date).toLocaleDateString(undefined, { 
                  weekday: 'short' 
                })}
              </Text>
              <Image 
                source={{ 
                  uri: `https://openweathermap.org/img/w/${day.icon}.png` 
                }}
                style={styles.smallIcon}
              />
              <Text style={styles.forecastTemp}>
                {Math.round(day.temp)}°C
              </Text>
              <Text style={styles.forecastDesc}>
                {day.description}
              </Text>
            </Card.Content>
          </Card>
        ))}
      </ScrollView>

      {weather.lastUpdated && (
        <Text style={styles.lastUpdated}>
          {t('lastUpdated')}: {
            new Date(weather.lastUpdated).toLocaleTimeString()
          }
        </Text>
      )}

      <Button 
        mode="contained" 
        onPress={loadWeatherData}
        style={styles.refreshButton}
      >
        {t('refresh')}
      </Button>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 16,
    backgroundColor: '#f5f5f5',
  },
  centered: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 16,
  },
  error: {
    color: '#ef4444',
    marginBottom: 16,
  },
  currentWeather: {
    marginBottom: 16,
  },
  location: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 8,
  },
  currentMain: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginVertical: 16,
  },
  weatherIcon: {
    width: 64,
    height: 64,
  },
  temperature: {
    fontSize: 48,
    marginLeft: 16,
  },
  description: {
    fontSize: 18,
    textAlign: 'center',
    marginBottom: 16,
    textTransform: 'capitalize',
  },
  details: {
    flexDirection: 'row',
    justifyContent: 'space-around',
  },
  forecastTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    marginVertical: 16,
  },
  forecastContainer: {
    marginBottom: 16,
  },
  forecastDay: {
    width: 120,
    marginRight: 8,
  },
  date: {
    textAlign: 'center',
    marginBottom: 8,
  },
  smallIcon: {
    width: 40,
    height: 40,
    alignSelf: 'center',
  },
  forecastTemp: {
    textAlign: 'center',
    fontSize: 18,
    marginVertical: 4,
  },
  forecastDesc: {
    textAlign: 'center',
    fontSize: 12,
    textTransform: 'capitalize',
  },
  lastUpdated: {
    textAlign: 'center',
    color: '#666',
    marginBottom: 16,
  },
  refreshButton: {
    marginBottom: 24,
  },
});

export default WeatherScreen;
