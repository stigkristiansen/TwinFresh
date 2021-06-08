<?php

declare(strict_types=1);

class Vent extends Protocol {
    private $controllerId;
    private $password;


    public function __construct(string $ControllerId='', string $Password = '') {
        parent::__construct();

        $this->controllerId = $ControllerId;
        $this->password = $Password;
    }

    public function RefreshStatus() {
        $command = self::EncodeValue(self::R).self::EncodeValue(self::POWER).self::EncodeValue(self::SPEED).self::EncodeValue(self::MODE).self::EncodeValue(self::HUMIDITY).self::EncodeValue(self::FILTERCOUNTDOWN).self::EncodeValue(self::TOTALTIME);
        $message = $this->Encode($command, $this->controllerId, $this->password); 

        $arr = str_split($message);

        for ($i=0;$i<sizeof($arr);$i++) {
            IPS_LogMessage('TwinFresh', ord($arr[$i]));
        }
        
        return $message;
    }

    public function Power(bool $State) {
        if($State) 
            $value = self::EncodeValue(self::POWERON);
        else
            $value = self::EncodeValue(self::POWEROFF);

        $command = self::EncodeValue(self::RW).self::EncodeValue(self::POWER).$value;
        $message = self::Encode($command, $this->controllerId, $this->password); 
       
        return $message;
    }

    public function Speed(int $Speed) {
        switch($Speed) {
            case 1:
                $value = self::SPEEDLOW;
                break;
            case 2:
                $value = self::SPEEDMEDIUM;
                break;
            case 3:
                $value = self::SPEEDHIGH;
                break;
            default:
                return false;
        }

        $command = self::EncodeValue(self::RW).self::EncodeValue(self::SPEED).self::EncodeValue($value);
        $message = self::Encode($command, $this->controllerId, $this->password); 
       
        return $message;
    }

    public function Mode(int $Mode) {
        switch($Mode) {
            case 0:
                $value = self::MODEVENTILATION;
                break;
            case 1:
                $value = self::MODERECOVERY;
                break;
            case 2:
                $value = self::MODESUPPLY;
                break;
            default:
                return false;
        }

        $command = self::EncodeValue(self::RW).self::EncodeValue(self::MODE).self::EncodeValue($value);
        $message = self::Encode($command, $this->controllerId, $this->password); 
       
        return $message;
    }


}