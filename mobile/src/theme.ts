import { DefaultTheme } from 'react-native-paper';

export const theme = {
  ...DefaultTheme,
  colors: {
    ...DefaultTheme.colors,
    primary: '#2563eb', // Blue-600
    accent: '#10b981', // Emerald-500
    background: '#f3f4f6', // Gray-100
    surface: '#ffffff',
    text: '#1f2937', // Gray-800
    error: '#ef4444', // Red-500
    success: '#22c55e', // Green-500
    warning: '#f59e0b', // Amber-500
  },
};
