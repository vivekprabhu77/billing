# Vignesh Decorators - Billing & Business Management System

A complete mobile-first web-based billing system designed for event decorators, sound, and lighting businesses.

## Features
- **Mobile-First Experience**: Optimized for one-hand usage during events.
- **Paper-Style Layout**: Digital bills that look like traditional physical bill books.
- **Dynamic Item Entry**: Add/Remove items on the fly with automatic calculations.
- **Comprehensive Reports**: Category-wise monthly income reports.
- **Payment Tracking**: Track paid, partial, and pending payments.
- **Photo Logs**: Upload and link event photos to specific bills.
- **Searchable History**: Quickly find bills by customer name or date.

## Technology Stack
- **Frontend**: HTML5, CSS3 (Vanilla), JavaScript (Vanilla)
- **Backend**: PHP 7.4+
- **Database**: MySQL

## Installation
1. Move the `vignesh` folder to your XAMPP `htdocs` directory.
2. Open PHPMyAdmin and create a database named `vignesh_decorators`.
3. Import the `database.sql` file provided in the root directory.
4. Open your browser and go to `http://localhost/vignesh`.

## Folder Structure
- `config/`: Database connection settings.
- `api/`: PHP scripts for saving/updating data.
- `public/`: CSS, JS, and uploaded media.
- `views/`: HTML templates and UI components.
- `index.php`: Main router.
