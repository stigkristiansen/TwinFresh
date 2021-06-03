<?php

declare(strict_types=1);

class Vent {
    use Protocol;
    
    private $controllerId;
    private $password;

    private $power;
    private $speed;
    private $mode;

    public function __construct(string $ControllerId, string $Password = '') {
        $this->controllerId = $ControllerId;
        $this->password = $Password;

        $this->power = -1;
        $this->speed = -1;
        $this->mode = -1;
    }

    public function RefreshStatus() {
        $command = self::EncodeValue(self::$R).self::EncodeValue(self::$POWER).self::EncodeValue(self::$SPEED).self::EncodeValue(self::$MODE);
        $encodedCommand = $this->Encode($command); 
        
        return $encodedCommand;
    }

    public function Power(bool $State) {
        if($State) 
            $value = self::EncodeValue(self::$POWERON);
        else
            $value = self::EncodeValue(self::$POWEROFF);

        $command = self::EncodeValue(self::$W).self::EncodeValue(self::$POWER).$value;
        $encodedCommand = $this->Encode($command); 
       
        return $encodedCommand;
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
        $encodedCommand = $this->Encode($command); 
       
        return $encodedCommand;
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
        $encodedCommand = $this->Encode($command); 
       
        return $encodedCommand;
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
    
}