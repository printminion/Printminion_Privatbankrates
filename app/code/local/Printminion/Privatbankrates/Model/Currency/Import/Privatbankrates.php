<?php
/**
 * @category    Printminion
 * @package     Printminion_Privatbankrates
 * @author      Misha M.-Kupriyanov
 * @copyright   Copyright (c) 2015 Misha M.-Kupriyanov (http://kupriyanov.com)
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

/**
 * Currency rate import model (From www.webservicex.net)
 *
 * @category   Printminion
 * @package    Printminion_Privatbankrates
 * @author     Misha M.-Kupriyanov for Printminion
 */
class Printminion_Privatbankrates_Model_Currency_Import_Privatbankrates extends Mage_Directory_Model_Currency_Import_Abstract
{
    /**
     * Privatbankrates API URL
     * @var string
     */
    protected $_url = 'https://api.privatbank.ua/p24api/pubinfo?json&exchange&coursid=5';
    protected $_messages = array();

    /**
     * HTTP client
     *
     * @var Varien_Http_Client
     */
    protected $_httpClient;
    /**
     * Fetched and cached rates array
     * @var array
     */
    protected $_rates;

    /**
     * Initialise our object with the full rates retrieved from Privatbankrates as the
     * free version only allows us to get the complete set of rates. But that's ok, we are
     * caching it and then processing the individual rates
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->_httpClient = new Varien_Http_Client();

        $url = $this->_url;

        $response = $this->_httpClient
            ->setUri($url)
            ->setConfig(array('timeout' => Mage::getStoreConfig('currency/pm_privatbankrates/timeout')))
            ->request('GET')
            ->getBody();

        // response is in json format
        if (!$response) {
            $this->_messages[] = Mage::helper('pm_privatbankrates')->__('Cannot retrieve rate from %s.', $url);
        } else {
            // check response content - returns array
            $response = Zend_Json::decode($response);
            if (array_key_exists('error', $response)) {
                $this->_messages[] = Mage::helper('pm_privatbankrates')->__('API returned error %s: %s', $response['status'], $response['description']);
            } elseif (array_key_exists('base_ccy', $response[0])) {
                $rates = array();

                $rates['UAH'] = array(
                    'buy' => 1,
                    'sale' => 1
                );

                foreach ((array) $response as $currency) {
                    $rates[$currency['ccy']] = $currency;
                }

                $this->_rates = $rates;

            } else {
                Mage::log('Privatbankrates API request: %s', $url);
                Mage::log('Privatbankrates API response: ' . print_r($response, true));
                $this->_messages[] = Mage::helper('pm_privatbankrates')->__('Unknown response from API, check system.log for details.');
            }
        }
    }

    /**
     * Convert an individual rate
     * Note that the https://api.privatbank.ua/p24api/pubinfo?json&exchange&coursid=5 free service gives rates RUR/US/EUR rates
     * so we do a cross-currency conversion via USD as the base.
     *
     * @param string $currencyFrom
     * @param string $currencyTo
     * @param int $retry
     * @return float
     */
    protected function _convert($currencyFrom, $currencyTo, $retry = 0)
    {
        if (sizeof($this->_messages) > 0) {
            return null;
        }
        $rate = null;

        if (array_key_exists($currencyFrom, $this->_rates) && array_key_exists($currencyTo, $this->_rates)) {
            // convert via base currency, whatever it is.
            if ($currencyTo == 'UAH') {
                $rate = (float)$this->_rates[$currencyFrom]['sale'];
            } else {
                $rate = (float)$this->_rates[$currencyTo]['buy'] / (float)$this->_rates[$currencyFrom]['sale'];
            }

        } else {
            $this->_messages[] = Mage::helper('pm_privatbankrates')->__('Can\'t convert from ' . $currencyFrom . ' to ' . $currencyTo . '. Rate doesn\'t exist.');
        }

        return $rate;
    }

    /**
     * Trim currency rate to 6 decimals
     *
     * @param float $number
     * @return float
     */
    protected function _numberFormat($number)
    {
        return number_format($number, 6);
    }
}