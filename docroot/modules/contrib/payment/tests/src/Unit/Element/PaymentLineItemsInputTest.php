<?php

namespace Drupal\Tests\payment\Unit\Element {

  use Drupal\Component\Utility\Html;
  use Drupal\Core\Ajax\AjaxResponse;
  use Drupal\Core\DependencyInjection\Container;
  use Drupal\Core\Form\FormState;
  use Drupal\Core\Form\FormStateInterface;
  use Drupal\Core\Render\RendererInterface;
  use Drupal\payment\Element\PaymentLineItemsInput;
  use Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface;
  use Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface;
  use Drupal\Tests\UnitTestCase;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
   * @coversDefaultClass \Drupal\payment\Element\PaymentLineItemsInput
   *
   * @group Payment
   */
  class PaymentLineItemsInputTest extends UnitTestCase {

    /**
     * The payment line item manager.
     *
     * @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentLineItemManager;

    /**
     * The renderer.
     *
     * @var \Drupal\Core\Render\RendererInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $renderer;

    /**
     * The string translator.
     *
     * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stringTranslation;

    /**
     * The class under test.
     *
     * @var \Drupal\payment\Element\PaymentLineItemsInput
     */
    protected $sut;

    /**
     * {@inheritdoc}
     */
    public function setUp() {
      $this->paymentLineItemManager = $this->getMock(PaymentLineItemManagerInterface::class);

      $this->renderer = $this->getMock(RendererInterface::class);

      $this->stringTranslation = $this->getStringTranslationStub();

      $container = new Container();
      $container->set('renderer', $this->renderer);
      \Drupal::setContainer($container);

      $configuration = [];
      $plugin_id = $this->randomMachineName();
      $plugin_definition = [];
      $this->sut = new PaymentLineItemsInput($configuration, $plugin_id, $plugin_definition, $this->stringTranslation, $this->renderer, $this->paymentLineItemManager);
    }

    /**
     * @covers ::create
     * @covers ::__construct
     */
    function testCreate() {
      $container = $this->getMock(ContainerInterface::class);
      $map = array(
        array('plugin.manager.payment.line_item', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentLineItemManager),
        array('renderer', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->renderer),
        array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
      );
      $container->expects($this->any())
        ->method('get')
        ->willReturnMap($map);

      $configuration = [];
      $plugin_id = $this->randomMachineName();
      $plugin_definition = [];

      $sut = PaymentLineItemsInput::create($container, $configuration, $plugin_id, $plugin_definition);
      $this->assertInstanceOf(PaymentLineItemsInput::class, $sut);
     }

    /**
     * @covers ::getInfo
     */
    public function testGetInfo() {
      $info = $this->sut->getInfo();
      $this->assertInternalType('array', $info);
      foreach ($info['#process'] as $callback) {
        $this->assertTrue(is_callable($callback));
      }
    }

    /**
     * @covers ::process
     *
     * @expectedException \InvalidArgumentException
     */
    public function testProcessWithInvalidCardinality() {
      $line_item_a = $this->getMock(PaymentLineItemInterface::class);
      $line_item_b = $this->getMock(PaymentLineItemInterface::class);
      $line_items = array($line_item_a, $line_item_b);

      $element = array(
        '#cardinality' => 1,
        '#default_value' => $line_items,
        '#name' => $this->randomMachineName(),
      );
      $form_state = $this->getMock(FormStateInterface::class);
      $form = [];
      $this->sut->process($element, $form_state, $form);
    }

    /**
     * @covers ::process
     *
     * @expectedException \InvalidArgumentException
     */
    public function testProcessWithInvalidDefaultValue() {
      $line_item_a = $this->getMock(PaymentLineItemInterface::class);
      $line_item_b = $this->randomMachineName();
      $line_items = array($line_item_a, $line_item_b);

      $element = array(
        '#cardinality' => PaymentLineItemsInput::CARDINALITY_UNLIMITED,
        '#default_value' => $line_items,
        '#name' => $this->randomMachineName(),
      );
      $form_state = $this->getMock(FormStateInterface::class);
      $form = [];
      $this->sut->process($element, $form_state, $form);
    }

    /**
     * @covers ::process
     */
    public function testProcess() {
      $form_state = new FormState();
      $form = [];

      $line_item_name_a = $this->randomMachineName();
      $line_item_configuration_form_a = array(
        '#foo' => $this->randomMachineName(),
      );
      $line_item_a = $this->getMock(PaymentLineItemInterface::class);
      $line_item_a->expects($this->atLeastOnce())
        ->method('buildConfigurationForm')
        ->with([], $form_state)
        ->willReturn($line_item_configuration_form_a);
      $line_item_a->expects($this->atLeastOnce())
        ->method('getName')
        ->willReturn($line_item_name_a);
      $line_item_name_b = $this->randomMachineName();
      $line_item_configuration_form_b = array(
        '#foo' => $this->randomMachineName(),
      );
      $line_item_b = $this->getMock(PaymentLineItemInterface::class);
      $line_item_b->expects($this->atLeastOnce())
        ->method('buildConfigurationForm')
        ->with([], $form_state)
        ->willReturn($line_item_configuration_form_b);
      $line_item_b->expects($this->atLeastOnce())
        ->method('getName')
        ->willReturn($line_item_name_b);
      $line_items = array($line_item_a, $line_item_b);

      $line_item_id_a = $this->randomMachineName();
      $line_item_id_b = $this->randomMachineName();
      $line_item_definitions = [
        $line_item_id_a => [
          'id' => $line_item_id_a,
          'label' => $this->randomMachineName(),
        ],
        $line_item_id_b => [
          'id' => $line_item_id_b,
          'label' => $this->randomMachineName(),
        ],
      ];

      $this->paymentLineItemManager->expects($this->atLeastOnce())
        ->method('getDefinitions')
        ->willReturn($line_item_definitions);

      $element = array(
        '#cardinality' => PaymentLineItemsInput::CARDINALITY_UNLIMITED,
        '#default_value' => $line_items,
        '#name' => $this->randomMachineName(),
        '#parents' => [],
      );

      $element = $this->sut->process($element, $form_state, $form);

      $this->assertArrayHasKey($line_item_name_a, $element['line_items']);
      $this->assertSame($line_item_configuration_form_a, $element['line_items'][$line_item_name_a]['plugin_form']);
      $this->assertArrayHasKey('delete', $element['line_items'][$line_item_name_a]);
      $this->assertArrayHasKey($line_item_name_b, $element['line_items']);
      $this->assertSame($line_item_configuration_form_b, $element['line_items'][$line_item_name_b]['plugin_form']);
      $this->assertArrayHasKey('delete', $element['line_items'][$line_item_name_b]);
      $this->assertArrayHasKey('add_more', $element);
      $this->assertArrayHasKey('add', $element['add_more']);
      $this->assertArrayHasKey('type', $element['add_more']);
    }

    /**
     * @covers ::setLineItems
     * @covers ::getLineItems
     */
    public function testGetLineItems() {
      $line_item_a = $this->getMock(PaymentLineItemInterface::class);
      $line_item_b = $this->getMock(PaymentLineItemInterface::class);
      $line_items = array($line_item_a, $line_item_b);

      $element = array(
        '#name' => $this->randomMachineName(),
      );
      $form_state = new FormState();

      $method_get = new \ReflectionMethod($this->sut, 'getLineItems');
      $method_get->setAccessible(TRUE);

      $method_set = new \ReflectionMethod($this->sut, 'setLineItems');
      $method_set->setAccessible(TRUE);

      $this->assertSame([], $method_get->invoke($this->sut, $element, $form_state));
      $method_set->invoke($this->sut, $element, $form_state, $line_items);
      $this->assertSame($line_items, $method_get->invoke($this->sut, $element, $form_state));
    }

    /**
     * @covers ::initializeLineItems
     *
     * @depends testGetLineItems
     */
    public function testInitializeLineItems() {
      $line_item_a = $this->getMock(PaymentLineItemInterface::class);
      $line_item_b = $this->getMock(PaymentLineItemInterface::class);
      $line_items = array($line_item_a, $line_item_b);

      $element = array(
        '#name' => $this->randomMachineName(),
        '#default_value' => $line_items,
      );
      $form_state = new FormState();

      $method_get = new \ReflectionMethod($this->sut, 'getLineItems');
      $method_get->setAccessible(TRUE);

      $method_set = new \ReflectionMethod($this->sut, 'setLineItems');
      $method_set->setAccessible(TRUE);

      $method_initialize = new \ReflectionMethod($this->sut, 'initializeLineItems');
      $method_initialize->setAccessible(TRUE);

      $this->assertSame([], $method_get->invoke($this->sut, $element, $form_state));
      $method_initialize->invoke($this->sut, $element, $form_state);
      $this->assertSame($line_items, $method_get->invoke($this->sut, $element, $form_state));
      $method_set->invoke($this->sut, $element, $form_state, []);
      $this->assertSame([], $method_get->invoke($this->sut, $element, $form_state));
    }

    /**
     * @covers ::valueCallback
     *
     * @depends testGetLineItems
     */
    public function testValueCalback() {
      $line_item = $this->getMock(PaymentLineItemInterface::class);
      $line_items = array($line_item);

      $element = array(
        '#name' => $this->randomMachineName(),
      );
      $form_state = new FormState();

      $method = new \ReflectionMethod($this->sut, 'setLineItems');
      $method->setAccessible(TRUE);

      $element_plugin = $this->sut;

      $method->invoke($this->sut, $element, $form_state, $line_items);
      $this->assertSame($line_items, $element_plugin::valueCallback($element, TRUE, $form_state));
      $this->assertSame($line_items, $element_plugin::valueCallback($element, FALSE, $form_state));
    }

    /**
     * @covers ::lineItemExists
     *
     * @depends testGetLineItems
     */
    public function testLineItemExists() {
      $line_item_name_a = $this->randomMachineName();
      $line_item_a = $this->getMock(PaymentLineItemInterface::class);
      $line_item_a->expects($this->atLeastOnce())
        ->method('getName')
        ->willReturn($line_item_name_a);
      $line_item_name_b = $this->randomMachineName();
      $line_item_b = $this->getMock(PaymentLineItemInterface::class);
      $line_item_b->expects($this->atLeastOnce())
        ->method('getName')
        ->willReturn($line_item_name_b);
      $line_items = array($line_item_a, $line_item_b);

      $element = array(
        '#name' => $this->randomMachineName(),
      );
      $form_state = new FormState();

      $method_set = new \ReflectionMethod($this->sut, 'setLineItems');
      $method_set->setAccessible(TRUE);

      $method_exists = new \ReflectionMethod($this->sut, 'lineItemExists');
      $method_exists->setAccessible(TRUE);

      $method_set->invoke($this->sut, $element, $form_state, $line_items);
      $this->assertTrue($method_exists->invoke($this->sut, $element, $form_state, $line_item_name_a));
      $this->assertTrue($method_exists->invoke($this->sut, $element, $form_state, $line_item_name_b));
      $this->assertFalse($method_exists->invoke($this->sut, $element, $form_state, $this->randomMachineName()));
    }

    /**
     * @covers ::createLineItemName
     *
     * @depends testGetLineItems
     * @depends testLineItemExists
     */
    public function testCreateLineItemName() {
      $line_item_name_a = $this->randomMachineName();
      $line_item_a = $this->getMock(PaymentLineItemInterface::class);
      $line_item_a->expects($this->atLeastOnce())
        ->method('getName')
        ->willReturn($line_item_name_a);
      $line_item_name_b = $this->randomMachineName();
      $line_item_b = $this->getMock(PaymentLineItemInterface::class);
      $line_item_b->expects($this->atLeastOnce())
        ->method('getName')
        ->willReturn($line_item_name_b);
      $line_items = array($line_item_a, $line_item_b);

      $element = array(
        '#name' => $this->randomMachineName(),
      );
      $form_state = new FormState();

      $method_set = new \ReflectionMethod($this->sut, 'setLineItems');
      $method_set->setAccessible(TRUE);

      $method_create = new \ReflectionMethod($this->sut, 'createLineItemName');
      $method_create->setAccessible(TRUE);

      $method_set->invoke($this->sut, $element, $form_state, $line_items);
      $this->assertSame($line_item_name_a . '1', $method_create->invoke($this->sut, $element, $form_state, $line_item_name_a));
      $this->assertSame($line_item_name_b . '1', $method_create->invoke($this->sut, $element, $form_state, $line_item_name_b));
      $line_item_name_c = $this->randomMachineName();
      $this->assertSame($line_item_name_c, $method_create->invoke($this->sut, $element, $form_state, $line_item_name_c));
    }

    /**
     * @covers ::addMoreSubmit
     */
    public function testAddMoreSubmit() {
      $plugin_id = $this->randomMachineName();

      $values = array(
        'add_more' => array(
          'type' => $plugin_id,
        ),
      );

      $line_item = $this->getMock(PaymentLineItemInterface::class);
      $line_item->expects($this->once())
        ->method('setName')
        ->with($plugin_id);

      $this->paymentLineItemManager->expects($this->once())
        ->method('createInstance')
        ->with($plugin_id)
        ->willReturn($line_item);

      $form_build = array(
        'foo' => array(
          '#name' => $this->randomMachineName(),
          'add_more' => array(
            'add' => array(
              '#array_parents' => array('foo', 'add_more', 'add'),
              '#parents' => [],
            ),
          ),
        ),
      );

      $form_state = new FormState();
      $form_state->setTriggeringElement($form_build['foo']['add_more']['add']);
      $form_state->setValues($values);

      $this->sut->addMoreSubmit($form_build, $form_state);
      $this->assertTrue($form_state->isRebuilding());
      $element = $this->sut;
      $line_items = $element::getLineItems($form_build['foo'], $form_state);
      $this->assertTrue(in_array($line_item, $line_items, TRUE));
    }

    /**
     * @covers ::ajaxAddMoreSubmit
     */
    public function testAjaxAddMoreSubmit() {
      $form_build = array(
        'foo' => array(
          '#id' => $this->randomMachineName(),
          '#name' => $this->randomMachineName(),
          'add_more' => array(
            'add' => array(
              '#array_parents' => array('foo', 'add_more', 'add'),
              '#parents' => [],
            ),
          ),
        ),
      );

      $form_state = new FormState();
      $form_state->setTriggeringElement($form_build['foo']['add_more']['add']);

      $response = $this->sut->ajaxAddMoreSubmit($form_build, $form_state);
      $this->assertInstanceOf(AjaxResponse::class, $response);
    }

    /**
     * @covers ::deleteSubmit
     */
    public function testDeleteSubmit() {
      $line_item_name = $this->randomMachineName();
      $root_element_name = $this->randomMachineName();

      $line_item_a = $this->getMock(PaymentLineItemInterface::class);
      $line_item_a->expects($this->once())
        ->method('getName')
        ->willReturn($this->randomMachineName());
      $line_item_b = $this->getMock(PaymentLineItemInterface::class);
      $line_item_b->expects($this->once())
        ->method('getName')
        ->willReturn($line_item_name);
      $line_item_c = $this->getMock(PaymentLineItemInterface::class);
      $line_item_c->expects($this->once())
        ->method('getName')
        ->willReturn($this->randomMachineName());

      $form_build = array(
        'foo' => array(
          '#name' => $root_element_name,
          'line_items' => array(
            $line_item_name => array(
              'delete' => array(
                '#array_parents' => array('foo', 'line_items', $line_item_name, 'delete'),
                '#parents' => [],
              ),
            ),
          ),
        ),
      );

      $form_state = new FormState();
      $form_state->set('payment.element.payment_line_items_input.configured.' . $root_element_name, array($line_item_a, $line_item_b, $line_item_c));
      $form_state->setTriggeringElement($form_build['foo']['line_items'][$line_item_name]['delete']);

      $this->sut->deleteSubmit($form_build, $form_state);
      $this->assertTrue($form_state->isRebuilding());
      $element = $this->sut;
      $line_items = $element::getLineItems($form_build['foo'], $form_state);
      $this->assertTrue(in_array($line_item_a, $line_items, TRUE));
      $this->assertFalse(in_array($line_item_b, $line_items, TRUE));
      $this->assertTrue(in_array($line_item_c, $line_items, TRUE));
    }

    /**
     * @covers ::ajaxDeleteSubmit
     */
    public function testAjaxDeleteSubmit() {
      $line_item_name = $this->randomMachineName();
      $root_element_name = $this->randomMachineName();

      $form_build = array(
        'foo' => array(
          '#id' => $this->randomMachineName(),
          '#name' => $root_element_name,
          'line_items' => array(
            $line_item_name => array(
              'delete' => array(
                '#array_parents' => array('foo', 'line_items', $line_item_name, 'delete'),
                '#parents' => [],
              ),
            ),
          ),
        ),
      );

      $form_state = new FormState();
      $form_state->setTriggeringElement($form_build['foo']['line_items'][$line_item_name]['delete']);

      $element = $this->sut;
      $response = $element::ajaxDeleteSubmit($form_build, $form_state);
      $this->assertInstanceOf(AjaxResponse::class, $response);
    }

    /**
     * @covers ::getElementId
     */
    public function testGetElementId() {
      $element_build = array(
        '#name' => $this->randomMachineName(),
      );

      $id_prefix = Html::getId('payment-element-payment_line_items_input');

      $form_state = new FormState();

      $method = new \ReflectionMethod($this->sut, 'getElementId');
      $method->setAccessible(TRUE);

      // Check twice, because once the ID has been set it must not change.
      $id = $method->invoke($this->sut, $element_build, $form_state);
      $this->assertSame(0, strpos($id, $id_prefix));
      $this->assertSame($id, $method->invoke($this->sut, $element_build, $form_state));
    }

    /**
     * @covers ::validate
     */
    public function testValidate() {
      $line_item_name_a = $this->randomMachineName();
      $line_item_name_b = $this->randomMachineName();
      $line_item_name_c = $this->randomMachineName();
      $root_element_name = $this->randomMachineName();

      $form_build = array(
        'foo' => array(
          '#name' => $root_element_name,
          '#parents' => array('foo'),
          // The line items are built below.
          'line_items' => [],
        ),
      );

      $line_item_a = $this->getMock(PaymentLineItemInterface::class);
      $line_item_b = $this->getMock(PaymentLineItemInterface::class);
      $line_item_c = $this->getMock(PaymentLineItemInterface::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject[] $line_items */
      $line_items = array(
        $line_item_name_a => $line_item_a,
        $line_item_name_b => $line_item_b,
        $line_item_name_c => $line_item_c,
      );
      foreach ($line_items as $line_item_name => $line_item) {
        $form_build['foo']['line_items'][$line_item_name] = array(
          'plugin_form' => array(
            '#foo' => $this->randomMachineName(),
          ),
        );

        $line_item->expects($this->atLeastOnce())
          ->method('getName')
          ->willReturn($line_item_name);
        $line_item->expects($this->once())
          ->method('validateConfigurationForm')
          ->with($form_build['foo']['line_items'][$line_item_name]['plugin_form']);
        $line_item->expects($this->once())
          ->method('submitConfigurationForm')
          ->with($form_build['foo']['line_items'][$line_item_name]['plugin_form']);
      }

      $form_state = new FormState();
      $form_state->set('payment.element.payment_line_items_input.configured.' . $root_element_name, array_values($line_items));
      $form_state->setValues(array(
        'foo' => array(
          'line_items' => array(
            $line_item_name_a => array(
              'weight' => 3,
            ),
            $line_item_name_b => array(
              'weight' => 1,
            ),
            $line_item_name_c => array(
              'weight' => 2,
            ),
          ),
        ),
      ));

      $this->sut->validate($form_build['foo'], $form_state, $form_build);
      $element = $this->sut;
      $line_items = $element::getLineItems($form_build['foo'], $form_state);
      $this->assertSame(array($line_item_b, $line_item_c, $line_item_a), $line_items);
    }

  }

}

namespace {

  if (!defined('RESPONSIVE_PRIORITY_LOW')) {
    define('RESPONSIVE_PRIORITY_LOW', 'priority-low');
  }
  if (!defined('RESPONSIVE_PRIORITY_MEDIUM')) {
    define('RESPONSIVE_PRIORITY_MEDIUM', 'priority-medium');
  }

}
