<?php

namespace Drupal\Tests\payment_form\Unit\Plugin\Field\FieldType;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\currency\FormHelperInterface;
use Drupal\payment_form\Plugin\Field\FieldType\PaymentForm;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment_form\Plugin\Field\FieldType\PaymentForm
 *
 * @group Payment Form Field
 */
class PaymentFormTest extends UnitTestCase {

  /**
   * The Currency form helper.
   *
   * @var \Drupal\currency\FormHelperInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currencyFormHelper;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * The class under test.
   *
   * @var \Drupal\payment_form\Plugin\Field\FieldType\PaymentForm|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->currencyFormHelper = $this->getMock(FormHelperInterface::class);

    $this->stringTranslation = $this->getStringTranslationStub();

    $container = new Container();
    $container->set('currency.form_helper', $this->currencyFormHelper);
    $container->set('string_translation', $this->stringTranslation);
    \Drupal::setContainer($container);

    $this->sut = $this->getMockBuilder(PaymentForm::class)
      ->disableOriginalConstructor()
      ->setMethods(['getSetting'])
      ->getMock();
  }

  /**
   * @covers ::fieldSettingsForm
   */
  public function testFieldSettingsForm() {
    $this->sut->expects($this->once())
      ->method('getSetting')
      ->with('currency_code');
    $form = [];
    /** @var \Drupal\Core\Form\FormStateInterface $form_state */
    $form_state = $this->getMock(FormStateInterface::class);
    $this->assertInternalType('array', $this->sut->fieldSettingsForm($form, $form_state));
  }

  /**
   * @covers ::schema
   */
  public function testSchema() {
    $field_storage_definition = $this->getMock(FieldStorageDefinitionInterface::class);
    $schema = $this->sut->schema($field_storage_definition);
    $this->assertInternalType('array', $schema);
    $this->assertArrayHasKey('plugin_configuration', $schema['columns']);
    $this->assertArrayHasKey('plugin_id', $schema['columns']);
  }

}
