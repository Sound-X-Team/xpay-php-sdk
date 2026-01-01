<?php

declare(strict_types=1);

namespace XPay\Laravel\Console;

use Illuminate\Console\Command;
use XPay\Exceptions\XPayException;
use XPay\XPay;

final class TestApiCommand extends Command
{
    protected $signature = 'xpay:test';
    
    protected $description = 'Test X-Pay API connectivity and authentication';

    /**
     * Execute the console command.
     */
    public function handle(XPay $xpay): int
    {
        $this->info('Testing X-Pay API connection...');
        
        try {
            $this->line("Using Merchant ID: " . substr($xpay->getMerchantId(), 0, 8) . '...');
            $this->line("Environment: " . (\config('xpay.environment') ?? 'sandbox'));
            $this->line("Base URL: " . (\config('xpay.base_url') ?? 'default'));
            
            $this->newLine();
            
            $result = $xpay->ping();
            
            if ($result['success'] ?? false) {
                $this->info('✅ API connection successful!');
                $this->line("Timestamp: {$result['timestamp']}");
                
                // Test payment methods endpoint
                $this->line('Testing payment methods endpoint...');
                $paymentMethods = $xpay->getPaymentMethods();
                $this->info('✅ Payment methods retrieved successfully!');
                
                if (isset($paymentMethods['payment_methods']) && is_array($paymentMethods['payment_methods'])) {
                    $this->table(
                        ['Type', 'Name', 'Enabled', 'Currencies'],
                        \collect($paymentMethods['payment_methods'])->map(function (array $method): array {
                            return [
                                $method['type'] ?? 'N/A',
                                $method['name'] ?? 'N/A',
                                ($method['enabled'] ?? false) ? 'Yes' : 'No',
                                implode(', ', $method['currencies'] ?? [])
                            ];
                        })->toArray()
                    );
                }
                
                return self::SUCCESS;
            } else {
                $this->error('❌ API connection failed');
                return self::FAILURE;
            }
        } catch (XPayException $e) {
            $this->error("❌ X-Pay API Error: {$e->getMessage()}");
            $this->line("Error Code: {$e->getErrorCode()}");
            
            if ($e->getHttpStatus()) {
                $this->line("HTTP Status: {$e->getHttpStatus()}");
            }
            
            $details = $e->getDetails();
            if ($details !== null) {
                $this->line("Details: " . json_encode($details, JSON_PRETTY_PRINT));
            }
            
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error("❌ Unexpected error: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}