<?php

/**
 * Enter description here...
 *
 */

require_once 'IpValidate/Address.php';

class IpValidate_Subnet
{
    /**
     * The network address as long integer (ip2long() output)
     *
     * @var int
     */
    protected $_netaddr;
    
    /**
     * The network mask ad long integer (ip2long() output)
     *
     * @var int
     */
    protected $_netmask;
    
    /**
     * Create a new subnet from a string
     *
     * @param string $addr
     */
    public function __construct($addr)
    {
        $subnet = self::_normalizeSubnetStr($addr);
        if ($subnet === false) {
            require_once 'IpValidate/Exception.php';
            throw new IpValidate_Exception("'$addr' is not a valid subnet mask");
        }
        
        list($addr, $mask) = explode('/', $subnet);
        $addr = ip2long($addr);
        $mask = ip2long($mask);
            
        $this->_netaddr = self::_getNetworkAddress($addr, $mask);
        $this->_netmask  = $mask;
    }
    
    public function isInRange($addr)
    {
        $addr = ip2long($addr);
        return ($addr & $this->_netmask) == $this->_netaddr;
    }
    
    /**
     * Get the network mask of the subnet 
     *
     * @return string
     */
    public function getNetworkMask()
    {
        return long2ip($this->_netmask);
    }
    
    /**
     * Get the network address of the subnet
     *
     * @return string
     */
    public function getNetworkAddress()
    {
        return long2ip($this->_netaddr);
    }
    
    /**
     * Get the broadcase address of the subnet
     * 
     * @return string
     */
    public function getBroadcastAddress()
    {
        $bcast = $this->_netaddr + ((~ $this->_netmask) & 0xffffffff);
        return long2ip($bcast);
    }
    
    /**
     * Stringify the object
     * 
     * Return a normalized w.x.y.z/a.b.c.d format representation of the subnet
     *
     * @return string
     */
    public function __toString()
    {
        return long2ip($this->_netaddr) . "/" . long2ip($this->_netmask);
    }
    
    /**
     * Normalize a subnet string to a standard dotted-decimal notation
     * 
     * Will take in subnet masks in different notations (wildcards, DDN with
     * bit count mask, DDN with DDN mask) and will retrun a normalized 
     * 0.0.0.0/0.0.0.0 formatted string.
     * 
     * If $subnet is not valid, will return false. 
     *
     * @param  string $subnet
     * @return string
     */
    static protected function _normalizeSubnetStr($subnet)
    {
        if (! is_string($subnet)) return false;
        
        if (strpos($subnet, '*') !== false) { // Wildcards notation
            // Check that we have four parts
            $parts = explode('.', $subnet);
            if (count($parts) != 4) return false;
            
            // Check that each part is either '*' or an int between 0 and 255
            $gotStar = false;
            $address = array();
            $mask    = array();
            foreach($parts as $part) {
                if ($part == '*') {
                    $address[] = '0';
                    $mask[]    = '0';
                    
                    $gotStar = true;
                    
                } elseif (ctype_digit($part) && $part >= 0 && $part <= 255) {
                    // Cant have a number after a star
                    if ($gotStar) return false;
                    
                    $address[] = (string) $part;
                    $mask[]    = '255';
                    
                } else {
                    return false;
                }
            }
            
            return implode(".", $address) . '/' . implode(".", $mask);
            
        } elseif (strpos($subnet, '/') !== false) { 
            list($address, $mask) = explode('/', $subnet);
            
            // Check that the address part is valid
            if (IpValidate_Address::isValid($address)) {
            
                // Check if the mask part is a valid address
                if (IpValidate_Address::isValid($mask)) {
                    
                    // Check that it is a valid mask (no 1s after 0s!)
                    $binMask = decbin(ip2long($mask));
                    if (preg_match('/^1*0*$/', $binMask)) {
                        return "$address/$mask";
                    }
                    
                } else {
                    
                    // Otherwise, the mask is # of bits between 0 and 32
                    if (ctype_digit($mask) && $mask >= 0 && $mask <= 32) {
                        // Convert the bitmask into dotted decimal notation
                        $mask = self::_getMaskFromBitcount((int) $mask);
                        return "$address/$mask";
                    }
                }
            }
            
        } else {
            // A valid address is in fact a valid subnet for our purposes
            if (IpValidate_Address::isValid($subnet)) {
                return "$subnet/255.255.255.255";
            } 
        }
        
        return false;
    }
    
    /**
     * Convert a bit-count mask to dotted-decimal notation
     *
     * @param  integer $bits
     * @return string
     */
    static protected function _getMaskFromBitcount($bits)
    {
        if ($bits > 32 || $bits < 0) {
            require_once 'IpValidate/Exception.php';
            throw new IpValidate_Exception("Bit count is expected to be between 0 and 32");
        }
        
        $bitmask = (~0 << (32 - $bits)) & 0xffffffff;
        return long2ip($bitmask);
    }
    
    /**
     * Get the network address (lowest address in the subnet) from a base 
     * address and a network mask
     *
     * This method operates on integers
     * 
     * @param  integer $address
     * @param  integer $netmask
     * @return integer
     */
    static protected function _getNetworkAddress($address, $netmask)
    {
        return ($address & $netmask);
    }
    
    /**
     * Check if a string is a valid subnet mask 
     * 
     * Valid formats are wildcard format (192.168.1.*), dotted-decimal with
     * bit count as mask (192.168.1.0/24), or dotted-decimal with a 
     * dotted-decimal mask (192.168.1.0/255.255.255.0).
     *
     * @param  string  $subnet
     * @return boolean
     */
    static public function isValid($subnet)
    {
        return (bool) self::_normalizeSubnetStr($subnet);
    }
}
