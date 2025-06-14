<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $apiKey;
    protected $baseUrl;
    protected $sender;

    public function __construct()
    {
        $this->apiKey = config('services.sms.api_key');
        $this->baseUrl = config('services.sms.base_url');
        $this->sender = config('services.sms.sender');
    }

    /**
     * Send an SMS message to a single recipient.
     *
     * @param string $phoneNumber
     * @param string $message
     * @return array
     */
    public function sendSms(string $phoneNumber, string $message): array
    {
        try {
            // This is a placeholder for actual SMS gateway integration
            // Replace with actual SMS gateway API calls
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/send', [
                'to' => $phoneNumber,
                'message' => $message,
                'sender' => $this->sender,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message_id' => $response->json('message_id'),
                    'status' => 'sent'
                ];
            }

            Log::error('SMS sending failed', [
                'phone' => $phoneNumber,
                'error' => $response->body()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to send SMS',
                'status' => 'failed'
            ];
        } catch (\Exception $e) {
            Log::error('SMS service error', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'failed'
            ];
        }
    }

    /**
     * Send SMS messages to multiple recipients.
     *
     * @param array $phoneNumbers
     * @param string $message
     * @return array
     */
    public function sendBulkSms(array $phoneNumbers, string $message): array
    {
        $results = [];
        $success = 0;
        $failed = 0;

        foreach ($phoneNumbers as $phoneNumber) {
            $result = $this->sendSms($phoneNumber, $message);
            $results[] = [
                'phone' => $phoneNumber,
                'status' => $result['status']
            ];

            if ($result['success']) {
                $success++;
            } else {
                $failed++;
            }
        }

        return [
            'total' => count($phoneNumbers),
            'success' => $success,
            'failed' => $failed,
            'details' => $results
        ];
    }

    /**
     * Check delivery status of a message.
     *
     * @param string $messageId
     * @return array
     */
    public function checkDeliveryStatus(string $messageId): array
    {
        try {
            // This is a placeholder for actual SMS gateway integration
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/status/' . $messageId);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->json('status')
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to check status'
            ];
        } catch (\Exception $e) {
            Log::error('SMS status check error', [
                'message_id' => $messageId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
