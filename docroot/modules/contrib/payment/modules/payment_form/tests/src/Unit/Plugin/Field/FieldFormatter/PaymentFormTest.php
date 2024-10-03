<?php

namespace Drupal\Tests\payment_form\Unit\Plugin\Field\FieldFormatter;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface;
use Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface;
use Drupal\payment_form\Plugin\Field\FieldFormatter\PaymentForm;
use Drupal\payment_form\Plugin\Field\FieldType\PaymentForm as PaymentFormFieldType;
use Drupal\payment_form\Plugin\Payment\Type\PaymentForm as PaymentFormPaymentType;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @coversDefaultClass \Drupal\payment_form\Plugin\Field\FieldFormatter\PaymentForm
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
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityFormBuilder;

  /**
   * The payment line item manager.
   *
   * @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentLineItemManager;

  /**
   * The payment storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStorage;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $renderer;

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $request;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $requestStack;

  /**
   * The subject under test.
   *
   * @var \Drupal\payment_form\Plugin\Field\FieldFormatter\PaymentForm
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->entityFormBuilder = $this->getMock(EntityFormBuilderInterface::class);

    $this->paymentLineItemManager = $this->getMock(PaymentLineItemManagerInterface::class);

    $this->fieldDefinition = $this->getMock(FieldDefinitionInterface::class);

    $this->renderer = $this->getMock(RendererInterface::class);

    $this->request = $this->getMockBuilder(Request::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->requestStack = $this->getMockBuilder(RequestStack::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->requestStack->expects($this->any())
      ->method('getCurrentRequest')
      ->willReturn($this->request);

    $this->paymentStorage = $this->getMock(EntityStorageInterface::class);

    $this->sut = new PaymentForm($this->randomMachineName(), [], $this->fieldDefinition, [], $this->randomMachineName(), $this->randomMachineName(), [], $this->requestStack, $this->entityFormBuilder, $this->paymentStorage, $this->paymentLineItemManager);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  public function testCreate() {

    $entity_manager = $this->getMock(EntityManagerInterface::class);
    $entity_manager->expects($this->atLeastOnce())
      ->method('getStorage')
      ->with('payment')
      ->willReturn($this->paymentStorage);

    $container = $this->getMock(ContainerInterface::class);
    $map = [
      ['entity.form_builder', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityFormBuilder],
      ['entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_manager],
      ['plugin.manager.payment.line_item', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentLineItemManager],
      ['request_stack', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->requestStack],
    ];
    $container->expects($this->atLeastOnce())
      ->method('get')
      ->willReturnMap($map);

    $plugin_configuration = [
      'field_definition' => $this->fieldDefinition,
      'label' => $this->randomMachineName(),
      'settings' => [],
      'third_party_settings' => [],
      'view_mode' => $this->randomMachineName(),
    ];

    $this->sut = PaymentForm::create($container, $plugin_configuration, $this->randomMachineName(), []);
  }

  /**
   * @covers ::viewElements
   */
  public function testViewElements() {
    $entity_type_id = $this->randomMachineName();
    $bundle = $this->randomMachineName();
    $field_name = $this->randomMachineName();
    $destination_url = $this->randomMachineName();
    $currency_code = $this->randomMachineName();

    $plugin_id = $this->randomMachineName();
    $plugin_configuration = [
      $this->randomMachineName() => $this->randomMachineName(),
    ];

    $plugin_id_property = $this->getMock(TypedDataInterface::class);
    $plugin_id_property->expects($this->once())
      ->method('getValue')
      ->willReturn($plugin_id);
    $plugin_configuration_property = $this->getMock(TypedDataInterface::class);
    $plugin_configuration_property->expects($this->once())
      ->method('getValue')
      ->willReturn($plugin_configuration);
    $map = [
      ['plugin_id', $plugin_id_property],
      ['plugin_configuration', $plugin_configuration_property],
    ];
    $item = $this->getMockBuilder(PaymentFormFieldType::class)
      ->disableOriginalConstructor()
      ->getMock();
    $item->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap($map);

    $entity = $this->getMock(EntityInterface::class);
    $entity->expects($this->atLeastOnce())
      ->method('bundle')
      ->willReturn($bundle);
    $entity->expects($this->atLeastOnce())
      ->method('getEntityTypeId')
      ->willReturn($entity_type_id);

    $iterator = new \ArrayIterator([$item]);
    $items = $this->getMockBuilder(FieldItemList::class)
      ->disableOriginalConstructor()
      ->setMethods(['getEntity', 'getIterator'])
      ->getMock();
    $items->expects($this->atLeastOnce())
      ->method('getEntity')
      ->willReturn($entity);
    $items->expects($this->atLeastOnce())
      ->method('getIterator')
      ->willReturn($iterator);

    $this->fieldDefinition->expects($this->once())
      ->method('getName')
      ->willReturn($field_name);
    $this->fieldDefinition->expects($this->atLeastOnce())
      ->method('getSetting')
      ->with('currency_code')
      ->willReturn($currency_code);

    $payment_type = $this->getMockBuilder(PaymentFormPaymentType::class)
      ->disableOriginalConstructor()
      ->getMock();
    $payment_type->expects($this->once())
      ->method('setEntityTypeId')
      ->with($entity_type_id);
    $payment_type->expects($this->once())
      ->method('setBundle')
      ->with($bundle);
    $payment_type->expects($this->once())
      ->method('setFieldName')
      ->with($field_name);
    $payment_type->expects($this->once())
      ->method('setDestinationUrl')
      ->with($destination_url);

    $payment = $this->getMock(PaymentInterface::class);
    $payment->expects($this->once())
      ->method('setCurrencyCode')
      ->with($currency_code);
    $payment->expects($this->once())
      ->method('getPaymentType')
      ->willReturn($payment_type);

    $payment_line_item = $this->getMock(PaymentLineItemInterface::class);

    $this->paymentLineItemManager->expects($this->once())
      ->method('createInstance')
      ->with($plugin_id, $plugin_configuration)
      ->willReturn($payment_line_item);

    $this->paymentStorage->expects($this->once())
      ->method('create')
      ->with([
        'bundle' => 'payment_form',
      ])
      ->willReturn($payment);

    $this->request->expects($this->atLeastOnce())
      ->method('getUri')
      ->willReturn($destination_url);

    $form = [
      '#foo' => $this->randomMachineName(),
    ];

    $this->entityFormBuilder->expects($this->once())
      ->method('getForm')
      ->with($payment, 'payment_form')
      ->willReturn($form);

    $this->assertSame($form, $this->sut->viewElements($items, 'en'));
  }

}
