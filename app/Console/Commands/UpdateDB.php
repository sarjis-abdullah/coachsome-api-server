<?php

namespace App\Console\Commands;

use App\Data\ContactData;
use App\Data\MessageData;
use App\Data\Promo;
use App\Data\TranslationData;
use App\Entities\ChatSetting;
use App\Entities\Contact;
use App\Entities\GiftOrder;
use App\Entities\Message;
use App\Entities\PromoCode;
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
        $giftOrders = GiftOrder::get();
        foreach ($giftOrders as $giftOrder) {
            $promoCode = PromoCode::find($giftOrder->promo_code_id);
            if ($promoCode) {
                $promoCode->promo_category_id = Promo::CATEGORY_ID_GIFT_CARD;
                $promoCode->save();
            }
        }
    }
}
