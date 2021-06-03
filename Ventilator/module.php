<?php

declare(strict_types=1);

require_once(__DIR__ . "/../libs/autoload.php");

class Ventilator extends IPSModule {
	use ProfileHelper, BufferHelper;
	
	public function Create(){
		//Never delete this line!
		parent::Create();

		$this->RequireParent('{82347F20-F541-41E1-AC5B-A636FD3AE2D8}');

		$this->RegisterPropertyString(Properties::IPADDRESS, '');
		$this->RegisterPropertyString(Properties::ID, '');
		$this->RegisterPropertyString(Properties::PASSWORD, '');
		$this->RegisterPropertyInteger(Properties::UPDATEINTERVAL, 5);

		$this->RegisterProfileIntegerEx(Profiles::SPEED, Profiles::SPEED_ICON, '', '', [
			[Speed::LOW, Speed::LOW_TEXT, '', -1],
			[Speed::MEDIUM, Speed::MEDIUM_TEXT, '', -1],
			[Speed::HIGH, SPEED::HIGH_TEXT, '', -1]
		]);

		$this->RegisterProfileIntegerEx(Profiles::MODE, Profiles::MODE_ICON, '', '', [
			[Mode::VENTILATION, Mode::VENTILATION_TEXT, '', -1],
			[Mode::RECOVERY, Mode::RECOVERY_TEXT, '', -1],
			[Mode::SUPPLY, Mode::SUPPLY_TEXT, '', -1]
		]);

		$this->RegisterVariableBoolean(Variables::POWER_IDENT, Variables::POWER_TEXT, '~Switch', 1);
		$this->EnableAction(Variables::POWER_IDENT);

		$this->RegisterVariableInteger(Variables::SPEED_IDENT, Variables::SPEED_TEXT, Profiles::SPEED, 2);
		$this->SetValue(Variables::SPEED_IDENT, 1);
		$this->EnableAction(Variables::SPEED_IDENT);

		$mode = $this->RegisterVariableInteger(Variables::MODE_IDENT, Variables::MODE_TEXT, Profiles::MODE, 3);
		$this->EnableAction(Variables::MODE_IDENT);
		$this->RegisterTimer(Timers::UPDATE . (string) $this->InstanceID, 0, 'if(IPS_VariableExists(' . (string) $mode . ')) RequestAction(' . (string) $mode . ', 255);'); 

		$this->RegisterMessage(0, IPS_KERNELMESSAGE);
		
	}

	public function Destroy(){
		$module = json_decode(file_get_contents(__DIR__ . '/module.json'));
		if(count(IPS_GetInstanceListByModuleID($module->id))==0) {
			$this->DeleteProfile(Profiles::POWER);
			$this->DeleteProfile(Profiles::SPEED);
			$this->DeleteProfile(Profiles::MODE);
		}

		//Never delete this line!
		parent::Destroy();
	}

	public function ApplyChanges()
	{
		//Never delete this line!
		parent::ApplyChanges();

		if (IPS_GetKernelRunlevel() == KR_READY) {
            $this->SetTimers();
        }
	}

	public function RequestAction($Ident, $Value) {
		$this->LogMessage("RequestAction: ".$Ident.":".$Value, KL_MESSAGE);

		try {
			switch ($Ident) {
				case Variables::POWER_IDENT:
					$this->Power($Value);
					break;
				case Variables::SPEED_IDENT:
					if($this->GetValue(Variables::POWER_IDENT)) {   // Don't care if the device is off
						$this->Speed($Value);
					}
					break;
				case Variables::MODE_IDENT:
					if($Value>200) { // Values above 200 is used inside scheduled scripts and Form Actions
						switch($Value) {
							case 255: // Call Update();
								$this->Refresh();
								break;
						}
					} else if($this->GetValue(Variables::POWER_IDENT)) {   // Don't care if the device is off
						$this->Mode($Value);
					}
					break;
			}
		} catch(Exception $e) {
			$this->LogMessage(sprintf(Errors::UNEXPECTED,  $e->getMessage()), KL_ERROR);
		}
	}

	public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
        parent::MessageSink($TimeStamp, $SenderID, $Message, $Data);

        if ($Message == IPS_KERNELMESSAGE && $Data[0] == KR_READY) 
            $this->SetTimers();
    }

	private function SetTimers() {
		$this->SetTimerInterval(Timers::UPDATE . (string) $this->InstanceID, $this->ReadPropertyInteger(Properties::UPDATEINTERVAL)*1000);
	}


	private function Power(bool $State) {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
						
		if($this->VerifyDeviceIp($ipAddress)){
			$controlId = $this->ReadPropertyString(Properties::ID);
			$password = $this->ReadPropertyString(Properties::PASSWORD);
			
			$vent = new Vent($controlId, $password);
			$data = $vent->Power($State);
			
			$this->Send($data, $ipAddress, Udp::PORT);
		}
	}

	private function Speed(int $Value) {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
						
		if($this->VerifyDeviceIp($ipAddress)){
			$controlId = $this->ReadPropertyString(Properties::ID);
			$password = $this->ReadPropertyString(Properties::PASSWORD);

			$vent = new Vent($controlId, $password);
			$data = $vent->Speed($Value);
		
			$this->Send($data, $ipAddress, Udp::PORT);
		}
	}


	private function Mode(int $Value) {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
						
		if($this->VerifyDeviceIp($ipAddress)){
			$controlId = $this->ReadPropertyString(Properties::ID);
			$password = $this->ReadPropertyString(Properties::PASSWORD);
			
			$vent = new Vent($controlId, $password);
			$data = $vent->Mode($Value);
		
			$this->Send($data, $ipAddress, Udp::PORT);
		}
	}

	private function Refresh() {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
						
		if($this->VerifyDeviceIp($ipAddress)){
			$controlId = $this->ReadPropertyString(Properties::ID);
			$password = $this->ReadPropertyString(Properties::PASSWORD);
			
			$vent = new Vent($controlId, $password);
			$data = $vent->RefreshStatus();
		
			$this->Send($data, $ipAddress, Udp::PORT);
		}
	}

	private function VerifyDeviceIp($IpAddress) {
		if(strlen($IpAddress)>0)
			if($this->PingTest($IpAddress)) {
				$report['IpAddressCheck'] = 0; // Reset count on success
			
				if($this->Lock(Buffers::REPORT)) {
					$this->SetBuffer(Buffers::REPORT, serialize($report));
					$this->Unlock(Buffers::REPORT);
				}
				
				$this->SetStatus(102);
				return true;
			} else
				$msg = sprintf(Errors::NOTRESPONDING, (string) $this->InstanceID, $IpAddress);
		else
			$msg = sprintf(Errors::MISSINGIP, (string) $this->InstanceID);	

		$this->SetStatus(104);
		
		if($this->Lock(Buffers::REPORT)) {
			$report = unserialize($this->GetBuffer(Buffers::REPORT));
			$this->Unlock(Buffers::REPORT);
		}
		
		$countReported = isset($report['IpAddressCheck'])?$report['IpAddressCheck']:0;
		if($countReported<10) {
			$countReported++;
			$report['IpAddressCheck'] = $countReported;
			
			if($this->Lock(Buffers::REPORT)) {
				$this->SetBuffer(Buffers::REPORT, serialize($report));
				$this->Unlock(Buffers::REPORT);
			}
			
			$this->LogMessage($msg, KL_ERROR);
		}
		
		return false;	
	}

	private function PingTest(string $IPAddress) {
		$wait = 500;
		for($count=0;$count<3;$count++) {
			if(Sys_Ping($IPAddress, $wait))
				return true;
			$wait*=2;
		}

		return false;
	}
	
	private function Send(string $Text, string $ClientIP, int $ClientPort){
		$this->SendDataToParent(json_encode(['DataID' => '{C8792760-65CF-4C53-B5C7-A30FCC84FEFE}', "ClientIP" => $ClientIP, "ClientPort" => $ClientPort, "Buffer" => iconv("ISO-8859-1", "UTF-8", $Text)]));
	}

	public function ReceiveData($JSONString){
		$data = json_decode($JSONString);
		$buffer = iconv("UTF-8","ISO-8859-1", $data->Buffer);

		$controlId = $this->ReadPropertyString(Properties::ID);
		$password = $this->ReadPropertyString(Properties::PASSWORD);
			
		$vent = new Vent($controlId, $password);
		$vent->Decode($buffer);

		$value = $vent->GetPower();
		if($value!=-1)
			$this->SetValueEx(Variables::POWER_IDENT, $value);
		
		$value = $vent->GetSpeed();
		if($value!=-1)
			$this->SetValueEx(Variables::SPEED_IDENT, $value);

		$value = $vent->GetMode();
		if($value!=-1)
			$this->SetValueEx(Variables::MODE_IDENT, $value);
	}

	private function SetValueEx(string $Ident, $Value) {
		$oldValue = $this->GetValue($Ident);
		if($oldValue!=$Value)
			$this->SetValue($Ident, $Value);
	}
}