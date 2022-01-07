<?php namespace BB\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use GuzzleHttp\Client as HttpClient;

class BillMembers extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'bb:bill-members';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bill members based on the sub charge records';

    /**
     * @var \BB\Services\MemberSubscriptionCharges
     */
    private $subscriptionChargeService;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->subscriptionChargeService = \App::make('\BB\Services\MemberSubscriptionCharges');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try{
            //Update the payments status from pending to due
            $this->info('Moving sub charges to due');
            $this->subscriptionChargeService->makeChargesDue();
    
            //Bill the due charges
            $this->info('Billing members');
            $result = $this->subscriptionChargeService->billMembers();
    
            $this->info('Finished');
    
            $this->notifyTelegram("✔️ billMembers ran");
            $this->notifyTelegram("Billed members: " . $result['gc_users'] . " GC users, " . $result['gc_users_blled'] . " bills created.");
            
        }catch(Exception $e){
            $this->notifyTelegram("🚨 billMembers encountered an exception");
            \Log::error($e);
        }
    }

    protected function notifyTelegram($notification)
    {
        (new HttpClient)->get(
            "https://api.telegram.org/bot" . env('TELEGRAM_BOT_KEY') . "/sendMessage" .
            "?chat_id=" . env('TELEGRAM_BOT_CHAT') . 
            "&text=⏲️" . urlencode($notification)
        );
    }

}
