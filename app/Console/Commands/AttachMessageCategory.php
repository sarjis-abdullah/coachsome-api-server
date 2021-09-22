<?php

namespace App\Console\Commands;

use App\Data\MessageData;
use App\Entities\Message;
use Illuminate\Console\Command;

class AttachMessageCategory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attach:message-category';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'All message have attached with category...';

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
            }
        }
    }
}
