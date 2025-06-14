import axios from 'axios';
import Config from 'react-native-config';
import { databaseService } from './DatabaseService';

interface WeatherData {
  location: {
    lat: number;
    lon: number;
    name?: string;
  };
  current: {
    temp: number;
    humidity: number;
    windSpeed: number;
    description: string;
    icon: string;
  };
  forecast: Array<{
    date: string;
    temp: number;
    description: string;
    icon: string;
  }>;
}

class WeatherService {
  private readonly apiKey: string;
  private readonly baseUrl: string;

  constructor() {
    this.apiKey = Config.OPENWEATHER_API_KEY;
    this.baseUrl = 'https://api.openweathermap.org/data/2.5';
  }

  async getWeatherByCoordinates(lat: number, lon: number): Promise<WeatherData> {
    try {
      // Get current weather
      const currentResponse = await axios.get(
        `${this.baseUrl}/weather?lat=${lat}&lon=${lon}&appid=${this.apiKey}&units=metric`
      );

      // Get forecast
      const forecastResponse = await axios.get(
        `${this.baseUrl}/forecast?lat=${lat}&lon=${lon}&appid=${this.apiKey}&units=metric`
      );

      const weather: WeatherData = {
        location: {
          lat,
          lon,
          name: currentResponse.data.name,
        },
        current: {
          temp: currentResponse.data.main.temp,
          humidity: currentResponse.data.main.humidity,
          windSpeed: currentResponse.data.wind.speed,
          description: currentResponse.data.weather[0].description,
          icon: currentResponse.data.weather[0].icon,
        },
        forecast: forecastResponse.data.list
          .filter((_: any, index: number) => index % 8 === 0) // One forecast per day
          .slice(0, 5) // 5 days forecast
          .map((item: any) => ({
            date: item.dt_txt.split(' ')[0],
            temp: item.main.temp,
            description: item.weather[0].description,
            icon: item.weather[0].icon,
          })),
      };

      // Save to local database
      await this.saveWeatherData(weather);

      return weather;
    } catch (error) {
      console.error('Weather API error:', error);
      throw error;
    }
  }

  private async saveWeatherData(weatherData: WeatherData) {
    try {
      await databaseService.saveRecord({
        type: 'weather_data',
        data: weatherData,
        status: 'pending',
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
      });
    } catch (error) {
      console.error('Failed to save weather data:', error);
    }
  }

  async getOfflineWeatherData(lat: number, lon: number): Promise<WeatherData | null> {
    try {
      const records = await databaseService.getRecordsByType('weather_data');
      const nearbyRecords = records.filter(record => {
        const data = record.data as WeatherData;
        const distance = this.calculateDistance(
          lat,
          lon,
          data.location.lat,
          data.location.lon
        );
        return distance < 1; // Within 1km
      });

      if (nearbyRecords.length === 0) return null;

      // Get most recent record
      const mostRecent = nearbyRecords.reduce((prev, current) => {
        return new Date(prev.created_at) > new Date(current.created_at) ? prev : current;
      });

      return mostRecent.data as WeatherData;
    } catch (error) {
      console.error('Failed to get offline weather data:', error);
      return null;
    }
  }

  private calculateDistance(lat1: number, lon1: number, lat2: number, lon2: number): number {
    const R = 6371; // Earth's radius in km
    const dLat = this.deg2rad(lat2 - lat1);
    const dLon = this.deg2rad(lon2 - lon1);
    const a =
      Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(this.deg2rad(lat1)) * Math.cos(this.deg2rad(lat2)) *
      Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
  }

  private deg2rad(deg: number): number {
    return deg * (Math.PI / 180);
  }
}

export const weatherService = new WeatherService();
