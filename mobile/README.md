# NSG MOA Mobile App

Mobile application for the Nasarawa State Ministry of Agriculture Monitoring & Evaluation System.

## Features

- Offline data collection
- Azure AD authentication
- Multi-language support (English and Hausa)
- Weather information
- GIS/Map integration
- Sync capabilities with backend

## Requirements

- Node.js 16 or newer
- React Native development environment setup
- Android Studio (for Android development)
- Xcode (for iOS development, macOS only)
- Azure account for authentication
- OpenWeather API key for weather data
- Google Maps API key for mapping

## Setup

1. Install dependencies:
   ```bash
   npm install
   ```

2. Environment Setup:
   - Copy `.env.example` to `.env`
   - Fill in required environment variables:
     - AZURE_CLIENT_ID
     - AZURE_TENANT_ID
     - API_BASE_URL
     - OPENWEATHER_API_KEY
     - GOOGLE_MAPS_API_KEY

3. iOS Setup (macOS only):
   ```bash
   cd ios
   pod install
   cd ..
   ```

4. Start the development server:
   ```bash
   npm start
   ```

5. Run on devices:
   - Android: `npm run android`
   - iOS: `npm run ios`

## Features Implementation Status

- ✅ Project structure
- ✅ Authentication setup
- ✅ Navigation setup
- ✅ Multi-language support
- ⬜ Offline data collection
- ⬜ Weather integration
- ⬜ GIS/Maps integration
- ⬜ Data sync
- ✅ Forms and validation

## Architecture

The app follows a clean architecture approach with the following structure:

```
src/
  ├── components/      # Reusable UI components
  ├── contexts/        # React contexts (auth, offline data, etc.)
  ├── screens/         # Screen components
  ├── navigation/      # Navigation configuration
  ├── services/        # API and other services
  ├── hooks/          # Custom React hooks
  ├── utils/          # Utility functions
  ├── types/          # TypeScript type definitions
  └── i18n/           # Internationalization
```

## Offline Support

- SQLite for local data storage
- Queue system for pending uploads
- Automatic sync when online
- Conflict resolution strategies

## Security

- Azure AD authentication
- Secure token storage
- API request signing
- Offline data encryption

## Contributing

1. Create a feature branch
2. Make your changes
3. Submit a pull request

## Testing

```bash
# Run tests
npm test

# Run with coverage
npm test -- --coverage
```
