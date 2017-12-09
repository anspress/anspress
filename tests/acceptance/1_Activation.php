<?php
class AcpActivationTest extends PHPUnit_Extensions_Selenium2TestCase {
  public function setUp() {
    $this->setHost('localhost');
    $this->setPort(4444);
    $this->setBrowserUrl('http://anspress.local');
  }

  public function tearDown() {
      $this->stop();
  }

  public function testValidFormSubmission(){
    $this->url('/');
    //$this->fillFormAndSubmit($inputs);

    $content = $this->byTag('body')->text();
    $this->assertEquals('Everything is Good!', $content);
  }
}