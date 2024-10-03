<?php

namespace Drupal\payment\Tests;

use Drupal\payment\Entity\PaymentMethodConfigurationInterface;
use Drupal\simpletest\WebTestBase;

/**
 * Tests module installation and uninstallation.
 *
 * @group Payment
 */
class ModuleInstallUninstallWebTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('payment');

  /**
   * Test installation and uninstallation.
   */
  protected function testInstallationAndUninstallation() {
    /** @var \Drupal\Core\Extension\ModuleInstallerInterface $module_installer */
    $module_installer = \Drupal::service('module_installer');
    $module_handler = \Drupal::moduleHandler();
    $this->assertTrue($module_handler->moduleExists('payment'));

    // Test default configuration.
    $names = array('collect_on_delivery', 'no_payment_required');
    foreach ($names as $name) {
      $payment_method = entity_load('payment_method_configuration', $name);
      $this->assertTrue($payment_method instanceof PaymentMethodConfigurationInterface);
    }

    $module_installer->uninstall(array('payment'));
    $this->assertFalse($module_handler->moduleExists('payment'));
  }
}
