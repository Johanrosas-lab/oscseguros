<?php

namespace Drupal\payment_form\Tests\Plugin\Field\FieldFormatter;

use Drupal\field\FieldStorageConfigInterface;
use Drupal\payment\Tests\Generate;
use Drupal\payment_form\Plugin\Payment\Type\PaymentForm;
use Drupal\simpletest\WebTestBase;

/**
 * \Drupal\payment\Plugin\Field\FieldFormatter\PaymentForm web test.
 *
 * @group Payment Form Field
 */
class PaymentFormWebTest extends WebTestBase {

  /**
   * The payment method configuration used for testing.
   *
   * @var \Drupal\payment\Entity\PaymentMethodConfiguration
   */
  protected $paymentMethod;

  /**
   * The payment entity storage controller.
   *
   * @var \Drupal\payment\Entity\Payment\PaymentStorage
   */
  protected $paymentStorage;

  /**
   * The plugin ID of the status to set the payment to.
   *
   * @var string
   */
  protected $executeStatusPluginId = 'payment_pending';

  /**
   * The user to add the field to.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['field', 'filter', 'payment', 'payment_form'];

  /**
   * {@inheritdoc
   */
  protected function setUp() {
    parent::setUp();
    $this->paymentStorage = \Drupal::entityManager()->getStorage('payment');

    /** @var \Drupal\currency\ConfigImporterInterface $config_importer */
    $config_importer = \Drupal::service('currency.config_importer');
    $config_importer->importCurrency('EUR');

    // Create the field and field instance.
    $field_name = strtolower($this->randomMachineName());
    entity_create('field_storage_config', [
      'cardinality' => FieldStorageConfigInterface::CARDINALITY_UNLIMITED,
      'entity_type' => 'user',
      'field_name' => $field_name,
      'type' => 'payment_form',
    ])->save();
    entity_create('field_config', [
      'bundle' => 'user',
      'entity_type' => 'user',
      'field_name' => $field_name,
      'settings' => [
        'currency_code' => 'EUR',
      ],
    ])->save();
    entity_get_display('user', 'user', 'default')
      ->setComponent($field_name, [
        'type' => 'payment_form',
      ])
      ->save();

    // Create an entity.
    $this->user = entity_create('user', [
      'name' => $this->randomString(),
      'status' => TRUE,
    ]);
    foreach (Generate::createPaymentLineItems() as $line_item) {
      $this->user->get($field_name)->appendItem([
        'plugin_id' => $line_item->getPluginId(),
        'plugin_configuration' => $line_item->getConfiguration(),
      ]);
    }
    $this->user->save();

    // Create a payment method.
    $this->paymentMethod = Generate::createPaymentMethodConfiguration(2, 'payment_basic');
    $this->paymentMethod->setPluginConfiguration([
      'execute_status_id' => $this->executeStatusPluginId,
    ]);
    $this->paymentMethod->save();
  }

  /**
   * Tests the formatter().
   */
  protected function testFormatter() {
    // Make sure there are no payments yet.
    $this->assertEqual(count($this->paymentStorage->loadMultiple()), 0);
    $user = $this->drupalCreateUser(['access user profiles']);
    $this->drupalLogin($user);
    $path = 'user/' . $this->user->id();
    $this->drupalPostForm($path, [], t('Pay'));
    // The front page is the currently logged-in user.
    // @todo The following code does not work, as it results in the following
    // failure if this test is run on Drush' built-in server:
    // @code
    // Expected &#039;http://local.dev:8080/user/2&#039; matches current URL
    //
    // (http://local.dev:8080/user/2?q=user/2).
    //
    // Value &#039;http://local.dev:8080/user/2?q=user/2&#039; is equal to value
    //
    // &#039;http://local.dev:8080/user/2&#039;.
    // @endcode
    //
    // @code
    // $this->assertUrl($path);
    // @endcode

    $this->assertResponse('200');
    // This is supposed to be the first and only payment.
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = $this->paymentStorage->load(1);
    var_dump($payment->getPaymentType());
    if ($this->assertTrue((bool) $payment)) {
      $this->assertTrue($payment->getPaymentType() instanceof PaymentForm);
      $this->assertIdentical($payment->getPaymentStatus()->getPluginId(), $this->executeStatusPluginId);
    }
  }
}
