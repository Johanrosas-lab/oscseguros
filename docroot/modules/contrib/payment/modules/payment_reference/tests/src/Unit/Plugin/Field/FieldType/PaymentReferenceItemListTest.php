<?php

namespace Drupal\Tests\payment_reference\Unit\Plugin\Field\FieldType;

use Drupal\Core\Form\FormStateInterface;
use Drupal\payment_reference\Plugin\Field\FieldType\PaymentReferenceItemList;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment_reference\Plugin\Field\FieldType\PaymentReferenceItemList
 *
 * @group Payment Reference Field
 */
class PaymentReferenceItemListTest extends UnitTestCase {

  /**
   * The class under test.
   *
   * @var \Drupal\payment_reference\Plugin\Field\FieldType\PaymentReferenceItemList|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->sut = $this->getMockBuilder(PaymentReferenceItemList::class)
      ->disableOriginalConstructor()
      ->setMethods(NULL)
      ->getMock();
  }

  /**
   * @covers ::defaultValuesForm
   */
  public function testDefaultValuesForm() {
    $form = [];
    $form_state = $this->getMock(FormStateInterface::class);
    // We explicitly do not want form elements to configure a default value.
    $this->assertSame([], $this->sut->defaultValuesForm($form, $form_state));
  }

  /**
   * @covers ::defaultValuesFormValidate
   */
  public function testDefaultValuesFormValidate() {
    $element = [];
    $form = [];
    $form_state = $this->getMock(FormStateInterface::class);
    // These methods do nothing, but make sure they do not cause errors either.
    $this->sut->defaultValuesFormValidate($element, $form, $form_state);
  }

  /**
   * @covers ::defaultValuesFormSubmit
   */
  public function testDefaultValuesFormSubmit() {
    $element = [];
    $form = [];
    $form_state = $this->getMock(FormStateInterface::class);
    // These methods do nothing, but make sure they do not cause errors either.
    $this->sut->defaultValuesFormSubmit($element, $form, $form_state);
  }

}
