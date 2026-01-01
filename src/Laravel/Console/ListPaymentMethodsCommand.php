<?php

declare(strict_types=1);

namespace XPay\Laravel\Console;

use Illuminate\Console\Command;
use XPay\Exceptions\XPayException;
use XPay\XPay;

final class ListPaymentMethodsCommand extends Command
{
    protected $signature = 'xpay:payment-methods {--country= : Filter by country code}';
    
    protected $description = 'List available payment methods for the merchant';

    /**
     * Execute the console command.
     */
    public function handle(XPay $xpay): int
    {
        $this->info('Fetching available payment methods...');
        
        try {
            $paymentMethods = $xpay->getPaymentMethods();
            
            $this->line("Environment: " . ($paymentMethods['environment'] ?? 'unknown'));
            $this->line("Merchant ID: " . ($paymentMethods['merchant_id'] ?? 'unknown'));
            $this->newLine();
            
            if (!isset($paymentMethods['payment_methods']) || !is_array($paymentMethods['payment_methods']) || empty($paymentMethods['payment_methods'])) {
                $this->warn('No payment methods available');
                return self::SUCCESS;
            }
            
            $headers = ['Type', 'Name', 'Description', 'Enabled', 'Currencies'];
            $rows = [];
            
            foreach ($paymentMethods['payment_methods'] as $method) {
                if (!is_array($method)) {
                    continue;
                }
                
                $rows[] = [
                    $method['type'] ?? 'N/A',
                    $method['name'] ?? 'N/A',
                    $method['description'] ?? 'N/A',
                    ($method['enabled'] ?? false) ? '✅ Yes' : '❌ No',
                    implode(', ', $method['currencies'] ?? [])
                ];
            }
            
            $this->table($headers, $rows);
            
            $enabledCount = count(array_filter(
                $paymentMethods['payment_methods'], 
                fn(mixed $method): bool => is_array($method) && ($method['enabled'] ?? false)
            ));
            
            $this->info("Total: " . count($paymentMethods['payment_methods']) . " methods");
            $this->info("Enabled: {$enabledCount} methods");
            
            return self::SUCCESS;
        } catch (XPayException $e) {
            $this->error("❌ X-Pay API Error: {$e->getMessage()}");
            $this->line("Error Code: {$e->getErrorCode()}");
            
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error("❌ Unexpected error: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}