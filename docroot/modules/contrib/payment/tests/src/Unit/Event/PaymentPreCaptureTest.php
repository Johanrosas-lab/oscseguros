<?php

namespace Drupal\Tests\payment\Unit\Event;

use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Event\PaymentPreCapture;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Event\PaymentPreCapture
 *
 * @group Payment
 */
class PaymentPreCaptureTest extends UnitTestCase {

  /**
   * The payment.
   *
   * @var \Drupal\payment\Entity\PaymentInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $payment;

  /**
   * The event under test.
   *
   * @var \Drupal\payment\Event\PaymentPreCapture
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->payment = $this->getMock(PaymentInterface::class);

    $this->sut = new PaymentPreCapture($this->payment);
  }

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->sut = new PaymentPreCapture($this->payment);
  }

  /**
   * @covers ::getPayment
   */
  public function testGetPayment() {
    $this->assertSame($this->payment, $this->sut->getPayment());
  }

}
