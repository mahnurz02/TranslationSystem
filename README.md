Project Overview
The Laravel Translation API provides an interface for managing translations in a Laravel application. It supports registering and logging in users, performing CRUD operations on translations, searching, and exporting translation data in various formats.

This README covers how to set up and use the project, including installation steps, API endpoints, and test instructions.

Features
User Authentication: Register, login, and logout users with token-based authentication.

Translations Management: Create, read, update, delete, and search translations.

Caching: Translations are cached for optimized performance.

Export: Export translations in a structured JSON format.

Pagination: Translations are paginated for easy handling of large datasets.

Prerequisites
Before starting, ensure that you have the following installed on your machine:

PHP 8.2>=

Composer (for managing PHP dependencies)

Environment Variables
Make sure to configure your .env file as set in env.example
create database and provide db name in env accordingly

insatll composer to setup vendor

After setting up the project seed the db using below commands

    php artisan db:seed
    php artisan tinker
    \App\Models\Translation::factory()->count(100000)->create();

create swagger doc using below command
    php artisan l5-swagger:generate

Laravel 12 provides sanctum by default for authorization purpose, I have used the same for token based authentication 

