<?php

declare(strict_types=1);

trait Protocol {
    static $PREFIX = 0xfd;
    static $TYPE = 0x02;
    static $R = 0x01;
    static $W = 0x02;
    static $RW = 0x03;
    static $INC = 0x04;
    static $DEC = 0x05;
    static $POWER = 0x01;
    static $POWEROFF = 0x00;
    static $POWERON = 0x01;
    static $SPEED = 0x02;
    static $SPEEDLOW = 0x01;
    static $SPEEDMEDIUM = 0x02;
    static $SPEEDHIGH = 0x03;
    static $MODE = 0xb7;
    static $MODEVENTILATION = 0x00;
    static $MODERECOVERY = 0x01;
    static $MODESUPPLY = 0x02;
    static $RESPONSE = 0x06;
    static $UNINITIZIALIZED = -1;

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

    private function Checksum(string $Data) {
        $arr = str_split($Data);
        $sum = 0;
        
        foreach ($arr as $char) {
           $value = ord($char);
           $sum+=$value;    
        }
        
        $low = $sum & 0xff;
        $high = $sum >> 8;
       
        return chr($low).chr($high);
    }

    private function Encode($Data){
        $data = self::EncodeValue(self::$TYPE).$this->EncodeControllerId().$this->EncodePassword().$Data;
        return self::EncodeValue(self::$PREFIX).$data.$this->Checksum($data);
    }

    private function EncodeControllerId() {
        $size = strlen($this->controllerId);
        return chr($size).$this->controllerId;
    }

    private function EncodePassword(){
        $size = strlen($this->password);
        if($size>0)
            return chr($size).$this->password;
        else
            return chr(0x00);
    }
}
