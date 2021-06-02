<?php

declare(strict_types=1);

trait Protocol {
    static $PREFIX = 0xfd;
    static $TYPE = 0x02;
    static $R = 0x01;
    static $W = 0x02;
    static $INC = 0x04;
    static $DEC = 0x05;
    static $POWER = 0x01;
    static $POWEROFF = 0x00;
    static $POWERON = 0x01;
    static $SPEED = 0x02;
    static $SPEDDLOW = 0x01;
    static $SPEEDMEDIUM = 0x02;
    static $SPEDDHIGH = 0x03;
    static $MODE = 0xb7;
    static $MODEVENTILATION = 0x00;
    static $MODERECOVERY = 0x01;
    static $MODESUPPLY = 0x02;
    static $RESPONSE = 0x06;

    public function Decode(string $Data) {
        $prefix = substr($Data, 0, 2);
       
        if(strcmp($prefix, self::EncodeValue(self::$PREFIX))!=0)
            return false;
        
        $receivedChecksum = substr($Data, strlen($Data)-2);
        
        $data = substr($Data, 2, strlen($Data)-4);
        $calculatedChecksum = $this->Checksum($data);
        if(strcmp($receivedChecksum, $calculatedChecksum)!=0)
            return false;

        $parameters = str_split($data);

        if(strcmp($parameters[0], self::EncodeValue(self::$TYPE))!=0)
            return false;

        $idSize = ord($parameters[1]);
        $passwordSize = ord($parameters[$idSize+2]);
        $startIndex = 3+$idSize+$passwordSize;
      
        for($i=$startIndex;$i<sizeof($parameters);$i++) {
            switch(ord($parameters[$i])) {
                case self::$RESPONSE:
                    break;
                case self::$POWER:
                    $i++;
                    $this->power = ord($parameters[$i]);
                    break;
                case self::$SPEED:
                    $i++;
                    $this->speed = ord($parameters[$i]);
                    break;
                case self::$MODE:
                    $i++;
                    $this->mode = ord($parameters[$i]);
                    break;
                default:
                    return false;
           }
        }

        //print_r('Power: '.$this->power.' Mode: '.$this->mode.' Speed: '.$this->speed);
        
        return true;
    }

    private function EncodeValue(int $Value) {
        switch($Value) {
            case self::$PREFIX:
                return chr($Value).chr($Value);
            default:
                return chr($Value);
        }
    }
}
