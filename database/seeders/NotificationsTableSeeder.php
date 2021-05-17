<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class NotificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['id'=> 1, 'description'=>'Recieve email notifications when someone message you', 't_key'=>'setting_label_notification_when_message_you'],
            ['id'=> 2, 'description'=>'Recieve email notifications when someone make a request, booking, or review', 't_key'=>'setting_label_notification_when_make_req'],
            ['id'=> 3, 'description'=>'Recieve SMS notifications when someone make a booking or decline a request', 't_key'=>'setting_label_when_make_booking'],
            ['id'=> 4, 'description'=>'Recieve marketing/newsletter emails', 't_key'=>'setting_label_notification_news_letter'],
        ];
        \App\Entities\NotificationCategory::insert($data);
    }
}
