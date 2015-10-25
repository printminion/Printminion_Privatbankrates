<?php

/**
* Test for class Printminion_Privatbankrates_Helper_Data
*
* @category    Printminion
* @package     Printminion_Privatbankrates
*/
class Printminion_Privatbankrates_Test_Helper_Data extends EcomDev_PHPUnit_Test_Case
{
  /**
  * Tests is extension active
  *
  * @test
  * @loadFixture
  */
  public function testIsExtensionActive()
  {
    $this->assertTrue(
      Mage::helper('pm_privatbankrates')->isExtensionActive(),
      'Extension is not active please check config'
    );
  }
}