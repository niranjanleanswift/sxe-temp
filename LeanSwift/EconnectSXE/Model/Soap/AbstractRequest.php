<?php
/**
 * *
 *  * LeanSwift Extension
 *  *
 *  * NOTICE OF LICENSE
 *  *
 *  * This source file is subject to the LeanSwift Connector Extension License
 *  * that is bundled with this package in the file LICENSE.txt located in the Connector Server.
 *  *
 *  * DISCLAIMER
 *  *
 *  * This extension is licensed and distributed by LeanSwift. Do not edit or add to this file
 *  * if you wish to upgrade Extension and Connector to newer versions in the future.
 *  * If you wish to customize Extension for your needs please contact LeanSwift for more
 *  * information. You may not reverse engineer, decompile,
 *  * or disassemble LeanSwift Connector Extension (All Versions), except and only to the extent that
 *  * such activity is expressly permitted by applicable law not withstanding this limitation.
 *  *
 *  * @copyright   Copyright (C) Leanswift Solutions, Inc - All Rights Reserved
 *  * Unauthorized copying of this file, via any medium is strictly prohibited.
 *  * Proprietary and confidential.
 *  * Terms and conditions http://leanswift.com/leanswift-eula/
 *  * @category Norsea
 *
 */

namespace LeanSwift\EconnectSXE\Model\Soap;

use LeanSwift\EconnectSXE\Api\Soap\RequestInterface;
use LeanSwift\EconnectSXE\Helper\Data;
use Monolog\Logger;

abstract class AbstractRequest
{
    protected $_helper;
    protected $_request;
    protected $_mappings;
    protected $_logger;
    protected $_loggerEnablePath;

    public function __construct
    (
        RequestInterface $request,
        Data $helper,
        $mappings = [],
        Logger $logger,
        $loggerEnablePath = ''
    )
    {
        $this->_request = $request;
        $this->_helper  = $helper;
        $this->_mappings = $mappings;
        $this->_logger = $logger;
        $this->_loggerEnablePath = $loggerEnablePath;
    }

    public function send() {
        $this->_request->setLogger($this->_logger);
        $this->_request->setLoggerEnablePath($this->_loggerEnablePath);
        $this->_request->setRequestBody($this->getPostValues(), $this->_mappings['namespace']);
        $this->_request->setAPI($this->_mappings['api']);
        $this->_request->sendRequest();
    }

    public function getResponse()
    {
        return $this->_request->getResponse();
    }

    abstract public function getPostValues();
}
