<?php

namespace Drupal\Tests\payment\Unit\Plugin\Payment\Method;

use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Utility\Token;
use Drupal\payment\EventDispatcherInterface;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Provides a base for tests for classes that extend
 * \Drupal\payment\Plugin\Payment\Method\PaymentMethodBase.
 */
abstract class PaymentMethodBaseTestBase extends UnitTestCase {

  /**
   * The cache context manager.
   *
   * @var \Drupal\Core\Cache\Context\CacheContextsManager
   */
  protected $cacheCContextManager;

  /**
   * The event dispatcher.
   *
   * @var \Drupal\payment\EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $eventDispatcher;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $moduleHandler;

  /**
   * The token API.
   *
   * @var \Drupal\Core\Utility\Token|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $token;

  /**
   * The payment status manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusManager;

  /**
   * The definition of the payment method plugin under test.
   *
   * @var mixed[]
   */
  protected $pluginDefinition = [];

  /**
   * The ID of the payment method plugin under test.
   *
   * @var string
   */
  protected $pluginId;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->eventDispatcher = $this->getMock(EventDispatcherInterface::class);

    $this->moduleHandler = $this->getMock(ModuleHandlerInterface::class);

    $this->paymentStatusManager = $this->getMock(PaymentStatusManagerInterface::class);

    $this->token = $this->getMockBuilder(Token::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->pluginDefinition = [
      'active' => TRUE,
      'message_text' => $this->randomMachineName(),
      'message_text_format' => $this->randomMachineName(),
    ];

    $this->pluginId = $this->randomMachineName();
    $this->cacheCContextManager = $this->getMockBuilder(CacheContextsManager::class)
      ->disableOriginalConstructor()
      ->getMock();

    $container = new Container();
    $container->set('cache_contexts_manager', $this->cacheCContextManager);
    \Drupal::setContainer($container);
  }

}
