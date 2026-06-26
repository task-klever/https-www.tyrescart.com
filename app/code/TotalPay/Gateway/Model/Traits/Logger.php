<?php


namespace TotalPay\Gateway\Model\Traits;

/**
 * Trait for defining common variables and methods for all Payment Solutions
 * Trait OnlinePaymentMethod
 * @package TotalPay\Gateway\Model\Traits
 */
trait Logger
{

  /**
   * Collected debug information
   *
   * @var array
   */
  protected $_debugData = [];

  /**
  * Init Logger
  *
  * @return \Zend\Log\Logger
  */
  protected function _initLogger()
  {
    $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/totalpay.log');
    $logger = new \Zend_Log();
    $logger->addWriter($writer);
    return $logger;
  }

  /**
   * Log debug data to file
   *
   * @return void
   */
  protected function _writeDebugData()
  {
    if ($this->getConfigHelper()->getDebug()) {
      $this->getLogger()->debug(var_export($this->_debugData, true));
    }
  }

  /**
   * @param string $key
   * @param array|string $value
   * @return $this
   */
  protected function _addDebugData($key, $value)
  {
      $this->_debugData[$key] = $value;
      return $this;
  }
}
