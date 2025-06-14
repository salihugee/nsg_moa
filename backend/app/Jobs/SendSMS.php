<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\SMSService;
use Illuminate\Support\Facades\Log;

class SendSMS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $recipient;
    protected $message;
    protected $type;
    protected $area;
    protected $alert;

    /**
     * The number of times the job may be attempted.
     */
    public $tries;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff;

    /**
     * Create a new job instance.
     */
    public function __construct($type, $data)
    {
        $this->type = $type;
        $this->tries = config('services.sms.retry_attempts', 3);
        $this->backoff = config('services.sms.retry_delay', 5);

        switch ($type) {
            case 'single':
                $this->recipient = $data['recipient'];
                $this->message = $data['message'];
                break;
            case 'bulk':
                $this->recipient = $data['recipients'];
                $this->message = $data['message'];
                break;
            case 'area':
                $this->area = $data['area'];
                $this->message = $data['message'];
                break;
            case 'weather':
                $this->area = $data['area'];
                $this->alert = $data['alert'];
                break;
        }
    }

    /**
     * Execute the job.
     */
    public function handle(SMSService $smsService)
    {
        try {
            $result = false;

            switch ($this->type) {
                case 'single':
                    $result = $smsService->sendSMS($this->recipient, $this->message);
                    break;
                case 'bulk':
                    $result = $smsService->sendBulkSMS($this->recipient, $this->message);
                    break;
                case 'area':
                    $result = $smsService->sendAreaSMS($this->area, $this->message);
                    break;
                case 'weather':
                    $result = $smsService->sendWeatherAlert($this->area, $this->alert);
                    break;
            }

            if (!$result) {
                throw new \Exception('SMS sending failed');
            }

            Log::info('SMS job completed successfully', [
                'type' => $this->type,
                'recipient' => $this->recipient ?? 'area-based'
            ]);

        } catch (\Exception $e) {
            Log::error('SMS job failed', [
                'type' => $this->type,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            if ($this->attempts() >= $this->tries) {
                Log::critical('SMS job failed after all retries', [
                    'type' => $this->type,
                    'recipient' => $this->recipient ?? 'area-based'
                ]);
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('SMS job failed permanently', [
            'type' => $this->type,
            'recipient' => $this->recipient ?? 'area-based',
            'error' => $exception->getMessage()
        ]);
    }
}
