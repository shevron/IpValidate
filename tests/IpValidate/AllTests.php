<?php

// Load the IpValidate TestHelper
require_once dirname(dirname(__FILE__)) . '/TestHelper.php';

// Load PHPUnit TestSuite class
require_once 'PHPUnit/Framework/TestSuite.php';

// Load individual tests
require_once 'IpValidate/AddressTest.php';
require_once 'IpValidate/SubnetTest.php';

class IpValidate_AllTests extends PHPUnit_Framework_TestSuite
{
    /**
     * Constructs the test suite handler.
     */
    public function __construct()
    {
        $this->setName('IpValidate_AllTests');
        
        $this->addTestSuite('IpValidate_AddressTest');
        $this->addTestSuite('IpValidate_SubnetTest');
    }

    /**
     * Creates the suite.
     */
    public static function suite()
    {
        return new self();
    }
}
