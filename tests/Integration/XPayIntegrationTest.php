<?php

declare(strict_types=1);

namespace XPay\Tests\Integration;

use PHPUnit\Framework\TestCase;
use XPay\Types\PaymentMethodData;
use XPay\Types\PaymentRequest;
use XPay\Types\CreateCustomerRequest;
use XPay\Types\XPayConfig;
use XPay\XPay;

/**
 * Integration tests for the X-Pay SDK
 * 
 * These tests require a valid sandbox API key to run.
 * Set XPAY_TEST_API_KEY environment variable or skip these tests.
 */
final class XPayIntegrationTest extends TestCase
{
  private ?XPay $xpay = null;

  protected function setUp(): void
  {
    $apiKey = $_SERVER['XPAY_TEST_API_KEY'] ?? $_ENV['XPAY_TEST_API_KEY'] ?? null;
    $merchantId = $_SERVER['XPAY_MERCHANT_ID'] ?? $_ENV['XPAY_MERCHANT_ID'] ?? null;

    if (!$apiKey) {
      $this->markTestSkipped('XPAY_TEST_API_KEY environment variable not set');
    }

    $config = new XPayConfig(
      apiKey: $apiKey,
      merchantId: $merchantId,
      environment: 'sandbox',
      baseUrl: 'http://localhost:8000'
    );

    $this->xpay = new XPay($config);
  }

  public function testApiConnectivity(): void
  {
    $result = $this->xpay->ping();

    $this->assertTrue($result['success']);
    $this->assertArrayHasKey('timestamp', $result);
  }

  public function testGetPaymentMethods(): void
  {
    $methods = $this->xpay->getPaymentMethods();

    $this->assertArrayHasKey('payment_methods', $methods);
    $this->assertArrayHasKey('environment', $methods);
    $this->assertEquals('sandbox', $methods['environment']);
    $this->assertIsArray($methods['payment_methods']);
  }

  public function testCreateStripePayment(): void
  {
    // First create a customer
    $customer = $this->xpay->customers->create(new CreateCustomerRequest(
      email: 'stripe-test-' . bin2hex(random_bytes(4)) . '@example.com',
      name: 'Stripe Test User',
      phone: '+1234567890',
      description: 'Test customer for Stripe payment'
    ));

    $payment = $this->xpay->payments->create(new PaymentRequest(
      amount: '10.00',
      paymentMethod: 'stripe',
      currency: 'USD',
      description: 'Integration test payment',
      customerId: $customer->id,
      paymentMethodData: new PaymentMethodData(
        paymentMethodTypes: ['card']
      )
    ));

    $this->assertNotEmpty($payment->id);
    $this->assertEquals('10.00', $payment->amount);
    $this->assertEquals('USD', $payment->currency);
    $this->assertEquals('stripe', $payment->paymentMethod);
    $this->assertEquals($customer->id, $payment->customerId);
    $this->assertNotEmpty($payment->clientSecret);
  }

  public function testRetrievePayment(): void
  {
    // First create a customer
    $customer = $this->xpay->customers->create(new CreateCustomerRequest(
      email: 'retrieve-test-' . bin2hex(random_bytes(4)) . '@example.com',
      name: 'Retrieve Test User',
      phone: '+1234567890',
      description: 'Test customer for payment retrieval'
    ));

    // Then create a payment
    $createdPayment = $this->xpay->payments->create(new PaymentRequest(
      amount: '5.00',
      paymentMethod: 'stripe',
      currency: 'USD',
      customerId: $customer->id
    ));

    // Then retrieve it
    $retrievedPayment = $this->xpay->payments->retrieve($createdPayment->id);

    $this->assertEquals($createdPayment->id, $retrievedPayment->id);
    $this->assertEquals($createdPayment->amount, $retrievedPayment->amount);
    $this->assertEquals($createdPayment->currency, $retrievedPayment->currency);
    $this->assertEquals($customer->id, $retrievedPayment->customerId);
  }

  public function testListPayments(): void
  {
    // First create a customer
    $customer = $this->xpay->customers->create(new CreateCustomerRequest(
      email: 'list-test-' . bin2hex(random_bytes(4)) . '@example.com',
      name: 'List Test User',
      phone: '+1234567890',
      description: 'Test customer for payment listing'
    ));

    // Create a couple of payments
    for ($i = 0; $i < 2; $i++) {
      $this->xpay->payments->create(new PaymentRequest(
        amount: (10 + $i) . '.00',
        paymentMethod: 'stripe',
        currency: 'USD',
        description: 'List test payment ' . ($i + 1),
        customerId: $customer->id
      ));
    }

    $result = $this->xpay->payments->list(['limit' => 5]);

    $this->assertArrayHasKey('payments', $result);
    $this->assertArrayHasKey('total', $result);
    $this->assertIsArray($result['payments']);
    $this->assertIsInt($result['total']);
    $this->assertGreaterThanOrEqual(2, $result['total']);
  }
  public function testCreateCustomer(): void
  {
    $customer = $this->xpay->customers->create(new CreateCustomerRequest(
      email: 'integration-test-' . bin2hex(random_bytes(4)) . '@example.com',
      name: 'Integration Test User',
      phone: '+1234567890',
      description: 'Customer from integration test'
    ));

    $this->assertNotEmpty($customer->id);
    $this->assertStringStartsWith('integration-test-', $customer->email);
    $this->assertEquals('Integration Test User', $customer->name);
    $this->assertEquals('+1234567890', $customer->phone);
    $this->assertEquals('Customer from integration test', $customer->description);
  }

  public function testCreateMobileMoneyPayment(): void
  {
    // First create a customer
    $customer = $this->xpay->customers->create(new CreateCustomerRequest(
      email: 'momo-test-' . bin2hex(random_bytes(4)) . '@example.com',
      name: 'MoMo Test User',
      phone: '+231123456789',
      description: 'Test customer for Mobile Money payment'
    ));

    $payment = $this->xpay->payments->create(new PaymentRequest(
      amount: '50.00',
      paymentMethod: 'momo_liberia',
      currency: 'USD',
      description: 'Integration test MoMo payment',
      customerId: $customer->id,
      paymentMethodData: new PaymentMethodData(
        phoneNumber: '+231123456789'
      )
    ));

    $this->assertNotEmpty($payment->id);
    $this->assertEquals('50.00', $payment->amount);
    $this->assertEquals('USD', $payment->currency);
    $this->assertEquals('momo', $payment->paymentMethod);
    $this->assertEquals($customer->id, $payment->customerId);
    $this->assertNotEmpty($payment->referenceId);
  }
}
