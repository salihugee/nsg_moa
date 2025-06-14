# Nasarawa State Ministry of Agriculture M&E System

A comprehensive Monitoring and Evaluation System for the Nasarawa State Ministry of Agriculture. This system includes a web-based dashboard, mobile application, and real-time analytics platform for agricultural data management.

## Features

- Real-time agricultural data monitoring
- Farmer and farm management
- Crop and livestock tracking
- Project metrics and analytics
- Geospatial data integration
- SMS communication system
- Weather data integration
- Mobile data collection
- Interactive dashboards

## Technology Stack

- **Backend**: Laravel/PHP with PostgreSQL + PostGIS
- **Frontend**: Vue.js with Tailwind CSS
- **Mobile**: Vue Native
- **Documentation**: OpenAPI/Swagger
- **Cloud**: Microsoft Azure
- **Analytics**: Power BI integration

## Getting Started

### Prerequisites

- PHP 8.1+
- Composer
- PostgreSQL 14+ with PostGIS extension
- Node.js 18+
- npm or yarn

### Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/nsg_moa.git
cd nsg_moa
```

2. Install backend dependencies:
```bash
cd backend
composer install
```

3. Set up environment variables:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure database in `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nsg_moa
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. Run migrations and seeders:
```bash
php artisan migrate --seed
```

6. Start the development server:
```bash
php artisan serve
```

### API Documentation

The API documentation is available at `/api/documentation` when running the application. It provides comprehensive information about all available endpoints, request/response formats, and authentication requirements.

To regenerate the API documentation:
```bash
php artisan l5-swagger:generate
```

## Project Structure

- `backend/` - Laravel API backend
  - `app/` - Core application code
  - `database/` - Migrations and seeders
  - `routes/` - API route definitions
  - `tests/` - Automated tests
- `frontend/` - Vue.js web application
- `mobile/` - Vue Native mobile application

## Contributing

1. Create a new branch for your feature
2. Make your changes
3. Submit a pull request

## License

This project is proprietary and confidential. Unauthorized copying or distribution is prohibited.

## Contact

For support or inquiries, contact support@nsgmoa.gov.ng