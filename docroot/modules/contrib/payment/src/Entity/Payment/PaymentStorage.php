<?php

namespace Drupal\payment\Entity\Payment;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Handles storage for payment entities.
 */
class PaymentStorage extends SqlContentEntityStorage {

  /**
   * The payment status manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface
   */
  protected $paymentStatusManager;

  /**
   * The payment type manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface
   */
  protected $paymentTypeManager;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The entity cache.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface $payment_status_manager
   *   The payment status manager.
   * @param \Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface $payment_type_manager
   *   The payment type manager.
   */
  public function __construct(EntityTypeInterface $entity_type, Connection $database, EntityManagerInterface $entity_manager, CacheBackendInterface $cache, LanguageManagerInterface $language_manager, PaymentStatusManagerInterface $payment_status_manager, PaymentTypeManagerInterface $payment_type_manager) {
    parent::__construct($entity_type, $database, $entity_manager, $cache, $language_manager);
    $this->paymentStatusManager = $payment_status_manager;
    $this->paymentTypeManager = $payment_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static($entity_type, $container->get('database'), $container->get('entity.manager'), $container->get('cache.entity'), $container->get('language_manager'), $container->get('plugin.manager.payment.status'), $container->get('plugin.manager.payment.type'));
  }

  /**
   * {@inheritdoc}
   */
  public function create(array $values = []) {
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = parent::create($values);
    $payment_type = $this->paymentTypeManager->createInstance($values['bundle']);
    $payment_type->setPayment($payment);
    $payment->get('payment_type')->setValue($payment_type);
    $status = $this->paymentStatusManager->createInstance('payment_created')
      ->setCreated(time());
    $payment->setPaymentStatus($status);

    return $payment;
  }

  /**
   * {@inheritdoc}
   */
  protected function mapToStorageRecord(ContentEntityInterface $entity, $table_name = NULL) {
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = $entity;

    $record = parent::mapToStorageRecord($entity, $table_name);
    $deltas = [];
    foreach ($payment->getPaymentStatuses() as $delta => $item) {
      $deltas[] = $delta;
    }
    $record->current_payment_status_delta = max($deltas);

    return $record;
  }

}
