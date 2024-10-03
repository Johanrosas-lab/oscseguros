<?php

namespace Drupal\Tests\payment\Unit\Event;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Access\AccessResultNeutral;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Event\PaymentExecuteAccess;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Event\PaymentExecuteAccess
 *
 * @group Payment
 */
class PaymentExecuteAccessTest extends UnitTestCase {

  /**
   * The account to check access for.
   *
   * @var \Drupal\payment\Entity\PaymentInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $account;

  /**
   * The event under test.
   *
   * @var \Drupal\payment\Event\PaymentExecuteAccess
   */
  protected $sut;

  /**
   * The payment.
   *
   * @var \Drupal\payment\Entity\PaymentInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $payment;

  /**
   * The payment method.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethod;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->account = $this->getMock(AccountInterface::class);

    $this->payment = $this->getMock(PaymentInterface::class);

    $this->paymentMethod = $this->getMock(PaymentMethodInterface::class);

    $this->sut = new PaymentExecuteAccess($this->payment, $this->paymentMethod, $this->account);
  }

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->sut = new PaymentExecuteAccess($this->payment, $this->paymentMethod, $this->account);
  }

  /**
   * @covers ::getAccount
   */
  public function testGetAccount() {
    $this->assertSame($this->account, $this->sut->getAccount());
  }

  /**
   * @covers ::getPayment
   */
  public function testGetPayment() {
    $this->assertSame($this->payment, $this->sut->getPayment());
  }

  /**
   * @covers ::getPaymentMethod
   */
  public function testGetPaymentMethod() {
    $this->assertSame($this->paymentMethod, $this->sut->getPaymentMethod());
  }

  /**
   * @covers ::getAccessResult
   * @covers ::setAccessResult
   */
  public function testGetAccessResultAllowed() {
    $result = new AccessResultAllowed();
    $this->assertSame($this->sut, $this->sut->setAccessResult($result));
    $this->assertTrue($this->sut->getAccessResult()->isAllowed());
  }

  /**
   * @covers ::getAccessResult
   * @covers ::setAccessResult
   */
  public function testGetAccessResultForbidden() {
    $result = new AccessResultForbidden();
    $this->assertSame($this->sut, $this->sut->setAccessResult($result));
    $this->assertFalse($this->sut->getAccessResult()->isAllowed());
  }

  /**
   * @covers ::getAccessResult
   * @covers ::setAccessResult
   */
  public function testGetAccessResultNeutral() {
    $result = new AccessResultNeutral();
    $this->assertSame($this->sut, $this->sut->setAccessResult($result));
    $this->assertTrue($this->sut->getAccessResult()->isAllowed());
  }

}
