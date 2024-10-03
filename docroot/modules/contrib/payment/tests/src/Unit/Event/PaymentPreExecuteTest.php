<?php

namespace Drupal\Tests\payment\Unit\Event;

use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Event\PaymentPreExecute;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Event\PaymentPreExecute
 *
 * @group Payment
 */
class PaymentPreExecuteTest extends UnitTestCase {

  /**
   * The payment.
   *
   * @var \Drupal\payment\Entity\PaymentInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $payment;

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Event\PaymentPreExecute
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->payment = $this->getMock(PaymentInterface::class);

    $this->sut = new PaymentPreExecute($this->payment);
  }

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->sut = new PaymentPreExecute($this->payment);
  }

  /**
   * @covers ::getPayment
   */
  public function testGetPayment() {
    $this->assertSame($this->payment, $this->sut->getPayment());
  }

}
