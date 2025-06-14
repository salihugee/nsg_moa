# Environment Variables Setup

To run this application, you'll need to set up the following environment variables in a `.env` file:

1. Copy the `.env.example` file to `.env`:
   ```bash
   cp .env.example .env
   ```

2. Fill in the following variables in your `.env` file:

## Azure AD (MSAL) Configuration
- `VITE_AZURE_CLIENT_ID`: Your Azure AD application client ID
- `VITE_AZURE_TENANT_ID`: Your Azure AD tenant ID

## Power BI Configuration
- `VITE_POWERBI_REPORT_ID`: The ID of your Power BI report
- `VITE_POWERBI_WORKSPACE_ID`: The ID of your Power BI workspace

## OpenWeather API Configuration
- `VITE_OPENWEATHER_API_KEY`: Your OpenWeather API key

### Getting the Values

1. **Azure AD Configuration**:
   - Go to the Azure Portal
   - Navigate to Azure Active Directory > App Registrations
   - Create or select your application
   - Copy the Application (client) ID and Directory (tenant) ID

2. **Power BI Configuration**:
   - Open Power BI Service
   - Navigate to your workspace
   - Open the report
   - The IDs will be in the URL:
     ```
     https://app.powerbi.com/groups/{workspaceId}/reports/{reportId}
     ```

3. **OpenWeather API Key**:
   - Sign up at [OpenWeather](https://openweathermap.org/api)
   - Navigate to your account
   - Generate an API key
