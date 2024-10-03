<?php

namespace Drupal\Tests\payment\Unit\Controller;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\payment\Controller\EditPayment;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Controller\EditPayment
 *
 * @group Payment
 */
class EditPaymentTest extends UnitTestCase {

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Controller\EditPayment
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->stringTranslation = $this->getStringTranslationStub();

    $this->sut = new EditPayment($this->stringTranslation);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->getMock(ContainerInterface::class);
    $container->expects($this->once())
      ->method('get')
      ->with('string_translation')
      ->willReturn($this->stringTranslation);

    $sut = EditPayment::create($container);
    $this->assertInstanceOf(EditPayment::class, $sut);
  }

  /**
   * @covers ::title
   */
  public function testTitle() {
    $payment = $this->getMock(PaymentInterface::class);
    $payment->expects($this->atLeastOnce())
      ->method('id');

    $this->assertInstanceOf(TranslatableMarkup::class, $this->sut->title($payment));
  }

}
