<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WaNotificationService;

class CheckUnattendedStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'absen:check-unattended';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check students who have not checked in after jam_batas_terlambat and jam_batas_alfa, and send WhatsApp notifications.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking unattended students for late warnings and auto-alfa...');
        
        $results = WaNotificationService::checkAndSendUnattendedNotifications();

        $this->info("Processed: {$results['processed']} students.");
        $this->info("Late Warnings Sent: {$results['warnings']}");
        $this->info("Alfa Recorded & Sent: {$results['alfas']}");

        return Command::SUCCESS;
    }
}
