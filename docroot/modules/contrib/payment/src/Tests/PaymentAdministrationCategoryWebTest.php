<?php

namespace Drupal\payment\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests the Payment category in the administration UI.
 *
 * @group Payment
 */
class PaymentAdministrationCategoryWebTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('payment');

  /**
   * Tests administrative overview.
   */
  protected function testOverview() {
    $this->drupalGet('admin/config/services');
    $this->assertNoLink('Payment');
    $this->drupalGet('admin/config/services/payment');
    $this->assertResponse('403');
    $this->drupalLogin($this->drupalCreateUser(array('access administration pages')));
    $this->drupalGet('admin/config');
    $this->drupalGet('admin/config/services');
    $this->assertLink('Payment');
    $this->drupalGet('admin/config/services/payment');
    $this->assertResponse('200');
  }
}
