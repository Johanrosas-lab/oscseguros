<?php

namespace Drupal\Tests\payment\Unit\Plugin\Payment\Method;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\Token;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Plugin\Payment\Method\Unavailable;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\Method\Unavailable
 *
 * @group Payment
 */
class UnavailableTest extends UnitTestCase {

  /**
   * The plugin definition.
   *
   * @var array
   */
  protected $pluginDefinition;

  /**
   * The payment status manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStatusManager;

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\Unavailable
   */
  protected $sut;

  /**
   * The token utility.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->token = $this->getMockBuilder(Token::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentStatusManager = $this->getMock(PaymentStatusManagerInterface::class);

    $this->pluginDefinition = array(
      'label' => $this->randomMachineName(),
    );

    $this->sut = new Unavailable([], '', $this->pluginDefinition, $this->token, $this->paymentStatusManager);
  }

  /**
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration() {
    $this->assertSame([], $this->sut->defaultConfiguration());
  }

  /**
   * @covers ::getPluginLabel
   */
  public function testGetPluginLabel() {
    $this->assertSame($this->pluginDefinition['label'], $this->sut->getPluginLabel());
  }

  /**
   * @covers ::calculateDependencies
   */
  public function testCalculateDependencies() {
    $this->assertSame([], $this->sut->calculateDependencies());
  }

  /**
   * @covers ::getConfiguration
   */
  public function testGetConfiguration() {
    $this->assertSame([], $this->sut->getConfiguration());
  }

  /**
   * @covers ::setConfiguration
   */
  public function testSetConfiguration() {
    $this->assertSame($this->sut, $this->sut->setConfiguration([]));
  }

  /**
   * @covers ::getSupportedCurrencies
   */
  public function testGetSupportedCurrencies() {
    $method = new \ReflectionMethod($this->sut, 'getSupportedCurrencies');
    $method->setAccessible(TRUE);

    $this->assertSame([], $method->invoke($this->sut));
  }

  /**
   * @covers ::setPayment
   * @covers ::getPayment
   */
  public function testGetPayment() {
    $payment = $this->getMock(PaymentInterface::class);

    $this->assertSame($this->sut, $this->sut->setPayment($payment));
    $this->assertSame($payment, $this->sut->getPayment());
  }

  /**
   * @covers ::buildConfigurationForm
   */
  public function testBuildConfigurationForm() {
    $form = [];
    $form_state = $this->getMock(FormStateInterface::class);
    $payment = $this->getMock(PaymentInterface::class);
    $elements = $this->sut->buildConfigurationForm($form, $form_state, $payment);
    $this->assertInternalType('array', $elements);
    $this->assertEmpty($elements);
  }

  /**
   * @covers ::validateConfigurationForm
   */
  public function testValidateConfigurationForm() {
    $form = [];
    $form_state = $this->getMock(FormStateInterface::class);
    $this->sut->validateConfigurationForm($form, $form_state);
  }

  /**
   * @covers ::submitConfigurationForm
   */
  public function testSubmitConfigurationForm() {
    $form = [];
    $form_state = $this->getMock(FormStateInterface::class);
    $this->sut->submitConfigurationForm($form, $form_state);
  }

  /**
   * @covers ::executePaymentAccess
   */
  public function testExecutePaymentAccess() {
    $account = $this->getMock(AccountInterface::class);

    $this->assertFalse($this->sut->executePaymentAccess($account)->isAllowed());
  }

  /**
   * @covers ::executePayment
   * @expectedException \RuntimeException
   */
  public function testExecutePayment() {
    $this->sut->executePayment();
  }

}
