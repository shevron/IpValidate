<?php

// Load the test helper
require_once dirname(dirname(__FILE__)) . '/TestHelper.php';

// Load the PHPUnit TestCase class
require_once 'PHPUnit/Framework/TestCase.php';

// Load the class being tested
require_once 'IpValidate/Subnet.php';


/**
 * IpValidate_Subnet test case.
 */
class IpValidate_SubnetTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test isValid() with a set of should-be valid subnets
     * 
     * @dataProvider validSubnetProvider
     */
    public function testIsValidValidSubnets($test)
    {
        $this->assertTrue(IpValidate_Subnet::isValid($test));
    }
    
    /**
     * Test isValid() with a set of should-be invalid subnets
     * 
     * @dataProvider invalidSubnetProvider
     */
    public function testIsValidInvalidSubnets($test)
    {
        $this->assertFalse(IpValidate_Subnet::isValid($test));
    }
    
    /**
     * Test that the constructor properly sets the properties of the object
     * 
     * @dataProvider subnetBaseaddrMaskProvider
     * @param        string $test
     * @param        string $exbase
     * @param        string $exmask
     */
    public function testConstructorHasProperties($test, $exbase, $exmask)
    {
        $subnet = new IpValidate_Subnet($test);
        
        $this->assertEquals(ip2long($exbase), 
            $this->getObjectAttribute($subnet, '_netaddr'));
        $this->assertEquals(ip2long($exmask), 
            $this->getObjectAttribute($subnet, '_netmask'));
    }

    /**
     * Test that the constructor throws an exception on invalid subnets
     * 
     * @expectedException IpValidate_Exception
     * @dataProvider      invalidSubnetProvider
     * @param             string $test
     */
    public function testConstructorExceptionOnInvalidSubnet($test)
    {
        new IpValidate_Subnet($test);
    }
    
    /**
     * Test that __toString returns a normalized string representation 
     * of the subnet object
     * 
     * @dataProvider subnetBaseaddrMaskProvider
     * @param        string $test
     * @param        string $exbase
     * @param        string $exmask 
     */
    public function testToString($test, $exbase, $exmask)
    {
        $subnet = new IpValidate_Subnet($test);
        $ex = "$exbase/$exmask";
        
        $this->assertEquals($ex, (string) $subnet);
    }

    /**
     * Test that we get the expected broadcast address from a subnet
     * 
     * @dataProvider subnetBroadaddrProvider
     * @param        string $test
     * @param        string $expected
     */
    public function testGetBroadcastAddress($test, $expected)
    {
        $subnet = new IpValidate_Subnet($test);
        $this->assertSame($expected, $subnet->getBroadcastAddress());
    }

    /**
     * Test that ::getNetworkMask returns the network mask of the subnet
     * 
     * @dataProvider subnetBaseaddrMaskProvider
     * @param        string $test
     * @param        string $exbase
     * @param        string $exmask 
     */
    public function testGetNetworkMask($test, $exbase, $exmask)
    {
        $subnet = new IpValidate_Subnet($test);
        $this->assertEquals($exmask, $subnet->getNetworkMask());
    }

    /**
     * Test that ::getNetworkAddress returns the network address of the subnet
     * 
     * @dataProvider subnetBaseaddrMaskProvider
     * @param        string $test
     * @param        string $exbase
     * @param        string $exmask
     */
    public function testGetNetworkAddress($test, $exbase, $exmask)
    {
        $subnet = new IpValidate_Subnet($test);
        $this->assertEquals($exbase, $subnet->getNetworkAddress());
    }

    /**
     * Test that a isInRange() works as expected
     * 
     * @dataProvider addressInSubnetProvider
     * @param        string  $range
     * @param        string  $addr
     * @param        boolean $expected
     */
    public function testIsInRange($range, $addr, $expected)
    {
        $subnet = new IpValidate_Subnet($range);
        $got = $subnet->isInRange($addr);
        $this->assertEquals($expected, $got);
    }

    /***********************************************
     * Data Providers
     **********************************************/
    
    /**
     * Data provider of valid subnet strings 
     *
     * @return array
     */
    public static function validSubnetProvider()
    {
        return array(
            array('10.0.0.0/8'),
            array('10.0.0.0/255.255.255.0'),
            array('10.0.*.*'),
            array('192.168.100.0/24'),
            array('192.168.0.0/255.255.255.252'),
            array('*.*.*.*'),
            array('1.2.3.4/32'),
            array('127.0.0.1')
        );
    }
    
    /**
     * Data provider of invalid subnet strings
     *
     * @return array
     */
    public static function invalidSubnetProvider()
    {
        return array(
            array(null),
            array(''),
            array(123.5),
            array('xtz'),
            array('example.com'),
            array('10.0.0.256/1'),
            array('10.0.0.0/255.255.1.0'),
            array('10.*.0.*'),
            array('192.168.100.0/33'),
            array('192.168.0.0/255.255.255.251'),
            array('*.*.*.0'),
            array('10.0.0.0/255.255.255.300'),
            array('127.x.*.*')
        );
    }
    
    /**
     * Data provider for subnet, expected base address and expected mask
     *
     * @return array
     */
    public static function subnetBaseaddrMaskProvider()
    {
        return array(
            array('10.0.0.0/8', '10.0.0.0', '255.0.0.0'),
            array('192.168.0.0/255.255.255.0', '192.168.0.0', '255.255.255.0'),
            array('192.168.123.*', '192.168.123.0', '255.255.255.0'),
            array('172.12.*.*', '172.12.0.0', '255.255.0.0'),
            array('10.0.0.138', '10.0.0.138', '255.255.255.255'),
            array('192.168.10.10/26', '192.168.10.0', '255.255.255.192')
        );
    }
    
    /**
     * Data provider for subnet and expected broadcast address
     *
     * @return array
     */
    public static function subnetBroadaddrProvider()
    {
        return array(
            array('10.0.0.0/8', '10.255.255.255'),
            array('192.168.0.0/255.255.255.0', '192.168.0.255'),
            array('192.168.123.*', '192.168.123.255'),
            array('172.12.*.*', '172.12.255.255'),
            array('10.0.0.138', '10.0.0.138'),
            array('192.168.10.10/26', '192.168.10.63'),
            array('192.168.1.0/255.255.255.252', '192.168.1.3')
        );
    }
    
    /**
     * Data provider for subnet, address and whether or not the address is 
     * in the subnet
     *
     * @return array
     */
    public static function addressInSubnetProvider()
    {
        return array(
            array('10.0.0.0/8', '10.0.1.2', true),
            array('10.0.0.0/8', '10.0.0.0', true),
            array('10.0.0.0/8', '11.0.0.0', false),
            array('10.0.0.0/8', '1.2.3.4',  false),
            
            array('192.168.0.0/24', '192.168.0.2',   true),
            array('192.168.0.0/24', '192.168.1.2',   false),
            array('192.168.0.0/24', '192.168.0.255', true),
            
            array('192.168.0.0/25', '192.168.0.255', false),
            array('192.168.0.0/25', '192.168.0.127', true),
            array('192.168.0.0/25', '192.168.0.128', false),
            
            array('192.168.123.*', '192.168.123.0',   true),
            array('192.168.123.*', '192.168.123.255', true),
            array('192.168.123.*', '192.168.122.1',   false),
            
            array('10.0.0.138', '10.0.0.138', true),
            array('10.0.0.138', '10.0.0.139', false),
            array('10.0.0.138', '11.1.1.138', false),
            
            array('192.168.10.10/26', '192.168.10.0',   true),
            array('192.168.10.10/26', '192.168.10.2',   true),
            array('192.168.10.10/26', '192.168.10.63',  true),
            array('192.168.10.10/26', '192.168.10.64',  false),
            array('192.168.10.10/26', '192.168.11.251', false),
            
            array('192.168.1.0/255.255.255.252', '192.168.1.0', true),
            array('192.168.1.0/255.255.255.252', '192.168.1.1', true),
            array('192.168.1.0/255.255.255.252', '192.168.1.2', true),
            array('192.168.1.0/255.255.255.252', '192.168.1.3', true),
            array('192.168.1.0/255.255.255.252', '192.168.1.4', false),
            array('192.168.1.0/255.255.255.252', '192.168.1.5', false),
        );
    }    
}
