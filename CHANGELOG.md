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
- create group_global_settings table
- add column contact_category_id to contacts table and default value 1
- add column group_id to contacts with nullable value
- add email email_template_join_conversation to translation from resources/views/emails/joinConversationEmail.blade.php







