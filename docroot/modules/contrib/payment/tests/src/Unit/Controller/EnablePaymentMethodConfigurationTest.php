<?php

namespace Drupal\Tests\payment\Unit\Controller;

use Drupal\payment\Controller\EnablePaymentMethodConfiguration;
use Drupal\payment\Entity\PaymentMethodConfigurationInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @coversDefaultClass \Drupal\payment\Controller\EnablePaymentMethodConfiguration
 *
 * @group Payment
 */
class EnablePaymentMethodConfigurationTest extends UnitTestCase {

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Controller\EnablePaymentMethodConfiguration
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->sut = new EnablePaymentMethodConfiguration();
  }

  /**
   * @covers ::execute
   */
  public function testExecute() {
    $url = 'http://example.com/' . $this->randomMachineName();

    $payment_method_configuration = $this->getMock(PaymentMethodConfigurationInterface::class);
    $payment_method_configuration->expects($this->once())
      ->method('enable');
    $payment_method_configuration->expects($this->once())
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
