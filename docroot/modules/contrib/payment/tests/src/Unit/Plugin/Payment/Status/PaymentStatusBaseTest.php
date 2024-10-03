<?php

namespace Drupal\Tests\payment\Unit\Plugin\Payment\Status {

  use Drupal\Core\Datetime\DrupalDateTime;
  use Drupal\Core\Extension\ModuleHandlerInterface;
  use Drupal\Core\Language\LanguageInterface;
  use Drupal\Core\Language\LanguageManagerInterface;
  use Drupal\payment\Entity\PaymentInterface;
  use Drupal\payment\Plugin\Payment\Status\PaymentStatusBase;
  use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
  use Drupal\Tests\UnitTestCase;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
   * @coversDefaultClass \Drupal\payment\Plugin\Payment\Status\PaymentStatusBase
   *
   * @group Payment
   */
  class PaymentStatusBaseTest extends UnitTestCase {

    /**
     * The default datetime.
     *
     * @var \Drupal\Core\Datetime\DrupalDateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $defaultDateTime;

    /**
     * The language manager.
     *
     * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $languageManager;

    /**
     * The module handler.
     *
     * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleHandler;

    /**
     * The payment status plugin manager.
     *
     * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public $paymentStatusManager;

    /**
     * The definition of the payment status under test.
     *
     * @var array
     */
    public $pluginDefinition;

    /**
     * The ID of the payment status under test.
     *
     * @var string
     */
    public $pluginId;

    /**
     * The class under test.
     *
     * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusBase|\PHPUnit_Framework_MockObject_MockObject
     */
    public $sut;

    /**
     * {@inheritdoc}
     */
    public function setup() {
      $this->defaultDateTime = $this->getMockBuilder(DrupalDateTime::class)
        ->disableOriginalConstructor()
        ->getMock();

      $language = $this->getMock(LanguageInterface::class);

      $this->languageManager = $this->getMock(LanguageManagerInterface::class);
      $this->languageManager->expects($this->any())
        ->method('getCurrentLanguage')
        ->willReturn($language);

      $this->moduleHandler = $this->getMock(ModuleHandlerInterface::class);

      $this->paymentStatusManager = $this->getMock(PaymentStatusManagerInterface::class);

      $configuration = [];
      $this->pluginId = $this->randomMachineName();
      $this->pluginDefinition = array(
        'label' => $this->randomMachineName(),
      );
      $this->sut = $this->getMockBuilder(PaymentStatusBase::class)
        ->setConstructorArgs(array(
          $configuration,
          $this->pluginId,
          $this->pluginDefinition,
          $this->moduleHandler,
          $this->paymentStatusManager,
          $this->defaultDateTime
        ))
        ->getMockForAbstractClass();
    }

    /**
     * @covers ::create
     * @covers ::__construct
     */
    public function testCreate() {
      $container = $this->getMock(ContainerInterface::class);
      \Drupal::setContainer($container);
      $map = array(
        array(
          'language_manager',
          ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
          $this->languageManager
        ),
        array(
          'module_handler',
          ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
          $this->moduleHandler
        ),
        array(
          'plugin.manager.payment.status',
          ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
          $this->paymentStatusManager
        ),
      );
      $container->expects($this->any())
        ->method('get')
        ->willReturnMap($map);

      /** @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemBase $class_name */
      $class_name = get_class($this->sut);

      $sut = $class_name::create($container, [], $this->randomMachineName(), []);
      $this->assertInstanceOf(PaymentStatusBase::class, $sut);
    }

    /**
     * @covers ::calculateDependencies
     */
    public function testCalculateDependencies() {
      $this->assertSame([], $this->sut->calculateDependencies());
    }

    /**
     * @covers ::defaultConfiguration
     */
    public function testDefaultConfiguration() {
      $expected_configuration = array(
        'created' => time(),
        'id' => 0,
      );
      $this->assertSame($expected_configuration,
        $this->sut->defaultConfiguration());
    }

    /**
     * @covers ::setConfiguration
     * @covers ::getConfiguration
     */
    public function testGetConfiguration() {
      $configuration = array(
          $this->randomMachineName() => mt_rand(),
        ) + $this->sut->defaultConfiguration();
      $this->assertNull($this->sut->setConfiguration($configuration));
      $this->assertSame($configuration, $this->sut->getConfiguration());
    }

    /**
     * @covers ::setCreated
     * @covers ::getCreated
     */
    public function testGetCreated() {
      $created = mt_rand();
      $this->assertSame($this->sut, $this->sut->setCreated($created));
      $this->assertSame($created, $this->sut->getCreated());
    }

    /**
     * @covers ::setPayment
     * @covers ::getPayment
     */
    public function testGetPayment() {
      $payment = $this->getMock(PaymentInterface::class);
      $this->assertSame($this->sut, $this->sut->setPayment($payment));
      $this->assertSame($payment, $this->sut->getPayment());
    }

    /**
     * @covers ::getChildren
     */
    public function testGetChildren() {
      $children = array($this->randomMachineName());
      $this->paymentStatusManager->expects($this->once())
        ->method('getChildren')
        ->with($this->pluginId)
        ->willReturn($children);
      $this->assertSame($children, $this->sut->getChildren());
    }

    /**
     * @covers ::getDescendants
     */
    public function testGetDescendants() {
      $descendants = array($this->randomMachineName());
      $this->paymentStatusManager->expects($this->once())
        ->method('getDescendants')
        ->with($this->pluginId)
        ->willReturn($descendants);
      $this->assertSame($descendants, $this->sut->getDescendants());
    }

    /**
     * @covers ::getAncestors
     */
    public function testGetAncestors() {
      $ancestors = array($this->randomMachineName());
      $this->paymentStatusManager->expects($this->once())
        ->method('getAncestors')
        ->with($this->pluginId)
        ->willReturn($ancestors);
      $this->assertSame($ancestors, $this->sut->getAncestors());
    }

    /**
     * @covers ::hasAncestor
     */
    public function testHasAncestor() {
      $expected = TRUE;
      $this->paymentStatusManager->expects($this->once())
        ->method('hasAncestor')
        ->with($this->pluginId)
        ->willReturn($expected);
      $this->assertSame($expected, $this->sut->hasAncestor($this->pluginId));
    }

    /**
     * @covers ::isOrHasAncestor
     */
    public function testIsOrHasAncestor() {
      $expected = TRUE;
      $this->paymentStatusManager->expects($this->once())
        ->method('isOrHasAncestor')
        ->with($this->pluginId)
        ->willReturn($expected);
      $this->assertSame($expected,
        $this->sut->isOrHasAncestor($this->pluginId));
    }

  }

}

namespace {

  if (!function_exists('drupal_get_user_timezone')) {
    function drupal_get_user_timezone() {
    }
  }

}
