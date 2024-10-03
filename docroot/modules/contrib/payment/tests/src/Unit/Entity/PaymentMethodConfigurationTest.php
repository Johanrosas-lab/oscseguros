<?php

namespace Drupal\Tests\payment\Unit\Entity;

use Drupal\Core\Config\Entity\ConfigEntityTypeInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\payment\Entity\PaymentMethodConfiguration;
use Drupal\Tests\UnitTestCase;
use Drupal\user\UserInterface;
use Drupal\user\UserStorageInterface;

/**
 * @coversDefaultClass \Drupal\payment\Entity\PaymentMethodConfiguration
 *
 * @group Payment
 */
class PaymentMethodConfigurationTest extends UnitTestCase {

  /**
   * The bundle.
   *
   * @var string
   */
  protected $bundle;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * The entity type ID.
   *
   * @var string
   */
  protected $entityTypeId;

  /**
   * The class to test.
   *
   * @var \Drupal\payment\Entity\PaymentMethodConfiguration
   */
  protected $sut;

  /**
   * The typed config manager.
   *
   * @var \Drupal\Core\Config\TypedConfigManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $typedConfigManager;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $userStorage;

  /**
   * {@inheritdoc}
   *
   * @covers ::setEntityManager
   * @covers ::setTypedConfig
   * @covers ::setUserStorage
   */
  public function setUp() {
    $this->bundle = $this->randomMachineName();

    $this->entityManager = $this->getMock(EntityManagerInterface::class);

    $this->entityTypeId = $this->randomMachineName();

    $this->typedConfigManager = $this->getMock(TypedConfigManagerInterface::class);

    $this->userStorage = $this->getMock(UserStorageInterface::class);

    $this->sut = new PaymentMethodConfiguration([
      'pluginId' => $this->bundle,
    ], $this->entityTypeId);
    $this->sut->setEntityManager($this->entityManager);
    $this->sut->setTypedConfig($this->typedConfigManager);
    $this->sut->setUserStorage($this->userStorage);
  }

  /**
   * @covers ::bundle
   */
  public function testBundle() {
    $this->assertSame($this->bundle, $this->sut->bundle());
  }

  /**
   * @covers ::getPluginId
   */
  public function testPluginId() {
    $this->assertSame($this->bundle, $this->sut->getPluginId());
  }

  /**
   * @covers ::setPluginConfiguration
   * @covers ::getPluginConfiguration
   */
  public function testGetConfiguration() {
    $configuration = [$this->randomMachineName()];
    $this->assertSame($this->sut, $this->sut->setPluginConfiguration($configuration));
    $this->assertSame($configuration, $this->sut->getPluginConfiguration());
  }

  /**
   * @covers ::setLabel
   * @covers ::label
   */
  public function testLabel() {
    $entity_type = $this->getMock(ConfigEntityTypeInterface::class);
    $entity_type->expects($this->atLeastOnce())
      ->method('getKey')
      ->with('label')
      ->willReturn('label');

    $this->entityManager->expects($this->atLeastOnce())
      ->method('getDefinition')
      ->with($this->entityTypeId)
      ->willReturn($entity_type);

    $label = $this->randomMachineName();
    $this->assertSame($this->sut, $this->sut->setLabel($label));
    $this->assertSame($label, $this->sut->label());
  }

  /**
   * @covers ::setOwnerId
   * @covers ::getOwnerId
   */
  public function testGetOwnerId() {
    $id = mt_rand();
    $this->assertSame($this->sut, $this->sut->setOwnerId($id));
    $this->assertSame($id, $this->sut->getOwnerId());
  }

  /**
   * @covers ::getOwner
   *
   * @depends testGetOwnerId
   */
  public function testGetOwner() {
    $owner = $this->getMock(UserInterface::class);

    $id = mt_rand();

    $this->userStorage->expects($this->atLeastOnce())
      ->method('load')
      ->with($id)
      ->willReturn($owner);

    $this->sut->setOwnerId($id);
    $this->assertSame($owner, $this->sut->getOwner());
  }

  /**
   * @covers ::setOwner
   *
   * @depends testGetOwnerId
   */
  public function testSetOwner() {
    $id = mt_rand();

    $owner = $this->getMock(UserInterface::class);
    $owner->expects($this->atLeastOnce())
      ->method('id')
      ->willReturn($id);

    $this->sut->setOwner($owner);
    $this->assertSame($id, $this->sut->getOwnerId());
  }

  /**
   * @covers ::setId
   * @covers ::id
   */
  public function testId() {
    $id = mt_rand();
    $this->assertSame($this->sut, $this->sut->setId($id));
    $this->assertSame($id, $this->sut->id());
  }

  /**
   * @covers ::setUuid
   * @covers ::uuid
   */
  public function testUuid() {
    $uuid = $this->randomMachineName();
    $this->assertSame($this->sut, $this->sut->setUuid($uuid));
    $this->assertSame($uuid, $this->sut->uuid());
  }

  /**
   * @covers ::entityManager
   */
  public function testEntityManager() {
    $method = new \ReflectionMethod($this->sut, 'entityManager');
    $method->setAccessible(TRUE);
    $this->assertSame($this->entityManager, $method->invoke($this->sut));
  }

  /**
   * @covers ::getTypedConfig
   */
  public function testGetTypedConfig() {
    $method = new \ReflectionMethod($this->sut, 'getTypedConfig');
    $method->setAccessible(TRUE);
    $this->assertSame($this->typedConfigManager, $method->invoke($this->sut));
  }

  /**
   * @covers ::getUserStorage
   */
  public function testGetUserStorage() {
    $method = new \ReflectionMethod($this->sut, 'getUserStorage');
    $method->setAccessible(TRUE);
    $this->assertSame($this->userStorage, $method->invoke($this->sut));
  }

}
