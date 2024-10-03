<?php

namespace Drupal\Tests\payment\Unit\Entity\PaymentStatus {

  use Drupal\Core\Form\FormStateInterface;
  use Drupal\Core\StringTranslation\TranslatableMarkup;
  use Drupal\Core\Url;
  use Drupal\payment\Entity\PaymentStatus\PaymentStatusDeleteForm;
  use Drupal\payment\Entity\PaymentStatusInterface;
  use Drupal\Tests\UnitTestCase;
  use Psr\Log\LoggerInterface;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
   * @coversDefaultClass \Drupal\payment\Entity\PaymentStatus\PaymentStatusDeleteForm
   *
   * @group Payment
   */
  class PaymentStatusDeleteFormTest extends UnitTestCase {

    /**
     * The logger.
     *
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * The payment status.
     *
     * @var \Drupal\payment\Entity\PaymentStatusInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentStatus;

    /**
     * The string translator.
     *
     * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stringTranslation;

    /**
     * The class under test.
     *
     * @var \Drupal\payment\Entity\PaymentStatus\PaymentStatusDeleteForm
     */
    protected $sut;

    /**
     * {@inheritdoc}
     */
    public function setUp() {
      $this->logger = $this->getMock(LoggerInterface::class);

      $this->paymentStatus = $this->getMock(PaymentStatusInterface::class);

      $this->stringTranslation = $this->getStringTranslationStub();

      $this->sut = new PaymentStatusDeleteForm($this->stringTranslation, $this->logger);
      $this->sut->setEntity($this->paymentStatus);
    }

    /**
     * @covers ::create
     * @covers ::__construct
     */
    function testCreate() {
      $container = $this->getMock(ContainerInterface::class);
      $map = [
        ['payment.logger', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->logger],
        ['string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation],
      ];
      $container->expects($this->any())
        ->method('get')
        ->willReturnMap($map);

      $sut = PaymentStatusDeleteForm::create($container);
      $this->assertInstanceOf(PaymentStatusDeleteForm::class, $sut);
    }

    /**
     * @covers ::getQuestion
     */
    function testGetQuestion() {
      $this->assertInstanceOf(TranslatableMarkup::class, $this->sut->getQuestion());
    }

    /**
     * @covers ::getConfirmText
     */
    function testGetConfirmText() {
      $this->assertInstanceOf(TranslatableMarkup::class, $this->sut->getConfirmText());
    }

    /**
     * @covers ::getCancelUrl
     */
    function testGetCancelUrl() {
      $url = new Url($this->randomMachineName());

      $this->paymentStatus->expects($this->atLeastOnce())
        ->method('urlInfo')
        ->with('collection')
        ->willReturn($url);

      $cancel_url = $this->sut->getCancelUrl();
      $this->assertSame($url, $cancel_url);
    }

    /**
     * @covers ::submitForm
     */
    function testSubmitForm() {
      $this->logger->expects($this->atLeastOnce())
        ->method('info');

      $url = new Url($this->randomMachineName());

      $this->paymentStatus->expects($this->once())
        ->method('delete');
      $this->paymentStatus->expects($this->atLeastOnce())
        ->method('urlInfo')
        ->with('collection')
        ->willReturn($url);

      $form = [];
      $form_state = $this->getMock(FormStateInterface::class);
      $form_state->expects($this->once())
        ->method('setRedirectUrl')
        ->with($url);

      $this->sut->submitForm($form, $form_state);
    }

  }

}

namespace {

if (!function_exists('drupal_set_message')) {
  function drupal_set_message() {}
}

}
