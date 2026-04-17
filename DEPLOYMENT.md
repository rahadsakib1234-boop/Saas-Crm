# ComplyBuild CRM - Deployment Guide

## Overview
ComplyBuild CRM is a Laravel-based CRM with intelligent lead automation features:
- **Lead Temperature Scoring**: Automatically classifies leads as hot/warm/cold based on keywords
- **Automation Rules Engine**: Create custom rules to trigger actions based on lead attributes
- **Action Plugins**: Extensible action system (add tags, create tasks, send notifications)

## Requirements
- PHP 8.3+
- MySQL 8.0+ or MariaDB 10.5+
- Composer 2.5+
- Node.js 18+ & NPM (for frontend assets)
- Web server (Apache/Nginx)

## Quick Install

### 1. Clone/Download
```bash
git clone <your-repo-url> complybuild-crm
cd complybuild-crm
```

### 2. Install Dependencies
```bash
composer install --no-dev
npm install
npm run build
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=complybuild_crm
DB_USERNAME=your_user
DB_PASSWORD=your_password
APP_URL=https://your-domain.com
```

### 4. Run Installer
```bash
php artisan krayin-crm:install
```

Follow the prompts to:
- Set application name
- Configure database
- Create admin user

### 5. Set Permissions
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 6. Configure Web Server

#### Nginx Example:
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/complybuild-crm/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

#### Apache:
Point your virtual host document root to the `public/` directory.
Make sure `mod_rewrite` is enabled.

## Default Login
After installation:
- URL: `https://your-domain.com/admin/login`
- Email: (the email you set during install)
- Password: (the password you set during install)

## Features

### Lead Temperature Scoring
Automatically classifies leads based on keywords in description:
- **HOT** (≥15 points): urgent, buy now, immediately, need today
- **WARM** (≥5 points): interested, follow up, pricing, later
- **COLD** (default): no strong signals

Configure keywords in `config/lead_temperature.php`

### Automation Rules
Navigate to Settings > Automation to:
1. Create custom rules
2. Set conditions (field operators: contains, equals, etc.)
3. Define actions (add tag, create task, notify user)
4. View execution logs

### API Endpoints
- `GET /api/leads` - List leads
- `POST /api/leads` - Create lead
- `GET /api/leads/{id}` - Get lead details

## Testing

### Run PHPUnit Tests
```bash
php artisan test
```

### Run Browser Tests (Playwright)
```bash
cd packages/Webkul/Admin/tests/e2e-pw
npm install
npx playwright test
```

## Troubleshooting

### Login not working
- Check `SESSION_DRIVER` in `.env` (use `file` or `database`)
- Clear cache: `php artisan optimize:clear`
- Check storage permissions

### Assets not loading
```bash
npm run build
php artisan storage:link
```

### Database errors
```bash
php artisan migrate:fresh --seed
```

## Support
- Documentation: https://krayincrm.com/docs
- Community: https://discord.gg/zocomputer
- Email: support@complybuild.com

## License
MIT License - See LICENSE file for details.
