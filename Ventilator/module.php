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

		$this->RegisterProfileBooleanEx(Profiles::BOOST, Profiles::BOOST_ICON, '', '', [
			[Boost::ACTIVE, Boost::ACTIVE_TEXT, '', -1],
			[Boost::INACTIVE, Boost::INACTIVE_TEXT, '', -1]
		]);

		$this->RegisterProfileBooleanEx(Profiles::REPLACEFILTER, Profiles::REPLACEFILTER_ICON, '', '', [
			[ReplaceFilter::REPLACE, ReplaceFilter::REPLACE_TEXT, '', -1],
			[ReplaceFilter::OK, ReplaceFilter::OK_TEXT, '', -1]
		]);

		$this->RegisterProfileString(Profiles::TIMER, Profiles::TIMER_ICON, '', '');

		$this->RegisterVariableBoolean(Variables::POWER_IDENT, Variables::POWER_TEXT, Profiles::SWITCH, 1);
		$this->EnableAction(Variables::POWER_IDENT);

		$this->RegisterVariableInteger(Variables::SPEED_IDENT, Variables::SPEED_TEXT, Profiles::SPEED, 2);
		$this->SetValue(Variables::SPEED_IDENT, 1);
		$this->EnableAction(Variables::SPEED_IDENT);

		$mode = $this->RegisterVariableInteger(Variables::MODE_IDENT, Variables::MODE_TEXT, Profiles::MODE, 3);
		$this->EnableAction(Variables::MODE_IDENT);
		$this->RegisterTimer(Timers::UPDATE . (string) $this->InstanceID, 0, 'if(IPS_VariableExists(' . (string) $mode . ')) RequestAction(' . (string) $mode . ', 255);'); 

		$this->RegisterVariableInteger(Variables::HUMIDITY_IDENT, Variables::HUMIDITY_TEXT, Profiles::HUMIDITY, 4);

		$this->RegisterVariableString(Variables::FILTER_IDENT, Variables::FILTER_TEXT, Profiles::TIMER, 5);

		$this->RegisterVariableBoolean(Variables::REPLACEFILTER_IDENT, Variables::REPLACEFILTER_TEXT, Profiles::REPLACEFILTER, 6);

		$this->RegisterVariableString(Variables::TOTALTIME_IDENT, Variables::TOTALTIME_TEXT, Profiles::TIMER, 7);

		$this->RegisterVariableBoolean(Variables::BOOSTMODE_IDENT, Variables::BOOSTMODE_TEXT, Profiles::BOOST, 8);
		
		$this->RegisterMessage(0, IPS_KERNELMESSAGE);
		
	}

	public function Destroy(){
		$module = json_decode(file_get_contents(__DIR__ . '/module.json'));
		if(count(IPS_GetInstanceListByModuleID($module->id))==0) {
			$this->DeleteProfile(Profiles::SPEED);
			$this->DeleteProfile(Profiles::MODE);
			$this->DeleteProfile(Profiles::BOOST);
			$this->DeleteProfile(Profiles::REPLACEFILTER);
			$this->DeleteProfile(Profiles::TIMER);
		}

		//Never delete this line!
		parent::Destroy();
	}

	public function ApplyChanges()
	{
		//Never delete this line!
		parent::ApplyChanges();

		if (IPS_GetKernelRunlevel() == KR_READY) {
            $this->SetTimer();
        }
	}

	public function RequestAction($Ident, $Value) {
		try {
			switch ($Ident) {
				case Variables::POWER_IDENT:
					$this->Power($Value);
					break;
				case Variables::SPEED_IDENT:
					$this->Speed($Value);
					break;
				case Variables::MODE_IDENT:
					if($Value>200) { // Values above 200 are used inside scheduled scripts and Form Actions
						switch($Value) {
							case 255: // Call Refresh();
								$this->Refresh();
								break;
						}
					} else  { 
						$this->Mode($Value);
					}
					break;
			}
		} catch(Exception $e) {
			$this->LogMessage(sprintf(Errors::UNEXPECTED,  $e->getMessage()), KL_ERROR);
			$this->SendDebug(IPS_GetName($this->InstanceID), sprintf(Debug::REQUESTACTIONFAILED, $e->getMessage()), 0);
		}
	}

	public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
        parent::MessageSink($TimeStamp, $SenderID, $Message, $Data);

        if ($Message == IPS_KERNELMESSAGE && $Data[0] == KR_READY) 
            $this->SetTimer();
    }

	private function SetTimer() {
		$this->SetTimerInterval(Timers::UPDATE . (string) $this->InstanceID, $this->ReadPropertyInteger(Properties::UPDATEINTERVAL)*1000);
	}

	private function Power(bool $State) {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
						
		if($this->VerifyDeviceIp($ipAddress)){
			$controlId = $this->ReadPropertyString(Properties::ID);
			$password = $this->ReadPropertyString(Properties::PASSWORD);
			
			$vent = new Vent($controlId, $password);
			$message = $vent->Power($State);
			
			$this->Send($message, $ipAddress, Udp::PORT);
		}
	}

	private function Speed(int $Value) {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
						
		if($this->VerifyDeviceIp($ipAddress)){
			$controlId = $this->ReadPropertyString(Properties::ID);
			$password = $this->ReadPropertyString(Properties::PASSWORD);

			$vent = new Vent($controlId, $password);
			$message = $vent->Speed($Value);
		
			$this->Send($message, $ipAddress, Udp::PORT);
		}
	}

	private function Mode(int $Value) {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
						
		if($this->VerifyDeviceIp($ipAddress)){
			$controlId = $this->ReadPropertyString(Properties::ID);
			$password = $this->ReadPropertyString(Properties::PASSWORD);
			
			$vent = new Vent($controlId, $password);
			$message = $vent->Mode($Value);
		
			$this->Send($message, $ipAddress, Udp::PORT);
		}
	}

	private function Refresh() {
		$ipAddress = $this->ReadPropertyString(Properties::IPADDRESS);
						
		if($this->VerifyDeviceIp($ipAddress)){
			$controlId = $this->ReadPropertyString(Properties::ID);
			$password = $this->ReadPropertyString(Properties::PASSWORD);
			
			$vent = new Vent($controlId, $password);
			$message = $vent->RefreshStatus();
		
			$this->Send($message, $ipAddress, Udp::PORT);
		}
	}

	private function VerifyDeviceIp(string $IpAddress) {
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
		try {
			$this->SendDataToParent(json_encode(['DataID' => '{C8792760-65CF-4C53-B5C7-A30FCC84FEFE}', "ClientIP" => $ClientIP, "ClientPort" => $ClientPort, "Buffer" => iconv("ISO-8859-1", "UTF-8", $Text)]));
		} catch(Exception $e) {
			$this->LogMessage(sprintf(Errors::UNEXPECTED,  $e->getMessage()), KL_ERROR);
			$this->SendDebug(IPS_GetName($this->InstanceID), sprintf(Debug::SENDTTOPARENTFAILED,  $e->getMessage()), 0);
		}
		
	}

	public function ReceiveData($JSONString){
		$data = json_decode($JSONString);
		$buffer = iconv("UTF-8","ISO-8859-1", $data->Buffer);

		$this->SendDebug(IPS_GetName($this->InstanceID), sprintf(Debug::RECEIVEDDATAFROMPARENT,  $buffer), 0);

		$vent = new Vent();
		if($vent->Decode($buffer)==true) {

			$value = $vent->GetPower();
			if($value!=-1)
				$this->SetValueEx(Variables::POWER_IDENT, $value);
			
			$value = $vent->GetSpeed();
			if($value!=-1)
				$this->SetValueEx(Variables::SPEED_IDENT, $value);

			$value = $vent->GetMode();
			if($value!=-1)
				$this->SetValueEx(Variables::MODE_IDENT, $value);

			$value = $vent->GetHumidity();
			if($value!=-1)
				$this->SetValueEx(Variables::HUMIDITY_IDENT, $value);

			$value = $vent->GetFilterCountdown();
			if($value!='')
				$this->SetValueEx(Variables::FILTER_IDENT, $value);

			$value = $vent->GetFilterReplacement();
			if($value!=-1)
				$this->SetValueEx(Variables::REPLACEFILTER_IDENT, $value);

			$value = $vent->GetTotalTime();
			if($value!='')
				$this->SetValueEx(Variables::TOTALTIME_IDENT, $value);

			$value = $vent->GetBoostMode();
			if($value!=-1)
				$this->SetValueEx(Variables::BOOSTMODE_IDENT, $value);
		} else {
			$this->LogMessage(sprintf(Errors::UNEXPECTED, $e->getMessage()), KL_ERROR);
			$this->SendDebug(IPS_GetName($this->InstanceID), sprintf(Debug::DECODEFAILED, $e->getMessage()), 0);
		}
	}

	private function SetValueEx(string $Ident, $Value) {
		$oldValue = $this->GetValue($Ident);
		if($oldValue!=$Value)
			$this->SetValue($Ident, $Value);
	}
}