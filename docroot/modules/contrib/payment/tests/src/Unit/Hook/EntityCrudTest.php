<?php

namespace Drupal\Tests\payment\Unit\Hook;

use Drupal\payment\Entity\PaymentMethodConfigurationInterface;
use Drupal\payment\Entity\PaymentStatusInterface;
use Drupal\payment\Hook\EntityCrud;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodManager;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManager;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Hook\EntityCrud
 *
 * @group Payment
 */
class EntityCrudTest extends UnitTestCase {

  /**
   * The payment method manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodManager;

  /**
   * The payment status plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusManager;

  /**
   * The service under test.
   *
   * @var \Drupal\payment\Hook\EntityCrud.
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    // Because this is an integration test between the default managers, we
    // cannot mock the interfaces, but have to mock the classes.
    $this->paymentMethodManager = $this->getMockBuilder(PaymentMethodManager::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentStatusManager = $this->getMockBuilder(PaymentStatusManager::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->sut = new EntityCrud($this->paymentMethodManager, $this->paymentStatusManager);
  }

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->sut = new EntityCrud($this->paymentMethodManager, $this->paymentStatusManager);
  }

  /**
   * @covers ::invoke
   */
  public function testInvoke() {
    $payment_method = $this->getMock(PaymentMethodConfigurationInterface::class);
    $payment_method->expects($this->any())
      ->method('getEntityTypeId')
      ->willReturn('payment_method_configuration');

    $payment_status = $this->getMock(PaymentStatusInterface::class);
    $payment_status->expects($this->any())
      ->method('getEntityTypeId')
      ->willReturn('payment_status');

    $this->paymentMethodManager->expects($this->once())
      ->method('clearCachedDefinitions');

    $this->paymentStatusManager->expects($this->once())
      ->method('clearCachedDefinitions');

    $this->sut->invoke($payment_method);
    $this->sut->invoke($payment_status);
  }
}
