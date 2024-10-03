<?php

namespace Drupal\Tests\payment_reference\Unit\Element;

use Drupal\Component\Utility\Random;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface;
use Drupal\payment\QueueInterface;
use Drupal\payment_reference\Element\PaymentReference;
use Drupal\plugin\Plugin\Plugin\PluginSelector\PluginSelectorManagerInterface;
use Drupal\plugin\PluginType\PluginTypeInterface;
use Drupal\plugin\PluginType\PluginTypeManagerInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @coversDefaultClass \Drupal\payment_reference\Element\PaymentReference
 *
 * @group Payment Reference Field
 */
class PaymentReferenceTest extends UnitTestCase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $dateFormatter;

  /**
   * The link generator.
   *
   * @var \Drupal\Core\Utility\LinkGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $linkGenerator;

  /**
   * The payment method manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface
   */
  protected $paymentMethodManager;

  /**
   * The payment method type.
   *
   * @var \Drupal\plugin\PluginType\PluginTypeInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $paymentMethodType;

  /**
   * The payment queue.
   *
   * @var \Drupal\payment\QueueInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentQueue;

  /**
   * The payment storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStorage;

  /**
   * The plugin selector manager.
   *
   * @var \Drupal\plugin\Plugin\Plugin\PluginSelector\PluginSelectorManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $pluginSelectorManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $renderer;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $requestStack;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * The class under test.
   *
   * @var \Drupal\payment_reference\Element\PaymentReference
   */
  protected $sut;

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->currentUser = $this->getMock(AccountInterface::class);

    $this->dateFormatter = $this->getMockBuilder(DateFormatter::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->linkGenerator = $this->getMock(LinkGeneratorInterface::class);

    $this->paymentMethodManager = $this->getMock(PaymentMethodManagerInterface::class);

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->paymentMethodType = $this->prophesize(PluginTypeInterface::class);

    $this->paymentQueue = $this->getMock(QueueInterface::class);

    $this->paymentStorage = $this->getMock(EntityStorageInterface::class);

    $this->pluginSelectorManager = $this->getMock(PluginSelectorManagerInterface::class);

    $this->renderer = $this->getMock(RendererInterface::class);

    $this->requestStack = $this->getMockBuilder(RequestStack::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->urlGenerator = $this->getMock(UrlGeneratorInterface::class);

    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $plugin_definition = [];

    $this->sut = new PaymentReference($configuration, $plugin_id, $plugin_definition, $this->requestStack, $this->paymentStorage, $this->stringTranslation, $this->dateFormatter, $this->linkGenerator, $this->renderer, $this->currentUser, $this->pluginSelectorManager, $this->paymentMethodType->reveal(), new Random(), $this->paymentQueue);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $entity_manager = $this->getMock(EntityManagerInterface::class);
    $entity_manager->expects($this->once())
      ->method('getStorage')
      ->with('payment')
      ->willReturn($this->paymentStorage);

    $plugin_type_manager = $this->prophesize(PluginTypeManagerInterface::class);
    $plugin_type_manager->getPluginType('payment_method')
      ->willReturn($this->paymentMethodType->reveal());

    $container = $this->getMock(ContainerInterface::class);
    $map = array(
      array('current_user', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->currentUser),
      array('date.formatter', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->dateFormatter),
      array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_manager),
      array('link_generator', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->linkGenerator),
      array('payment_reference.queue', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentQueue),
      array('plugin.manager.plugin.plugin_selector', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->pluginSelectorManager),
      array('plugin.plugin_type_manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $plugin_type_manager->reveal()),
      array('renderer', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->renderer),
      array('request_stack', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->requestStack),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $plugin_definition = [];

    $sut = PaymentReference::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf(PaymentReference::class, $sut);
  }

  /**
   * @covers ::getPaymentQueue
   */
  public function testGetPaymentQueue() {
    $method = new \ReflectionMethod($this->sut, 'getPaymentQueue');
    $method->setAccessible(TRUE);
    $this->assertSame($this->paymentQueue, $method->invoke($this->sut));
  }

}
