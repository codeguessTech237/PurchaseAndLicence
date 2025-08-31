<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>


# About This Project

This project is a web-based software solution that requires installation and configuration before use.
The installation process is designed to be simple and guided step by step.

Please follow the steps below carefully and provide the required information to complete the installation successfully.

## 📦 Software Installation

Before starting, make sure you have:

- A working web server (Apache/Nginx)
- PHP 8+ installed
- MySQL database credentials (host, database name, username, password)
- Proper file permissions for storage and bootstrap/cache folders

## 🔑 Installation Steps
### ✅ Step 0: Required Database Information

Prepare the following database credentials:
- Database Host (e.g., 127.0.0.1)
- Database Name
- Database Username
- Database Password

These will be used to connect the application to your database.
![step-0.png](public/assets/capture/step-0.png)
### ✅ Step 1: Check & Verify File Permissions
Make sure PHP extensions like pdo_mysql, openssl, mbstring, and curl are enabled.

![step-1.png](public/assets/capture/step-1.png)
### ✅ Step 2: Update Purchase Information

Provide your purchase code and buyer username to verify your license.
![step-2.png](public/assets/capture/step-2.png)
### ✅ Step 3: Update Database Information

Fill in the database information you collected earlier:
- DB_HOST
- DB_DATABASE
- DB_USERNAME
- DB_PASSWORD

This will automatically update the .env file.
![step-3.png](public/assets/capture/step-3.png)
### ✅ Step 4: Import Database

The installer will import the default database schema and required tables.
Make sure your database is empty before proceeding.
![step-4.png](public/assets/capture/step-4.png)
### ✅ Step 5: Admin Account Settings

Set up your admin account credentials:
- Admin Name
- Admin Email
- Admin Password

This account will be used to log in to the system.
![step-5.png](public/assets/capture/step-5.png)
### ✅ Step 6: Final Configuration

Your software is now installed 🎉.

Before starting, configure:
- System settings (time zone, currency, etc.)
- Email settings (SMTP for sending mails)
- Other environment configurations if required.
![step-6.png](public/assets/capture/step-6.png)
### 🏠 Home – Start the Application

Once everything is set, you can log in to the system with your admin credentials and start using the software.
![home.png](public/assets/capture/home.png)
