<?php

class IpValidate_Address
{
    /**
     * The IPv4 address as an integer
     *
     * @var integere
     */
    protected $_addr;
    
    public function __construct($addr)
    {
        if (! self::isValid($addr)) {
            require_once 'IpValidate/Exception.php';
            throw new IpValidate_Exception("'$addr' is not a valid IPv4 address");
        }
        
        $this->_addr = ip2long($addr);
    }
    
    /**
     * Return the integer representation of the IPv4 address
     *
     * @return integer
     */
    public function asInteger()
    {
        return (double) sprintf("%u", $this->_addr);    
    }
    
    public function __toString()
    {
        return long2ip($this->_addr);
    }
    
    /**
     * Return TRUE if $addr is a valid dotted-decimal notation IPv4 address
     *
     * @param  string $addr
     * @return boolean
     */
    static public function isValid($addr)
    {
        if (! is_string($addr)) return false; 
        
        $parts = explode('.', $addr);
        if (count($parts) != 4) return false;

        foreach($parts as $part) {
            if (! ctype_digit($part)) return false;
            if ($part < 0 || $part > 255) return false;
        }
        
        return true;
    }
}
