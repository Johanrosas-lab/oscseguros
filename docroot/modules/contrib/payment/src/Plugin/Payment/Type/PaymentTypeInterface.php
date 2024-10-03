<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Type\PaymentTypeInterface.
 */

namespace Drupal\payment\Plugin\Payment\Type;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\PaymentAwareInterface;

/**
 * A payment type plugin.
 *
 * Plugins can additionally implement the following interfaces:
 * - \Drupal\Component\Plugin\ConfigurablePluginInterface
 *   Required if the plugin has any internal configuration, so it can be
 *   exported for recreation of the plugin at a later time.
 */
interface PaymentTypeInterface extends PluginInspectionInterface, PaymentAwareInterface {

  /**
   * Returns the description of the payment this plugin is of.
   *
   * @param string|\Drupal\Core\StringTranslation\TranslationWrapper
   */
  public function getPaymentDescription();

  /**
   * Checks if the payment type context can be resumed.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *
   * @see self::getResumeContextResponse
   */
  public function resumeContextAccess(AccountInterface $account);

  /**
   * Resumes the payer's original workflow.
   *
   * @return \Drupal\payment\Response\ResponseInterface
   *
   * @see self::resumeContextAccess
   */
  public function getResumeContextResponse();

}
