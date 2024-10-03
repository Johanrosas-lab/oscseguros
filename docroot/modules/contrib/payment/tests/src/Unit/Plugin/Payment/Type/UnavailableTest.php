<?php

namespace Drupal\Tests\payment\Unit\Plugin\Payment\Type;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslationWrapper;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\EventDispatcherInterface;
use Drupal\payment\Plugin\Payment\Type\Unavailable;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\Type\Unavailable
 *
 * @group Payment
 */
class UnavailableTest extends UnitTestCase {

  /**
   * The event dispatcher.
   *
   * @var \Drupal\payment\EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $eventDispatcher;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Plugin\Payment\Type\Unavailable|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->eventDispatcher = $this->getMock(EventDispatcherInterface::class);

    $this->stringTranslation = $this->getStringTranslationStub();

    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $plugin_definition = [];
    $this->sut = new Unavailable($configuration, $plugin_id, $plugin_definition, $this->eventDispatcher, $this->stringTranslation);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->getMock(ContainerInterface::class);
    $map = array(
      array('payment.event_dispatcher', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->eventDispatcher),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $configuration = [];
    $plugin_definition = [];
    $plugin_id = $this->randomMachineName();
    $sut = Unavailable::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf(Unavailable::class, $sut);
  }

  /**
   * @covers ::resumeContextAccess
   */
  public function testResumeContextAccess() {
    $account = $this->getMock(AccountInterface::class);

    $access = $this->sut->resumeContextAccess($account);
    $this->assertInstanceOf(AccessResultInterface::class, $access);
    $this->assertTrue($access->isForbidden());
  }

  /**
   * @covers ::doGetResumeContextResponse
   * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  public function testDoGetResumeContextResponse() {
    $payment = $this->getMock(PaymentInterface::class);
    $this->sut->setPayment($payment);

    $this->sut->getResumeContextResponse();
  }

  /**
   * @covers ::getPaymentDescription
   */
  public function testGetPaymentDescription() {
    $this->assertInstanceOf(TranslationWrapper::class, $this->sut->getPaymentDescription());
  }
}
