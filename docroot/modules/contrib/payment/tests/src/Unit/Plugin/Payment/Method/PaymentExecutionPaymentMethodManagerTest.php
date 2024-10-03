<?php

namespace Drupal\Tests\payment\Unit\Plugin\Payment\Method;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentExecutionPaymentMethodManager;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface;
use Drupal\plugin\PluginOperationsProviderInterface;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\Method\PaymentExecutionPaymentMethodManager
 *
 * @group Payment
 */
class PaymentExecutionPaymentMethodManagerTest extends UnitTestCase {

  /**
   * The account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The payment to filter methods by.
   *
   * @var \Drupal\payment\Entity\PaymentInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $payment;

  /**
   * The original payment method manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodManager;

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentExecutionPaymentMethodManager
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->account = $this->getMock(AccountInterface::class);

    $this->payment = $this->getMock(PaymentInterface::class);

    $this->paymentMethodManager = $this->getMock(PaymentMethodManagerInterface::class);

    $this->sut = new PaymentExecutionPaymentMethodManager($this->payment, $this->account, $this->paymentMethodManager);
  }

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->sut = new PaymentExecutionPaymentMethodManager($this->payment, $this->account, $this->paymentMethodManager);
  }

  /**
   * @covers ::processDecoratedDefinitions
   */
  public function testProcessDecoratedDefinitions() {
    $payment_method_id_a = $this->randomMachineName();
    $payment_method_a = $this->getMock(PaymentMethodInterface::class);
    $payment_method_a->expects($this->atLeastOnce())
      ->method('executePaymentAccess')
      ->with($this->account)
      ->willReturn(AccessResult::allowed());
    $payment_method_id_b = $this->randomMachineName();
    $payment_method_b = $this->getMock(PaymentMethodInterface::class);
    $payment_method_b->expects($this->atLeastOnce())
      ->method('executePaymentAccess')
      ->with($this->account)
      ->willReturn(AccessResult::forbidden());

    $payment_method_definitions = [
      $payment_method_id_a => [
        'id' => $payment_method_id_a,
      ],
      $payment_method_id_b => [
        'id' => $payment_method_id_b,
      ],
    ];
    $this->paymentMethodManager->expects($this->atLeastOnce())
      ->method('getDefinitions')
      ->willReturn($payment_method_definitions);
    $map = [
      [$payment_method_id_a, [], $payment_method_a],
      [$payment_method_id_b, [], $payment_method_b],
    ];
    $this->paymentMethodManager->expects($this->atLeast(count($map)))
      ->method('createInstance')
      ->willReturnMap($map);

    $filtered_plugin_definitions = $this->sut->getDefinitions();
    $expected_filtered_plugin_definitions = [
      $payment_method_id_a => [
        'id' => $payment_method_id_a,
      ],
    ];
    $this->assertSame($expected_filtered_plugin_definitions, $filtered_plugin_definitions);
  }

  /**
   * @covers ::getOperationsProvider
   */
  public function testGetOperationsProvider() {
    $payment_method_id = $this->randomMachineName();
    $payment_method = $this->getMock(PaymentMethodInterface::class);
    $payment_method->expects($this->atLeastOnce())
      ->method('executePaymentAccess')
      ->with($this->account)
      ->willReturn(AccessResult::allowed());

    $payment_method_definitions = [
      $payment_method_id => [
        'id' => $payment_method_id,
      ],
    ];
    $this->paymentMethodManager->expects($this->atLeastOnce())
      ->method('getDefinitions')
      ->willReturn($payment_method_definitions);
    $this->paymentMethodManager->expects($this->atLeastOnce())
      ->method('createInstance')
      ->with($payment_method_id)
      ->willReturn($payment_method);
    $operations_provider = $this->getMock(PluginOperationsProviderInterface::class);
    $this->paymentMethodManager->expects($this->atLeastOnce())
      ->method('getOperationsProvider')
      ->with($payment_method_id)
      ->willReturn($operations_provider);

    $this->assertSame($operations_provider, $this->sut->getOperationsProvider($payment_method_id));
  }

  /**
   * @covers ::getOperationsProvider
   *
   * @expectedException \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function testGetOperationsProviderWithNonExistentPlugin() {
    $this->paymentMethodManager->expects($this->atLeastOnce())
      ->method('getDefinitions')
      ->willReturn([]);

    $this->sut->getOperationsProvider($this->randomMachineName());
  }

}
