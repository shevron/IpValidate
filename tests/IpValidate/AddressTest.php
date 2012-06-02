<?php

// Load the test helper
require_once dirname(dirname(__FILE__)) . '/TestHelper.php';

// Load the PHPUnit TestCase class
require_once 'PHPUnit/Framework/TestCase.php';

// Load the class being tested
require_once 'IpValidate/Address.php';


/**
 * IpValidate_Address test case.
 */
class IpValidate_AddressTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test IpValidate_Addr::isValid with valid IP addresses
     * 
     * @param string $addr
     * @dataProvider validIpAddrProvider
     */
    public function testIsValidValidData($addr)
    {
        $this->assertTrue(IpValidate_Address::isValid($addr));
    }
    
    /**
     * Test IpValidate_Addr::isValid with invalid IP addresses 
     *
     * @param string $addr
     * @dataProvider invalidIpAddrProvider
     */
    public function testIsValidInvalidData($addr)
    {
        $this->assertFalse(IpValidate_Address::isValid($addr));
    }
    
	/**
	 * Make sure the constructor throws an exception when provided invalid 
	 * addreeses
	 * 
	 * @expectedException IpValidate_Exception
	 * @dataProvider      invalidIpAddrProvider
     */
    public function testConstructorExceptionOnInvalidAddr($addr)
    {
        new IpValidate_Address($addr);
    }
    
    /**
     * Test that normal constructor with valid addresses work properly
     *
     * @param        string $addr
     * @dataProvider validIpAddrProvider
     */
    public function testConstructorValidAddrs($addr)
    {
       $addrObj = new IpValidate_Address($addr);
       $addrInt = $this->getObjectAttribute($addrObj, '_addr');

       $this->assertEquals(ip2long($addr), $addrInt);
    }

    /**
     * Test that the integer value of an IPv4 address is as expected
     *
     * @param        string  $addr
     * @param        integer $expected
     * @dataProvider intValueProvider
     */
    public function testIntValue($addr, $expected)
    {
        $addrObj = new IpValidate_Address($addr);
        $this->assertEquals($expected, $addrObj->asInteger());
    }
    
    /**
     * Test conversion back to dotted-decimal string
     * 
     * @param        string $addr
     * @dataProvider validIpAddrProvider
     */
    public function testToString($addr)
    {
        $addrObj = new IpValidate_Address($addr);
        
        // Normalize addresses with leading zeros
        $parts = explode('.', $addr);
        foreach ($parts as &$p) {
            $p = (int) $p;
        }
        $normalizedAddr = implode('.', $parts);
        
        $this->assertEquals($normalizedAddr, (string) $addrObj);
    }

    /**
     * Data provider with a list of valid dotted-decimal IPv4 addresses
     *
     * @return array
     */
    public static function validIpAddrProvider()
    {
        return array(
            array('127.0.0.1'),
            array('12.34.56.78'),
            array('1.2.3.4'),
            array('001.002.003.004'),
            array('10.0.0.0'),
            array('192.168.0.255'),
            array('255.255.255.255'),
            array('0.0.0.0')
        );
    }
    
    /**
     * Data provider with a list of invalid dotted-decimal IPv4 addresses
     *
     * @return array
     */
    public static function invalidIpAddrProvider()
    {
        return array(
            array('x'),
            array(''),
            array(null),
            array(false),
            array(true),
            array(123),
            array('1.2.3'),
            array('www.example.com'),
            array('127.0.0.i'),
            array('192.168.0.1.2'),
            array('1.256.2.3'),
            array('100.200.300.400'),
            array('192.168.0.256')
        );
    }
    
    /**
     * Provides an array of valid dotted-decimal addresses along with their 
     * integer value
     *
     * @return array
     */
    public static function intValueProvider()
    {
        return array(
            array('255.255.255.255', 0xffffffff),
            array('000.000.000.000', 0),
            array('127.0.0.1',       0x7f000001),
            array('10.1.1.1',        0x0a010101),
            array('192.168.123.255', 0xc0a87bff)
        );
    }
}

