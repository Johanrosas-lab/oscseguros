<?php

namespace Drupal\Tests\payment\Unit\Plugin\views\argument_validator;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Plugin\views\argument_validator\ViewPaymentsByOwner;
use Drupal\Tests\UnitTestCase;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\views\argument_validator\ViewPaymentsByOwner
 *
 * @group Payment
 */
class ViewPaymentsByOwnerTest extends UnitTestCase {

  /**
   * The current user
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currentUser;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * The plugin definition.
   *
   * @var mixed[]
   */
  protected $pluginDefinition = [];

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Plugin\views\argument_validator\ViewPaymentsByOwner
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->entityManager = $this->getMock(EntityManagerInterface::class);

    $this->currentUser = $this->getMock(AccountInterface::class);

    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $this->pluginDefinition = [
      'entity_type' => $this->randomMachineName(),
    ];
    $this->sut = new ViewPaymentsByOwner($configuration, $plugin_id, $this->pluginDefinition, $this->entityManager, $this->currentUser);
    $options = [
      'access' => FALSE,
      'bundles' => [],
      'multiple' => TRUE,
      'operation' => NULL,
    ];
    $view_executable = $this->getMockBuilder(ViewExecutable::class)
      ->disableOriginalConstructor()
      ->getMock();
    $display = $this->getMockBuilder(DisplayPluginBase::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();
    $this->sut->init($view_executable, $display, $options);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->getMock(ContainerInterface::class);
    $map = array(
      array('current_user', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->currentUser),
      array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityManager),
    );
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $sut = ViewPaymentsByOwner::create($container, $configuration, $plugin_id, $this->pluginDefinition);
    $this->assertInstanceOf(ViewPaymentsByOwner::class, $sut);
  }

  /**
   * @covers ::validateArgument
   */
  public function xtestValidateArgumentWithoutValidEntities() {
    $entity_storage = $this->getMock(EntityStorageInterface::class);
    $entity_storage->expects($this->atLeastOnce())
      ->method('loadMultiple')
      ->willReturn([]);

    $this->entityManager->expects($this->atLeastOnce())
      ->method('getStorage')
      ->with($this->pluginDefinition['entity_type'])
      ->willReturn($entity_storage);

    $argument = mt_rand();

    $this->assertFalse($this->sut->validateArgument($argument));
  }

  /**
   * @covers ::validateArgument
   *
   * @dataProvider providerValidateArgument
   */
  public function testValidateArgument($expected_validity, $argument, $current_user_id, array $permissions) {
    $entity = $this->getMock(EntityInterface::class);

    $entity_storage = $this->getMock(EntityStorageInterface::class);
    $entity_storage->expects($this->atLeastOnce())
      ->method('loadMultiple')
      ->willReturn([
        7 => $entity,
        9 => $entity,
      ]);

    $this->entityManager->expects($this->atLeastOnce())
      ->method('getStorage')
      ->with($this->pluginDefinition['entity_type'])
      ->willReturn($entity_storage);

    $this->currentUser->expects($this->any())
      ->method('id')
      ->willReturn($current_user_id);
    $map = [];
    foreach ($permissions as $permission) {
      $map[] = [$permission, TRUE];
    }
    $this->currentUser->expects($this->any())
      ->method('hasPermission')
      ->willReturnMap($map);

    $this->assertSame($expected_validity, $this->sut->validateArgument($argument));
  }

  /**
   * Provides data to self::testValidateArgument().
   */
  public function providerValidateArgument() {
    return [
      // Permissions to view own paymens only.
      [TRUE, '7', 7, ['payment.payment.view.own']],
      [FALSE, '7+9', 7, ['payment.payment.view.own']],
      [FALSE, '7,9', 7, ['payment.payment.view.own']],
      [FALSE, '9', 7, ['payment.payment.view.own']],

      // Permissions to view any payment.
      [TRUE, '7', 7, ['payment.payment.view.any']],
      [TRUE, '7+9', 7, ['payment.payment.view.any']],
      [TRUE, '7,9', 7, ['payment.payment.view.any']],

      // Permissions to view own and any payments.
      [TRUE, '7', 7, ['payment.payment.view.any', 'payment.payment.view.own']],
      [TRUE, '7+9', 7, ['payment.payment.view.any', 'payment.payment.view.own']],
      [TRUE, '7,9', 7, ['payment.payment.view.any', 'payment.payment.view.own']],
    ];
  }

}
