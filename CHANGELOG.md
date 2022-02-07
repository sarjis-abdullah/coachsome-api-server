# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]


## [develop,test,master] - 31/01/2022
- php artisan update:db

## [develop,test,master] - 03/01/2022
- Add column local_currency, local_total_amount at orders table
- php artisan update:db

---

## [develop,test,master] - 30/12/2021
- change order_date to transaction_date at gift_orders table

---

## [develop,test,master] - 22/12/2021

-   Add table gift_transactions, promo_categories
-   Add column promo_category_id at promo_codes table default value is 1
-   Add column recipent_name at table git_orders
-   Add column gift_transaction_id, gift_card_amount, transaction_date at orders
-   translation table pdf_template_gift_card key
-   php artisan update:db

---

## [develop,test,master] - 12/12/2021

- Add table athlete_settings, setting_values, user_verifications
- Remove column email_verified_at from users table
- change test2
