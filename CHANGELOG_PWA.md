# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

## [develop,test,master] - 27/01/2022
- Remove env APP_PWA_DOMAIN_EMAIL_VERIFICATION_URL=${APP_CLIENT_DOMAIN}/pwa/pages/post-registration
- Add APP_PWA_DOMAIN=${APP_CLIENT_DOMAIN}/pwa
- php artisan config:clear

## [develop,test,master] - 03/01/2022

- Add env APP_PWA_DOMAIN_EMAIL_VERIFICATION_URL=${APP_CLIENT_DOMAIN}/pwa/pages/post-registration
- Add translation key pwa_email_verification_content

## [develop,test,master] - 07/01/2022
- Add translation key pwa_password_reset_email

## [develop,test,master] - 28/02/2022
- change APP_PWA_DOMAIN to -  APP_PWA_DOMAIN=pwa.${APP_CLIENT_DOMAIN}

## [develop,master] - 08/03/2022
- Add env APP_PWA_DOMAIN_BASE_REVIEW_URL=${APP_PWA_DOMAIN}/username/review
- Add env APP_PWA_DOMAIN_TERMS_PAGE=${APP_PWA_DOMAIN}/terms-of-use
- Add env APP_PWA_DOMAIN_PRIVACY_PAGE=${APP_PWA_DOMAIN}/privacy-policy

## [develop,master] - 08/03/2022
- Add the following
#Minio config
MINIO_ENDPOINT="http://182.160.102.227:9000"
MINIO_KEY=hafijur
MINIO_SECRET=tikweb01234
MINIO_REGION=us-east-1
MINIO_BUCKET=files