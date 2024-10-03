<?php

namespace Drupal\Tests\payment\Unit\Entity\PaymentStatus;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\payment\Entity\PaymentStatus\PaymentStatusListBuilder;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Entity\PaymentStatus\PaymentStatusListBuilder
 *
 * @group Payment
 */
class PaymentStatusListBuilderTest extends UnitTestCase {

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityStorage;

  /**
   * The entity type.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityType;

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Entity\PaymentStatus\PaymentStatusListBuilder
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->entityStorage = $this->getMock(EntityStorageInterface::class);

    $this->entityType = $this->getMock(EntityTypeInterface::class);

    $this->sut = new PaymentStatusListBuilder($this->entityType, $this->entityStorage);
  }

  /**
   * @covers ::render
   *
   * @expectedException \Exception
   */
  function testRender() {
    $this->sut->render();
  }

}
