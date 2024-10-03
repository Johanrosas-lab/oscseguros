<?php

namespace Drupal\payment\Plugin\views\argument_validator;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Plugin\views\argument_validator\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Validates whether the current user has access to view a user's payments.
 *
 * @ViewsArgumentValidator(
 *   id = "payment_view_payments_by_owner",
 *   title = @Translation("Access to view a user's payments"),
 *   entity_type = "user"
 * )
 */
class ViewPaymentsByOwner extends User {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;
  /**
   * Constructs a new instance.
   *
   * @param mixed[] $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed[] $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, EntityManagerInterface $entity_manager, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_manager);
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('entity.manager'), $container->get('current_user'));
  }

  /**
   * {@inheritdoc}
   */
  public function validateArgument($argument) {
    if (!parent::validateArgument($argument)) {
      return FALSE;
    }

    // Extract the IDs from the argument. See parent::validateArgument().
    if ($this->multipleCapable && $this->options['multiple']) {
      $user_ids = array_filter(preg_split('/[,+ ]/', $argument));
    }
    else {
      $user_ids = [$argument];
    }

    // Allow access when the current user has access to view all payments, or
    // when the current user only tries to view their own payments and has
    // permission to do so.
    return [$this->currentUser->id()] == $user_ids && $this->currentUser->hasPermission('payment.payment.view.own') || $this->currentUser->hasPermission('payment.payment.view.any');
  }

}
