<?php

namespace App\Console\Commands;

use App\Entities\Contact;
use App\Entities\ContactUser;
use App\Entities\User;
use Illuminate\Console\Command;

class CreateContactUserFromExistingUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:contact-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create contact user from existing contact user list';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Process in progress ...');
        $users = User::all();
        foreach ($users as $user){
            $contacts = Contact::where('user_id', $user->id)->get();
            foreach ($contacts as $contact){
                $connectionUser = User::find($contact->connection_user_id);
                if ($connectionUser){
                    $hasContactUser = ContactUser::where('email', '=', $connectionUser->email)
                        ->where('receiverUserId', '=', $user->id)
                        ->first();
                    if ($hasContactUser){
                        continue;
                    }
                }

                if ($connectionUser){
                    $c['contactAbleUserId'] = $contact->connection_user_id;
                    $c['receiverUserId'] = $user->id;
                    $c['firstName'] = $connectionUser->first_name;
                    $c['lastName'] = $connectionUser->last_name;
                    $c['email'] = $connectionUser->email;
                    $c['comment'] = "Created from existing user";
                    $c['lastActiveAt'] = \Carbon\Carbon::now();
                    ContactUser::create($c);
                }
            }
        }

        $this->info('Created contact user successfully!');
    }
}
