<?php

namespace Drupal\Tests\payment\Unit\Entity\Payment;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\payment\Entity\Payment\PaymentStorage;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Entity\Payment\PaymentStorage
 *
 * @group Payment
 */
class PaymentStorageTest extends UnitTestCase {

  /**
   * The entity cache.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $cacheBackend;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $database;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * Information about the entity type.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityType;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $languageManager;

  /**
   * The payment status manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusManager;

  /**
   * The payment type manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentTypeManager;

  /**
   * The storage under test.
   *
   * @var \Drupal\payment\Entity\Payment\PaymentStorage
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->cacheBackend = $this->getMock(CacheBackendInterface::class);

    $this->database = $this->getMockBuilder(Connection::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->entityManager = $this->getMock(EntityManagerInterface::class);

    $this->entityType = $this->getMock(EntityTypeInterface::class);

    $this->languageManager = $this->getMock(LanguageManagerInterface::class);

    $this->paymentStatusManager= $this->getMock(PaymentStatusManagerInterface::class);

    $this->paymentTypeManager = $this->getMock(PaymentTypeManagerInterface::class);

    $this->sut = new PaymentStorage($this->entityType, $this->database, $this->entityManager, $this->cacheBackend, $this->languageManager, $this->paymentStatusManager, $this->paymentTypeManager);
  }

  /**
   * @covers ::createInstance
   * @covers ::__construct
   */
  public function testCreateInstance() {
    $container = $this->getMock(ContainerInterface::class);
    $map = array(
      array('cache.entity', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->cacheBackend),
      array('database', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->database),
      array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityManager),
      array('language_manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->languageManager),
      array('plugin.manager.payment.status', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentStatusManager),
      array('plugin.manager.payment.type', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentTypeManager),
    );
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $sut = PaymentStorage::createInstance($container, $this->entityType);
    $this->assertInstanceOf(PaymentStorage::class, $sut);
  }

}
