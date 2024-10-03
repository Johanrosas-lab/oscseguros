<?php

namespace Drupal\Tests\payment_reference\Unit\Controller;

use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface;
use Drupal\payment\Plugin\Payment\Type\PaymentTypeInterface;
use Drupal\payment_reference\Controller\ResumeContext;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment_reference\Controller\ResumeContext
 *
 * @group Payment Reference Field
 */
class ResumeContextTest extends UnitTestCase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currentUser;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * The class under test.
   *
   * @var \Drupal\payment_reference\Controller\ResumeContext
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->currentUser = $this->getMock(AccountInterface::class);

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->sut = new ResumeContext($this->currentUser, $this->stringTranslation);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->getMock(ContainerInterface::class);
    $map = array(
      array('current_user', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->currentUser),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $sut = ResumeContext::create($container);
    $this->assertInstanceOf(ResumeContext::class, $sut);
  }

  /**
   * @covers ::execute
   */
  public function testExecute() {
    $payment_status = $this->getMock(PaymentStatusInterface::class);

    $payment = $this->getMock(PaymentInterface::class);
    $payment->expects($this->once())
      ->method('access')
      ->with('view')
      ->willReturn(TRUE);
    $payment->expects($this->once())
      ->method('getPaymentStatus')
      ->willReturn($payment_status);

    $build = $this->sut->execute($payment);
    $this->assertInternalType('array', $build);
  }

  /**
   * @covers ::title
   */
  public function testTitle() {
    $label = $this->randomMachineName();

    $payment = $this->getMock(PaymentInterface::class);
    $payment->expects($this->once())
      ->method('label')
      ->willReturn($label);

    $this->assertSame($label, $this->sut->title($payment));
  }

  /**
   * @covers ::access
   *
   * @dataProvider providerTestAccess
   */
  public function testAccess($expected, $payment_type_access) {
    $payment_type = $this->getMock(PaymentTypeInterface::class);
    $payment_type->expects($this->once())
      ->method('resumeContextAccess')
      ->willReturn($payment_type_access);

    $payment = $this->getMock(PaymentInterface::class);
    $payment->expects($this->once())
      ->method('getPaymentType')
      ->willReturn($payment_type);

    $this->assertSame($expected, $this->sut->access($payment)->isAllowed());
  }

  /**
   * Provides data to testResumeContextAccess().
   */
  public function providerTestAccess() {
    return array(
      array(TRUE, TRUE),
      array(FALSE, FALSE),
    );
  }

}
