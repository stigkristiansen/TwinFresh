<?php

declare(strict_types=1);

class Protocol {
    protected const PREFIX = 0xfd;
    protected const TYPE = 0x02;
    protected const R = 0x01;
    protected const W = 0x02;
    protected const RW = 0x03;
    protected const INC = 0x04;
    protected const DEC = 0x05;
    protected const POWER = 0x01;
    protected const POWEROFF = 0x00;
    protected const POWERON = 0x01;
    protected const SPEED = 0x02;
    protected const SPEEDLOW = 0x01;
    protected const SPEEDMEDIUM = 0x02;
    protected const SPEEDHIGH = 0x03;
    protected const MODE = 0xb7;
    protected const MODEVENTILATION = 0x00;
    protected const MODERECOVERY = 0x01;
    protected const MODESUPPLY = 0x02;
    protected const HUMIDITY = 0x25;
    protected const BOOSTMODE = 0x06;
    protected const RESPONSE = 0x06;
    protected const FILTERCOUNTDOWN = 0x64;
    protected const FILTERREPLACEMENT = 0x88;
    protected const TOTALTIME = 0x7e;
    protected const DISCOVER = 0x7c;
    protected const MODEL = 0xb9;
    protected const SPECIALFE = 0xFE;
    protected const UNINITIZIALIZED = -1;
    protected const EMPTY = '';
    protected const DEFAULTDEVICE = 'DEFAULT_DEVICEID';

    private $power;
    private $speed;
    private $mode;
    private $humidity;
    private $filterCountdown;
    private $totalTime;
    private $boostMode;
    private $filterReplacement;
    private $controlId;
    private $model;

    public function __construct() {
        $this->power = self::UNINITIZIALIZED;
        $this->speed = self::UNINITIZIALIZED;
        $this->mode = self::UNINITIZIALIZED;
        $this->humidity = self::UNINITIZIALIZED;
        $this->boostMode = self::UNINITIZIALIZED;
        $this->filterReplacement = self::UNINITIZIALIZED;
        $this->filterCountdown = self::EMPTY;
        $this->totalTime = self::EMPTY;
        $this->controlId = self::EMPTY;
        $this->model = self::EMPTY;
    }

    public function GetMode() {
        return $this->mode;
    }

    public function GetSpeed(){
        return $this->speed;
    }

    public function GetPower() {
        return $this->power;
    }

    public function GetHumidity() {
        return $this->humidity;
    }

    public function GetFilterCountdown() {
        return $this->filterCountdown;
    }

    public function GetTotalTime() {
        return $this->totalTime;
    }

    public function GetFilterReplacement() {
        return $this->filterReplacement;
    }

    public function GetBoostMode() {
        return $this->boostMode;
    }

    public function GetControlId() {
        return $this->controlId;
    }

    public function GetModel() {
        return $this->model;
    }

    public function Decode(string $Data) {
        $prefix = substr($Data, 0, 2);
       
        if(strcmp($prefix, self::EncodeValue(self::PREFIX))!=0)
        throw new Exception('Invalid prefix!');
            //return false;
        
        $receivedChecksum = substr($Data, strlen($Data)-2);
        
        $data = substr($Data, 2, strlen($Data)-4);
        $calculatedChecksum = $this->Checksum($data);
        if(strcmp($receivedChecksum, $calculatedChecksum)!=0)
        throw new Exception('Invalid checksum');
            //return false;

        $parameters = str_split($data);

        if(strcmp($parameters[0], self::EncodeValue(self::TYPE))!=0)
            throw new Exception('Invalid type field!');
            //return false;

        $idSize = ord($parameters[1]);
        $passwordSize = ord($parameters[$idSize+2]);
        $startIndex = 3+$idSize+$passwordSize;

        if(strcmp($parameters[$startIndex], self::EncodeValue(self::RESPONSE))!=0)
            throw new Exception('Missing response code!');
            //return false;
      
        for($i=$startIndex+1;$i<sizeof($parameters);$i++) {
            //IPS_LogMessage('TwinFresh', 'Handling: '.ord($parameters[$i]));
            switch(ord($parameters[$i])) {
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
                case self::HUMIDITY:
                        $i++;
                        $this->humidity = ord($parameters[$i]);
                        break;
                case self::BOOSTMODE:
                    $i++;
                    $this->boostMode = ord($parameters[$i]);
                    break;
                case self::FILTERREPLACEMENT:
                    $i++;
                    $this->filterReplacement = ord($parameters[$i]);
                    break;
                case self::SPECIALFE:
                        $i++;
                        $size = ord($parameters[$i]); 
                        $i++;
                        switch(ord($parameters[$i])) {
                            case self::FILTERCOUNTDOWN:
                                $this->filterCountdown = (string )ord($parameters[$i+3]) . 'd ' . (string)ord($parameters[$i+2]). 'h ' . (string)ord($parameters[$i+1]).'m ';
                                break;
                            case self::TOTALTIME:
                                $this->totalTime = (string )(ord($parameters[$i+4])<<8 | ord($parameters[$i+3])) . 'd ' . (string)ord($parameters[$i+2]). 'h ' . (string)ord($parameters[$i+1]).'m ';
                                break;
                            case self::DISCOVER:
                                $this->controlId = substr($data,$i+1, $size);
                                break;
                            case self::MODEL:
                                $modelNumber = ord($parameters[$i+2])<<8 | ord($parameters[$i+1]);
                                switch($modelNumber) {
                                    case 3:
                                    case 5:
                                        $this->model = 'Vento Expert A30/A50/A85/A100';
                                        break;
                                    case 4:
                                        $this->model = 'Vento Expert Duo A30';
                                        break;
                                    default:
                                        $this->model = 'Unknown';
                                }
                                break;
                            default:
                                throw new Exception('Unknown parameter!');
                                //return false;
                        }
                        $i+=$size;
                        break;
                default:
                    throw new Exception('Unknown parameter!');
                    //return false;
           }
        }
        
        //return true;
    }

    public function CreateDiscoverMessage(){
        $command = self::EncodeValue(self::R).self::EncodeValue(self::DISCOVER);
        $message = $this->Encode($command, self::DEFAULTDEVICE, self::EMPTY); 

        return $message;
    }

    protected function EncodeValue(int $Value) {
        switch($Value) {
            case self::PREFIX:
                return chr($Value).chr($Value);
            default:
                return chr($Value);
        }
    }

    protected function Encode(string $Command, string $ControllerId, string $Password){
        $delta = self::EncodeValue(self::TYPE).$this->EncodeControllerId($ControllerId).$this->EncodePassword($Password).$Command;
        return self::EncodeValue(self::PREFIX).$delta.$this->Checksum($delta);
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

    private function EncodeControllerId(string $ControllerId) {
        $size = strlen($ControllerId);
        return chr($size).$ControllerId;
    }

    private function EncodePassword(string $Password){
        $size = strlen($Password);
        if($size>0)
            return chr($size).$Password;
        else
            return chr(0x00);
    }
}
