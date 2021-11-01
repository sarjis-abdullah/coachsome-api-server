## feature/chat-improvements [dev]
- composer dump-autoload -o
- Add table message_categories
- Add column message_category_id at messages table
- php artisan update:db
- create chat_settings table
- create contact_categories, groups table
- add column contact_category_id at contact table
- make nullable connection_user_id at contacts table
- create group_invitations table
- add email template to translation from resources/views/emails/joinConversationEmail.blade.php







