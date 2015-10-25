<?php

/**
 * Helper class
 *
 * @category   Printminion
 * @package    Printminion_Privatbankrates
 * @author     Misha M.-Kupriyanov for Printminion
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */
class Printminion_Privatbankrates_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Path for the config for extension active status
     */
    const CONFIG_EXTENSION_ACTIVE = 'currency/pm_privatbankrates/enable';

    /**
     * Variable for if the extension is active
     *
     * @var bool
     */
    protected $bExtensionActive;

    /**
     * Check to see if the extension is active
     *
     * @return bool
     */
    public function isExtensionActive()
    {
        if ($this->bExtensionActive === null) {
            $this->bExtensionActive = Mage::getStoreConfigFlag(self::CONFIG_EXTENSION_ACTIVE);
        }
        return $this->bExtensionActive;
    }
}