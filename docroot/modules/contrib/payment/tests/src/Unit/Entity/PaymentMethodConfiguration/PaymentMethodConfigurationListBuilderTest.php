<?php

namespace Drupal\Tests\payment\Unit\Entity\PaymentMethodConfiguration {

  use Drupal\Core\Config\Entity\ConfigEntityBase;
  use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
  use Drupal\Core\Entity\EntityManagerInterface;
  use Drupal\Core\Entity\EntityTypeInterface;
  use Drupal\Core\Entity\Query\QueryInterface;
  use Drupal\Core\Extension\ModuleHandlerInterface;
  use Drupal\Core\StringTranslation\TranslatableMarkup;
  use Drupal\Core\Url;
  use Drupal\payment\Entity\PaymentInterface;
  use Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationListBuilder;
  use Drupal\payment\Entity\PaymentMethodConfigurationInterface;
  use Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface;
  use Drupal\Tests\UnitTestCase;
  use Drupal\user\UserInterface;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
   * @coversDefaultClass \Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationListBuilder
   *
   * @group Payment
   */
  class PaymentMethodConfigurationListBuilderTest extends UnitTestCase {

    /**
     * The entity storage.
     *
     * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityStorage;

    /**
     * The entity type.
     *
     * @var \Drupal\Core\Entity\EntityTypeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityType;

    /**
     * The module handler.
     *
     * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleHandler;

    /**
     * The payment method configuration manager.
     *
     * @var \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodConfigurationManager;

    /**
     * The string translator.
     *
     * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stringTranslation;

    /**
     * The class under test.
     *
     * @var \Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationListBuilder
     */
    protected $sut;

    /**
     * {@inheritdoc}
     */
    public function setUp() {
      $this->entityStorage = $this->getMock(ConfigEntityStorageInterface::class);

      $this->entityType = $this->getMock(EntityTypeInterface::class);

      $this->moduleHandler = $this->getMock(ModuleHandlerInterface::class);

      $this->paymentMethodConfigurationManager = $this->getMock(PaymentMethodConfigurationManagerInterface::class);

      $this->stringTranslation = $this->getStringTranslationStub();

      $this->sut = new PaymentMethodConfigurationListBuilder($this->entityType, $this->entityStorage, $this->stringTranslation, $this->moduleHandler, $this->paymentMethodConfigurationManager);
    }

    /**
     * @covers ::createInstance
     * @covers ::__construct
     */
    function testCreateInstance() {
      $entity_manager = $this->getMock(EntityManagerInterface::class);
      $entity_manager->expects($this->once())
        ->method('getStorage')
        ->with('payment_method_configuration')
        ->willReturn($this->entityStorage);

      $container = $this->getMock(ContainerInterface::class);
      $map = array(
        array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_manager),
        array('module_handler', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->moduleHandler),
        array('plugin.manager.payment.method_configuration', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentMethodConfigurationManager),
        array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
      );
      $container->expects($this->any())
        ->method('get')
        ->willReturnMap($map);

      $sut = PaymentMethodConfigurationListBuilder::createInstance($container, $this->entityType);
      $this->assertInstanceOf(PaymentMethodConfigurationListBuilder::class, $sut);
    }

    /**
     * @covers ::buildHeader
     */
    function testBuildHeader() {
      $header = $this->sut->buildHeader();
      foreach ($header as $cell) {
        $this->assertInternalType('array', $cell);
        $this->assertInstanceOf(TranslatableMarkup::class, $cell['data']);
        if (array_key_exists('class', $cell)) {
          $this->assertInternalType('array', $cell['class']);
        }
      }
    }

    /**
     * @covers ::buildRow
     */
    function testBuildRow() {
      $payment_method_configuration_entity_label = $this->randomMachineName();
      $payment_method_configuration_entity_status = FALSE;
      $payment_method_configuration_plugin_id = $this->randomMachineName();
      $payment_method_configuration_plugin_label = $this->randomMachineName();

      $payment_method_configuration_plugin_definition = array(
        'label' => $payment_method_configuration_plugin_label,
      );

      $this->paymentMethodConfigurationManager->expects($this->any())
        ->method('getDefinition')
        ->with($payment_method_configuration_plugin_id)
        ->willReturn($payment_method_configuration_plugin_definition);

      $owner = $this->getMockBuilder(UserInterface::class);

      $payment_method_configuration = $this->getMock(PaymentMethodConfigurationInterface::class);
      $payment_method_configuration->expects($this->any())
        ->method('getOwner')
        ->willReturn($owner);
      $payment_method_configuration->expects($this->any())
        ->method('getPluginId')
        ->willReturn($payment_method_configuration_plugin_id);
      $payment_method_configuration->expects($this->any())
        ->method('getPaymentStatus')
        ->willReturn($payment_method_configuration_entity_status);
      $payment_method_configuration->expects($this->any())
        ->method('label')
        ->willReturn($payment_method_configuration_entity_label);

      $this->moduleHandler->expects($this->any())
        ->method('invokeAll')
        ->willReturn([]);

      $build = $this->sut->buildRow($payment_method_configuration);
      unset($build['data']['operations']['data']['#attached']);
      $expected_build = array(
        'data' => array(
          'label' => $payment_method_configuration_entity_label,
          'plugin' => $payment_method_configuration_plugin_label,
          'owner' => array(
            'data' => array(
              '#theme' => 'username',
              '#account' => $owner,
            )
          ),
          'operations' => array(
            'data' => array(
              '#type' => 'operations',
              '#links' => [],
            ),
          ),
        ),
        'class' => array('payment-method-configuration-disabled'),
      );
      $this->assertInstanceOf(TranslatableMarkup::class, $build['data']['status']);
      unset($build['data']['status']);
      $this->assertSame($expected_build, $build);
    }

    /**
     * @covers ::render
     *
     * @depends testBuildHeader
     */
    public function testRender() {
      $query = $this->getMock(QueryInterface::class);
      $query->expects($this->atLeastOnce())
        ->method('pager')
        ->willReturnSelf();
      $query->expects($this->atLeastOnce())
        ->method('sort')
        ->willReturnSelf();

      $this->entityStorage->expects($this->atLeastOnce())
        ->method('getQuery')
        ->willReturn($query);

      $this->entityType->expects($this->any())
        ->method('getClass')
        ->willReturn(ConfigEntityBase::class);

      $this->entityStorage->expects($this->once())
        ->method('loadMultipleOverrideFree')
        ->willReturn([]);

      $build = $this->sut->render();
      unset($build['table']['#attached']);
      unset($build['table']['#header']);
      $expected_build = array(
        '#type' => 'table',
        '#title' => NULL,
        '#rows' => [],
        '#attributes' => array(
          'class' => array('payment-method-configuration-list'),
        ),
        '#cache' => [
          'contexts' => NULL,
          'tags' => NULL,
        ],
      );
      $this->assertInstanceOf(TranslatableMarkup::class, $build['table']['#empty']);
      unset($build['table']['#empty']);
      $this->assertEquals($expected_build, $build['table']);
    }

    /**
     * @covers ::getDefaultOperations
     */
    public function testGetDefaultOperationsWithoutAccess() {
      $method = new \ReflectionMethod($this->sut, 'getDefaultOperations');
      $method->setAccessible(TRUE);

      $payment_method_configuration = $this->getMock(PaymentMethodConfigurationInterface::class);

      $operations = $method->invoke($this->sut, $payment_method_configuration);
      $this->assertEmpty($operations);
    }

    /**
     * @covers ::getDefaultOperations
     */
    public function testGetDefaultOperationsWithAccess() {
      $method = new \ReflectionMethod($this->sut, 'getDefaultOperations');
      $method->setAccessible(TRUE);

      $url_duplicate_form = new Url($this->randomMachineName());

      $payment = $this->getMock(PaymentInterface::class);
      $map = array(
        array('duplicate', NULL, FALSE, TRUE),
      );
      $payment->expects($this->any())
        ->method('access')
        ->willReturnMap($map);
      $map = array(
        array('duplicate-form', [], $url_duplicate_form),
      );
      $payment->expects($this->any())
        ->method('urlInfo')
        ->willReturnMap($map);

      $operations = $method->invoke($this->sut, $payment);
      $expected_operations = ['duplicate'];
      $this->assertSame($expected_operations, array_keys($operations));
      foreach ($operations as $name => $operation) {
        $this->assertInstanceof(TranslatableMarkup::class, $operation['title']);
        $this->assertInstanceof(Url::class, $operation['url']);
        if (array_key_exists('weight', $operation)) {
          $this->assertInternalType('int', $operation['weight']);
        }
      }
    }

  }

}

namespace {

  if (!defined('RESPONSIVE_PRIORITY_LOW')) {
    define('RESPONSIVE_PRIORITY_LOW', 'priority-low');
  }
  if (!defined('RESPONSIVE_PRIORITY_MEDIUM')) {
    define('RESPONSIVE_PRIORITY_MEDIUM', 'priority-medium');
  }

}
