<?php

namespace App\Console\Commands;

use App\Data\MessageData;
use App\Data\TranslationData;
use App\Entities\Message;
use App\Entities\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class UpdateDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updating database ...';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $messages = Message::all();
        foreach ($messages as $message) {
            if($message->type == 'text'){
                $message->message_category_id = MessageData::CATEGORY_ID_TEXT;
            } else {
                $key = json_decode($message->structure_content)->key;
                if($key == 'accepted_booking_time'){
                    $message->message_category_id = MessageData::CATEGORY_ID_ACCEPTED_BOOKING_TIME;
                }
                if($key == 'accepted_package_booking'){
                    $message->message_category_id = MessageData::CATEGORY_ID_ACCEPTED_PACKAGE_BOOKING;
                }
                if($key == 'big_text'){
                    $message->message_category_id = MessageData::CATEGORY_ID_BIG_TEXT;
                }
                if($key == 'big_text_time_booking'){
                    $message->message_category_id = MessageData::CATEGORY_ID_BIG_TEXT_TIME_BOOKING;
                }
                if($key == 'buy_package'){
                    $message->message_category_id = MessageData::CATEGORY_ID_BUY_PACKAGE;
                }
                if($key == 'declined_booking_time'){
                    $message->message_category_id = MessageData::CATEGORY_ID_DECLINED_BOOKING_TIME;
                }
                if($key == 'declined_package_booking'){
                    $message->message_category_id = MessageData::CATEGORY_ID_DECLINED_PACKAGE_BOOKING;
                }
                if($key == 'booking_package'){
                    $message->message_category_id = MessageData::CATEGORY_ID_BOOKING_PACKAGE;
                }
                if($key == 'booking_time'){
                    $message->message_category_id = MessageData::CATEGORY_ID_BOOKING_TIME;
                }
            }
            $message->save();
        }
    }
}
