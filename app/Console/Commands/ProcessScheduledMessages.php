<?php

namespace App\Console\Commands;

use App\Models\ScheduledMessage;
use App\Models\Customer;
use App\Services\WhatsappService;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ProcessScheduledMessages extends Command
{
    protected $signature = 'whatsapp:process-scheduled';
    protected $description = 'Process pending scheduled WhatsApp broadcast messages';

    protected $waService;

    public function __construct(WhatsappService $waService)
    {
        parent::__construct();
        $this->waService = $waService;
    }

    public function handle()
    {
        $this->info('🔄 Checking for scheduled messages...');

        // Get all pending scheduled messages that are due
        $pendingMessages = ScheduledMessage::where('status', 'pending')
            ->where('scheduled_at', '<=', Carbon::now())
            ->get();

        if ($pendingMessages->isEmpty()) {
            $this->info('✅ No scheduled messages to process.');
            return 0;
        }

        $this->info("📬 Found {$pendingMessages->count()} scheduled message(s) to process.");

        foreach ($pendingMessages as $scheduledMessage) {
            /** @var ScheduledMessage $scheduledMessage */
            $this->processMessage($scheduledMessage);
        }

        $this->info('✅ All scheduled messages processed.');
        return 0;
    }

    protected function processMessage(ScheduledMessage $scheduledMessage)
    {
        $this->info("📤 Processing message ID: {$scheduledMessage->id} (type: {$scheduledMessage->broadcast_type})");

        // Mark as processing
        $scheduledMessage->update(['status' => 'processing']);

        $message = $scheduledMessage->message;
        $successCount = 0;
        $failCount = 0;
        $errors = [];

        // For unpaid type, re-resolve customer IDs at execution time
        if ($scheduledMessage->broadcast_type === 'unpaid') {
            $maxRecipients = ScheduledMessage::getMaxRecipients($scheduledMessage->whatsapp_age);
            $customers = Customer::whereNotNull('phone')
                ->where('phone', '!=', '')
                ->whereHas('invoices', function ($q) {
                    $q->where('status', '!=', 'paid');
                })
                ->limit($maxRecipients)
                ->get();

            $scheduledMessage->update(['total_count' => $customers->count()]);
        } else {
            $customerIds = $scheduledMessage->customer_ids;
            $customers = Customer::whereIn('id', $customerIds)
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->get();
        }

        $total = $customers->count();
        $this->output->progressStart($total);

        foreach ($customers as $customer) {
            try {
                // Replace variables
                $msg = str_replace('{name}', $customer->name, $message);
                $msg = str_replace('{tagihan}', number_format($customer->monthly_price, 0, ',', '.'), $msg);

                // Send message
                $result = $this->waService->send($customer->phone, $msg, $scheduledMessage->admin_id);

                if ($result['status']) {
                    $successCount++;
                } else {
                    $failCount++;
                    $errors[] = "{$customer->name}: " . ($result['message'] ?? 'Unknown error');
                }
            } catch (\Exception $e) {
                $failCount++;
                $errors[] = "{$customer->name}: " . $e->getMessage();
            }

            $this->output->progressAdvance();

            // Small delay to avoid rate limiting
            usleep(500000); // 0.5 second delay
        }

        $this->output->progressFinish();

        // Update scheduled message with results
        $scheduledMessage->update([
            'status' => 'completed',
            'success_count' => $successCount,
            'failed_count' => $failCount,
            'error_log' => !empty($errors) ? implode("\n", array_slice($errors, 0, 50)) : null,
        ]);

        $this->info("   ✓ Success: {$successCount}, Failed: {$failCount}");
    }
}
