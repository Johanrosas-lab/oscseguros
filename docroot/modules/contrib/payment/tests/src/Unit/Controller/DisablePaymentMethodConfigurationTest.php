<?php

namespace Drupal\Tests\payment\Unit\Controller;

use Drupal\payment\Controller\DisablePaymentMethodConfiguration;
use Drupal\payment\Entity\PaymentMethodConfigurationInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @coversDefaultClass \Drupal\payment\Controller\DisablePaymentMethodConfiguration
 *
 * @group Payment
 */
class DisablePaymentMethodConfigurationTest extends UnitTestCase {

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Controller\DisablePaymentMethodConfiguration
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->sut = new DisablePaymentMethodConfiguration();
  }

  /**
   * @covers ::execute
   */
  public function testExecute() {
    $url = 'http://example.com/' . $this->randomMachineName();

    $payment_method_configuration = $this->getMock(PaymentMethodConfigurationInterface::class);
    $payment_method_configuration->expects($this->atLeastOnce())
      ->method('disable');
    $payment_method_configuration->expects($this->atLeastOnce())
      ->method('save');
    $payment_method_configuration->expects($this->atLeastOnce())
      ->method('url')
      ->with('collection')
      ->willReturn($url);

    $response = $this->sut->execute($payment_method_configuration);
    $this->assertInstanceOf(RedirectResponse::class, $response);
    $this->assertSame($url, $response->getTargetUrl());
  }

}
