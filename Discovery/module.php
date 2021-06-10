<?php
	declare(strict_types=1);

	require_once(__DIR__ . "/../libs/autoload.php");

	class TwinFreshDiscovery extends IPSModule {

		public function Create(){
			//Never delete this line!
			parent::Create();

			$this->RegisterPropertyInteger(Properties::DISCOVERYTIMEOUT, 500);
		}

		public function Destroy(){
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges(){
			//Never delete this line!
			parent::ApplyChanges();
		}

		public function GetConfigurationForm() {
			$tfDevices = $this->DiscoverTFDevices();
			$tfInstances = $this->GetTFInstances();
	
			$values = [];

			$this->SendDebug(IPS_GetName($this->InstanceID), Debug::BUILDINGFORM, 0);
	
			// Add devices that are discovered
			if(count($tfDevices)>0)
				$this->SendDebug(IPS_GetName($this->InstanceID), Debug::ADDINGDISCOVEREDDEVICE, 0);
			else
				$this->SendDebug(IPS_GetName($this->InstanceID), Debug::NODEVICEDISCOVERED, 0);

			foreach ($tfDevices as $id => $device) {
				$value = [
					Properties::ID	=> $id,
					Properties::MODEL => $device[Properties::MODEL],
					'instanceID' 			=> 0
				];

				$this->SendDebug(IPS_GetName($this->InstanceID), sprintf(Debug::ADDEDDISCOVEREDDEVICE, $device[Properties::ID]), 0);
				
				// Check if discovered device has an instance that is created earlier. If found, set InstanceID
				$instanceId = array_search($id, $tfInstances);
				if ($instanceId !== false) {
					$this->SendDebug(IPS_GetName($this->InstanceID), sprintf(Debug::ADDINSTANCETODEVICE, $device[Properties::ID], $instanceId), 0);
					unset($tfInstances[$instanceId]); // Remove from list to avoid duplicates
					//$value[Properties::ID] = $id;
					$value['instanceID'] = $instanceId;
				} 
				
				$value['create'] = [
					'moduleID'      => Modules::TWINFRESH,
					'name'			=> 'Ventilator '. $device[Properties::ID],
					'configuration' => [
						Properties::IPADDRESS => $device[Properties::IPADDRESS],
						Properties::ID 	 => $id
					]
				];
			
				$values[] = $value;
			}

			// Add devices that are not discovered, but created earlier
			if(count($ccInstances)>0)
				$this->SendDebug(IPS_GetName($this->InstanceID), Debug::ADDINGEXISTINGINSTANCE, 0);
			
			foreach ($ccInstances as $instanceId => $id) {
				$values[] = [
					Properties::ID => $id, 
					'instanceID'   => $instanceId
				];

				$this->SendDebug(IPS_GetName($this->InstanceID), sprintf(Debug::ADDINGINSTANCE, IPS_GetName($instanceId), $instanceId), 0);
			}

			$form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
			$form['actions'][0]['values'] = $values;

			$this->SendDebug(IPS_GetName($this->InstanceID), Debug::FORMCOMPLETED, 0);
	
			return json_encode($form);
		}
	
		private function DiscoverTFDevices() : array {
			$this->LogMessage(Messages::DISCOVER, KL_MESSAGE);

			$this->SendDebug(IPS_GetName($this->InstanceID), Debug::STARTINGDISCOVERY, 0);
			
			$devices = [];
			$buf = '';
			$ipAddress = '';
			$port = 0;

			$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
			if (!$socket) {
				return [];
			}
			
			$message = chr(0xfd).chr(0xfd).chr(0x02).chr(0x10).'DEFAULT_DEVICEID'.chr(0x00).chr(0x01).chr(0x7c).chr(0x30).chr(0x05);
			
			socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, 1);
			socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
			socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 1, 'usec' => 100000]);
			socket_bind($socket, '0.0.0.0', 0);
			
			if (@socket_sendto($socket, $message, strlen($message), 0, '255.255.255.255', 4000) === false) {
				return [];
			}
			
			usleep(100000);
						
			$i = 50;
			while ($i) {
				$ret = @socket_recvfrom($socket, $buf, 2048, 0, $ipAddress, $port);
				if ($ret === false) {
					break;
				}
				if ($ret === 0 || $port!=4000) {
					$i--;
					continue;
				}
				
				$this->SendDebug(IPS_GetName($this->InstanceID), Debug::FOUNDDEVICES, 0);

				$vent = new Protocol();
				$vent->Decode($buf);
				$controlId = $vent->GetControlId();
				$model = $vent->GetModel();

				if($model=='' || $controlId=='') {
					$i--;
					continue;
				}

				$this->SendDebug(IPS_GetName($this->InstanceID), sprintf(Debug::FOUNDDEVICE, $controlId, 0);

				$devices[$controlId] = [
					Properties::IPADDRESS = $ipaddress;
					Properties::MODEL = $model;
				];
			}

			socket_close($socket);
			
			return $devices;
		}

		private function GetTFInstances () : array {
			$devices = [];

			$this->SendDebug(IPS_GetName($this->InstanceID), sprintf(Debug::GETTINGINSTANCES, Modules::TWINFRESH), 0);

			$instanceIds = IPS_GetInstanceListByModuleID(Modules::TWINFRESH);
        	
        	foreach ($instanceIds as $instanceId) {
				$devices[$instanceId] = IPS_GetProperty($instanceId, Properties::ID);
			}

			$this->SendDebug(IPS_GetName($this->InstanceID), sprintf(Debug::NUMBERFOUND, count($devices)), 0);
			$this->SendDebug(IPS_GetName($this->InstanceID), Debug::INSTANCESCOMPLETED, 0);	

			return $devices;
		}

	}