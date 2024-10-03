<?php

namespace Drupal\Tests\payment_form\Unit\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface;
use Drupal\payment_form\Plugin\Field\FieldWidget\PaymentForm;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment_form\Plugin\Field\FieldWidget\PaymentForm
 *
 * @group Payment Form Field
 */
class PaymentFormTest extends UnitTestCase {

  /**
   * The field definition.
   *
   * @var \Drupal\Core\Field\FieldDefinitionInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $fieldDefinition;

  /**
   * The payment line item manager.
   *
   * @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentLineItemManager;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * The class under test.
   *
   * @var \Drupal\payment_form\Plugin\Field\FieldWidget\PaymentForm
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $plugin_id = $this->randomMachineName();
    $plugin_definition = [];
    $this->fieldDefinition = $this->getMock(FieldDefinitionInterface::class);
    $settings = [];
    $third_party_settings = [];

    $this->paymentLineItemManager = $this->getMock(PaymentLineItemManagerInterface::class);

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->sut = new PaymentForm($plugin_id, $plugin_definition, $this->fieldDefinition, $settings, $third_party_settings, $this->stringTranslation, $this->paymentLineItemManager);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->getMock(ContainerInterface::class);
    $map = [
      ['plugin.manager.payment.line_item', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentLineItemManager],
      ['string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation],
    ];
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $field_definition = $this->getMock(FieldDefinitionInterface::class);
    $configuration = [
      'field_definition' => $field_definition,
      'settings' => [],
      'third_party_settings' => [],
    ];
    $plugin_definition = [];
    $plugin_id = $this->randomMachineName();
    $sut = PaymentForm::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf(PaymentForm::class, $sut);
  }

  /**
   * @covers ::settingsSummary
   */
  public function testSettingsSummaryWithOneLineItem() {
    $line_items_data = [
      [
        'plugin_id' => $this->randomMachineName(),
        'plugin_configuration' => [],
      ],
    ];
    $this->sut->setSetting('line_items', $line_items_data);
    $this->stringTranslation->expects($this->any())
      ->method('formatPlural')
      ->with(1);
    $this->sut->settingsSummary();
  }

  /**
   * @covers ::settingsSummary
   */
  public function testSettingsSummaryWithMultipleLineItems() {
    $line_items_data = [
      [
        'plugin_id' => $this->randomMachineName(),
        'plugin_configuration' => [],
      ],
      [
        'plugin_id' => $this->randomMachineName(),
        'plugin_configuration' => [],
      ]
    ];
    $this->sut->setSetting('line_items', $line_items_data);
    $this->stringTranslation->expects($this->any())
      ->method('formatPlural')
      ->with(2);
    $this->sut->settingsSummary();
  }

  /**
   * @covers ::formElement
   */
  public function testFormElement() {
    $items = $this->getMockBuilder(FieldItemList::class)
      ->disableOriginalConstructor()
      ->getMock();;
    $delta = 0;
    $element = [];
    $form = [];
    $form_state = $this->getMock(FormStateInterface::class);

    $this->assertInternalType('array', $this->sut->formElement($items, $delta, $element, $form, $form_state));
  }

  /**
   * @covers ::formElementProcess
   */
  public function testFormElementProcess() {
    $field_storage_definition = $this->getMock(FieldStorageDefinitionInterface::class);

    $this->fieldDefinition->expects($this->atLeastOnce())
      ->method('getFieldStorageDefinition')
      ->willReturn($field_storage_definition);

    $iterator = new \ArrayIterator([
      (object) [
      'plugin_configuration' => [],
      'plugin_id' => $this->randomMachineName(),
    ]
    ]);
    $items = $this->getMockBuilder(FieldItemList::class)
      ->disableOriginalConstructor()
      ->setMethods(['getIterator'])
      ->getMock();
    $items->expects($this->once())
      ->method('getIterator')
      ->willReturn($iterator);

    $element = [
      '#array_parents' => ['line_items'],
      '#items' => $items,
    ];
    $form = [];
    $form_state = $this->getMock(FormStateInterface::class);

    $element = $this->sut->formElementProcess($element, $form_state, $form);
    $this->assertInternalType('array', $element);
    $this->arrayHasKey('array_parents', $element);
    $this->arrayHasKey('line_items', $element);
  }

}
