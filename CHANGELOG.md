
## [dev,test]

### Add
- add field is_online at users table default value 0
- add QUEUE_CONNECTION=database to env
- add jobs table
- add pending_notifications table
- add translation email_new_txt_message

## [dev,test,master] 

### CMD
- php artisan migrate --path=database/migrations/2021_07_13_130658_create_badges_table.php
- php artisan db:seed BadgesTableSeeder
- add column badge_id at users table default value 1
- booking_locations zip and city allow null






