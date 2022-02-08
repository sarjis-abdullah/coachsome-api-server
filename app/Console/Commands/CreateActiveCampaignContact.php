<?php

namespace App\Console\Commands;

use App\Data\RoleData;
use App\Entities\Profile;
use App\Entities\User;
use App\Services\ActiveCampaign\ActiveCampaignService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PHPUnit\Exception;

class CreateActiveCampaignContact extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:active-campaign-contact';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create active campaign contact .....';

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
        $activeCampaignService = new ActiveCampaignService();
//        $activeCampaignService->create("/contacts", ['contact'=>[
//            'firstName'=> 'Hafjur',
//            'lastName'=> 'Hafjur',
//            'email'=> 'testemail@gmail.com',
//            'phone'=> '01928234292',
//        ]]);

        $contacts = [];
        User::with(['profile'])->get()->each(function ($user) use(&$contacts){
          if($user->hasRole([RoleData::ROLE_KEY_ATHLETE, RoleData::ROLE_KEY_COACH])) {
              $data = [
                  "contact" => [
                      "firstName" => "",
                      "lastName" => "",
                      "email" => "",
                      "phone" => "",
                  ]
              ];

              $data["contact"]['firstName'] = $user->first_name;
              $data["contact"]['lastName'] = $user->last_name;
              $data["contact"]['email'] = $user->email;

              $profile = Profile::where('user_id', $user->id)->first();
              if ($profile) {
                  $code = "";
                  $number = "";
                  if ($profile->mobile_code) {
                      $code = config("dialingcode")[$profile->mobile_code];
                  }
                  if ($profile->mobile_no) {
                      $number = join(explode(" ", $profile->mobile_no));
                  }

                  if ($number && $code) {
                      $data["contact"]['phone'] = $code . $number;
                  }
              }

              $contacts[] = $data;

          }
        });

        $activeCampaignService = new ActiveCampaignService();

        foreach ($contacts as $contact) {
            try {
                $activeCampaignService->post("/contact/sync", $contact);
            } catch (\Exception $e) {
                Log::info($e->getMessage());
            }
        }

    }
}
