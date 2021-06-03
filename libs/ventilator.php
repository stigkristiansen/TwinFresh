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

        $command = self::EncodeValue(self::$RW).self::EncodeValue(self::$POWER).$value;
        $encodedCommand = $this->Encode($command); 
       
        return $encodedCommand;
    }

    public function Speed(int $Speed) {
        IPS_LogMessage('TwinFresh', 'Inside Vent::Speed()');

        switch($Speed) {
            case 1:
                $value = self::$SPEEDLOW;
                break;
            case 2:
                $value = self::$SPEEDMEDIUM;
                break;
            case 3:
                $value = self::$SPEEDHIGH;
                break;
            default:
                IPS_LogMessage('TwinFresh', 'Inside Vent::Speed(). Invalid value: '.$value);
                return false;
        }

        IPS_LogMessage('TwinFresh', 'Inside Vent::Speed(). Encoding: '.$value);

        $command = self::EncodeValue(self::$RW).self::EncodeValue(self::$SPEED).self::EncodeValue($value);

        IPS_LogMessage('TwinFresh', 'Inside Vent::Speed(). Sending...: ');
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

        $command = self::EncodeValue(self::$RW).self::EncodeValue(self::$MODE).self::EncodeValue($value);
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