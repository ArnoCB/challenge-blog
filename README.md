# Voclarion Developer Exercise

This is a solution to the Voclarion Developer exercise to create a simple Blog API. I have used PHP, Laravel, MySQL.

1. The solution is dockerized using Sail
    - Assuming everything is installed, it can be started with: ```./vendor/bin/sail up ```
    - It is possible to seed the database with 5 fake blog posts using ```php artisan db:seed```
    - (The external database port is set to 3310)
2. There is a PHPUnit test to test the API: 
    - tests/Unit/BlogApiTest.php
