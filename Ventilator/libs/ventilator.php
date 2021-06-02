<?php

declare(strict_types=1);

require_once(__DIR__ . "/../libs/autoload.php");

class Vent {
    use Protocol;

    //const PORT = 4000;
    //private $ipAddress;
    private $controllerId;
    private $password;

    private $power;
    private $speed;
    private $mode;

    public function __construct(string $ControllerId, string $Password = '') {
        //$this->ipAddress = $IPAddress;
        $this->controllerId = $ControllerId;
        $this->password = $Password;

        $this->power = -1;
        $this->speed = -1;
        $this->mode = -1;
    }

    public function RefreshStatus() {
        IPS_LogMessage('TwinFresh', 'Inside RefreshStatus()');
        $command = self::EncodeValue(self::$R).self::EncodeValue(self::$POWER).self::EncodeValue(self::$SPEED).self::EncodeValue(self::$MODE);
        IPS_LogMessage('TwinFresh', 'Built command');

        $encodedCommand = $this->Encode($Command); 
        IPS_LogMessage('TwinFresh', 'Built complete command');
        return $encodedCommand;
        //$this->SendCommand($command);
    }

    public function Power(bool $State) {
        if($State) 
            $value = self::EncodeValue(self::$POWERON);
        else
            $value = self::EncodeValue(self::$POWEROFF);

        $command = self::EncodeValue(self::$W).self::EncodeValue(self::$POWER).$value;
        
        return $command:
        //$this->SendCommand($command);
    }

    public function Speed(int $Speed) {
        switch($Speed) {
            case 1:
                $value = self::$SPEEDLOW;
                break;
            case 2:
                $value = self::$SPEEDMEDIUM;
                break;
            case 2:
                $value = self::$SPEEDHIGH;
                break;
            default:
                return false;
        }

        $command = self::EncodeValue(self::$W).self::EncodeValue(self::$SPEED).self::EncodeValue(self::$$value);

        return $command;
        //$this->SendCommand($command);
        
    }

    public function Mode(int $Mode) {
        switch($Mode) {
            case 0:
                $value = self::$MODEVENTILATION;
                break;
            case 1:
                $value = self::$MODERECOVERY;
                break;
            case 2:
                $value = self::$MODESUPPLY;
                break;
            default:
                return false;
        }

        $command = self::EncodeValue(self::$W).self::EncodeValue(self::$MODE).self::EncodeValue($value);
        
        return $command;
        //$this->SendCommand($command);
    }

    public function GetMode() {
        retyrn $this->mode;
    }

    public function GetSpeed(){
        return $this->speed;
    }

    public function GetPower() {
        eturn $this->power;
    }

    private function SendCommand(string $Command) {
        $encodedData = $this->Encode($Command);      

        USCK_SendPacket(39441, $encodedData,$this->ipAddress, self::PORT);
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