<?php

declare(strict_types=1);

class Protocol {
    const PREFIX = 0xfd;
    const TYPE = 0x02;
    const R = 0x01;
    const W = 0x02;
    const RW = 0x03;
    const INC = 0x04;
    const DEC = 0x05;
    const POWER = 0x01;
    const POWEROFF = 0x00;
    const POWERON = 0x01;
    const SPEED = 0x02;
    const SPEEDLOW = 0x01;
    const SPEEDMEDIUM = 0x02;
    const SPEEDHIGH = 0x03;
    const MODE = 0xb7;
    const MODEVENTILATION = 0x00;
    const MODERECOVERY = 0x01;
    const MODESUPPLY = 0x02;
    const RESPONSE = 0x06;
    const UNINITIZIALIZED = -1;

    public function Decode(string $Data) {
        $prefix = substr($Data, 0, 2);
       
        if(strcmp($prefix, self::EncodeValue(self::PREFIX))!=0)
            return false;
        
        $receivedChecksum = substr($Data, strlen($Data)-2);
        
        $data = substr($Data, 2, strlen($Data)-4);
        $calculatedChecksum = $this->Checksum($data);
        if(strcmp($receivedChecksum, $calculatedChecksum)!=0)
            return false;

        $parameters = str_split($data);

        if(strcmp($parameters[0], self::EncodeValue(self::TYPE))!=0)
            return false;

        $idSize = ord($parameters[1]);
        $passwordSize = ord($parameters[$idSize+2]);
        $startIndex = 3+$idSize+$passwordSize;
      
        for($i=$startIndex;$i<sizeof($parameters);$i++) {
            switch(ord($parameters[$i])) {
                case self::RESPONSE:
                    break;
                case self::POWER:
                    $i++;
                    $this->power = ord($parameters[$i]);
                    break;
                case self::SPEED:
                    $i++;
                    $this->speed = ord($parameters[$i]);
                    break;
                case self::MODE:
                    $i++;
                    $this->mode = ord($parameters[$i]);
                    break;
                default:
                    return false;
           }
        }
        
        return true;
    }

    protected function EncodeValue(int $Value) {
        switch($Value) {
            case self::PREFIX:
                return chr($Value).chr($Value);
            default:
                return chr($Value);
        }
    }

    protected function Checksum(string $Data) {
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

    protected function Encode($Data){
        $data = self::EncodeValue(self::TYPE).$this->EncodeControllerId().$this->EncodePassword().$Data;
        return self::EncodeValue(self::PREFIX).$data.$this->Checksum($data);
    }

    protected function EncodeControllerId() {
        $size = strlen($this->controllerId);
        return chr($size).$this->controllerId;
    }

    protected function EncodePassword(){
        $size = strlen($this->password);
        if($size>0)
            return chr($size).$this->password;
        else
            return chr(0x00);
    }
}
