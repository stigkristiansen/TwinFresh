<?php

declare(strict_types=1);

require_once(__DIR__ . "/../libs/autoload.php");

class Ventilator extends IPSModule {
	public function Create()
	{
		//Never delete this line!
		parent::Create();

		$this->RequireParent('{82347F20-F541-41E1-AC5B-A636FD3AE2D8}');
	}

	public function Destroy()
	{
		//Never delete this line!
		parent::Destroy();
	}

	public function Refresh() {
		$vent = new Vent('0022004547415717', '');
		$data = $vent->RefreshStatus();
		$arr = str_split($data);
		foreach($arr as $char) {
			IPS_LogMessage('TwinFresh', ord($char));	
		}
		
		$this->Send($data,'192.168.0.107', 4000);
	}

	public function ApplyChanges()
	{
		//Never delete this line!
		parent::ApplyChanges();
	}

	public function Send(string $Text, string $ClientIP, int $ClientPort)
	{
		$Text = $data = iconv("ISO-8859-1", "UTF-8", $Text);
		$this->SendDataToParent(json_encode(['DataID' => '{C8792760-65CF-4C53-B5C7-A30FCC84FEFE}', "ClientIP" => $ClientIP, "ClientPort" => $ClientPort, "Buffer" => $Text]));
	}

	public function ReceiveData($JSONString)
	{
		IPS_LogMessage('TwinFresh','Received data');
		$data = json_decode($JSONString);
		$buffer = iconv("UTF-8","ISO-8859-1", $data->Buffer);
		$arr = str_split($buffer);
		foreach($arr as $char) {
			IPS_LogMessage('TwinFresh', ord($char));	
		}
		IPS_LogMessage('Device RECV', $data->Buffer . ' - ' . $data->ClientIP . ' - ' . $data->ClientPort);
	}
}