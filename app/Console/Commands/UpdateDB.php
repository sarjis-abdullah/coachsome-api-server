<?php

namespace App\Console\Commands;

use App\Data\ContactData;
use App\Data\MessageData;
use App\Data\Promo;
use App\Data\SettingValue;
use App\Data\TranslationData;
use App\Entities\ChatSetting;
use App\Entities\Contact;
use App\Entities\GiftOrder;
use App\Entities\Message;
use App\Entities\NotificationSetting;
use App\Entities\Order;
use App\Entities\PromoCode;
use App\Entities\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
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
        $users = User::get();
        foreach ($users as $user) {
            $s = NotificationSetting::where('user_id', $user->id)->first();
            if (!$s) {
                NotificationSetting::create([
                    'user_id' => $user->id,
                    'inbox_message' => SettingValue::ID_EMAIL,
                    'order_message' => SettingValue::ID_EMAIL,
                    'order_update' => SettingValue::ID_EMAIL,
                    'booking_request' => SettingValue::ID_EMAIL,
                    'booking_change' => SettingValue::ID_EMAIL,
                    'account' => SettingValue::ID_EMAIL,
                    'marketting' => SettingValue::ID_EMAIL,
                ]);
            }
        }
    }
}
