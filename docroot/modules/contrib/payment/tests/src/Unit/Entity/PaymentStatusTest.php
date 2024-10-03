<?php

namespace Drupal\Tests\payment\Unit\Entity;

use Drupal\Core\Config\Entity\ConfigEntityTypeInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\payment\Entity\PaymentStatus;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Entity\PaymentStatus
 *
 * @group Payment
 */
class PaymentStatusTest extends UnitTestCase {

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
   * The class under test.
   *
   * @var \Drupal\payment\Entity\PaymentStatus
   */
  protected $sut;

  /**
   * The typed config manager.
   *
   * @var \Drupal\Core\Config\TypedConfigManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $typedConfigManager;

  /**
   * {@inheritdoc}
   *
   * @covers ::setEntityManager
   * @covers ::setTypedConfig
   */
  public function setUp() {
    $this->entityManager = $this->getMock(EntityManagerInterface::class);

    $this->entityTypeId = $this->randomMachineName();

    $this->typedConfigManager = $this->getMock(TypedConfigManagerInterface::class);

    $this->sut = new PaymentStatus([], $this->entityTypeId);
    $this->sut->setEntityManager($this->entityManager);
    $this->sut->setTypedConfig($this->typedConfigManager);
  }

  /**
   * @covers ::id
   * @covers ::setId
   */
  public function testId() {
    $id = strtolower($this->randomMachineName());
    $this->assertSame($this->sut, $this->sut->setId($id));
    $this->assertSame($id, $this->sut->id());
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
   * @covers ::getParentId
   * @covers ::setParentId
   */
  public function testGetParentId() {
    $id = strtolower($this->randomMachineName());
    $this->assertSame($this->sut, $this->sut->setParentId($id));
    $this->assertSame($id, $this->sut->getParentId());
  }

  /**
   * @covers ::getDescription
   * @covers ::setDescription
   */
  public function testGetDescription() {
    $description = $this->randomMachineName();
    $this->assertSame($this->sut, $this->sut->setDescription($description));
    $this->assertSame($description, $this->sut->getDescription());
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

}
