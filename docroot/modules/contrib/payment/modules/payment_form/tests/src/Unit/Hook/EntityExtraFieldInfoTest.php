<?php

namespace Drupal\Tests\payment_form\Unit\Hook;

use Drupal\payment_form\Hook\EntityExtraFieldInfo;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment_form\Hook\EntityExtraFieldInfo
 *
 * @group Payment
 */
class EntityExtraFieldInfoTest extends UnitTestCase {

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * The class under test.
   *
   * @var \Drupal\payment_form\Hook\EntityExtraFieldInfo
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->stringTranslation = $this->getStringTranslationStub();

    $this->sut = new EntityExtraFieldInfo($this->stringTranslation);
  }

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->sut = new EntityExtraFieldInfo($this->stringTranslation);
  }

  /**
   * @covers ::invoke
   */
  public function testInvoke() {
    $fields = $this->sut->invoke();
    $this->assertInternalType('array', $fields);
    $this->assertArrayHasKey('payment_method', $fields['payment']['payment_form']['form']);
  }
}
