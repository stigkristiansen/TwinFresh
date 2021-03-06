<?php
	declare(strict_types=1);

	require_once(__DIR__ . "/../libs/autoload.php");

	class TwinFreshDiscovery extends IPSModule {

		public function Create(){
			//Never delete this line!
			parent::Create();

			$this->RegisterPropertyInteger(Properties::DISCOVERYTIMEOUT, 1);
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

				$this->SendDebug(IPS_GetName($this->InstanceID), sprintf(Debug::ADDEDDISCOVEREDDEVICE, $id), 0);
				
				// Check if discovered device has an instance that is created earlier. If found, set InstanceID
				$instanceId = array_search($id, $tfInstances);
				if ($instanceId !== false) {
					$this->SendDebug(IPS_GetName($this->InstanceID), sprintf(Debug::ADDINSTANCETODEVICE, $id, $instanceId), 0);
					unset($tfInstances[$instanceId]); // Remove from list to avoid duplicates
					//$value[Properties::ID] = $id;
					$value['instanceID'] = $instanceId;
				} 
				
				$value['create'] = [
					'moduleID'      => Modules::TWINFRESH,
					'name'			=> 'Ventilator '. $id,
					'configuration' => [
						Properties::IPADDRESS => $device[Properties::IPADDRESS],
						Properties::ID 	 => $id
					]
				];
			
				$values[] = $value;
			}

			// Add devices that are not discovered, but created earlier
			if(count($tfInstances)>0)
				$this->SendDebug(IPS_GetName($this->InstanceID), Debug::ADDINGEXISTINGINSTANCE, 0);
			
			foreach ($tfInstances as $instanceId => $id) {
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
			$this->LogMessage(Messages::DISCOVER, KL_NOTIFY);

			$this->SendDebug(IPS_GetName($this->InstanceID), Debug::STARTINGDISCOVERY, 0);
			
			$devices = [];
			$buf = '';
			$ipAddress = '';
			$port = 0;

			$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
			if (!$socket) {
				$this->SendDebug(IPS_GetName($this->InstanceID), Debug::CREATESOCKETFAILED, 0);
				return [];
			}
			
			$proto = new Protocol();
			$message = $proto->CreateDiscoverMessage();

			$timeout = $this->ReadPropertyInteger(Properties::DISCOVERYTIMEOUT);
			
			socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
			socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, 1);
			socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $timeout, 'usec' => 0]);
			socket_bind($socket, '0.0.0.0', 0);
			
			if (@socket_sendto($socket, $message, strlen($message), 0, '255.255.255.255', Udp::PORT) === false) {
				$this->SendDebug(IPS_GetName($this->InstanceID), Debug::SENDSOCKETFAILED, 0);
				return [];
			}
												
			$i = 25;
			while ($i) {
				$this->SendDebug(IPS_GetName($this->InstanceID), Debug::WAITING, 0);
				$ret = @socket_recvfrom($socket, $buffer, 1024, 0, $ipAddress, $port);
				
				if ($ret === false) {
					$this->SendDebug(IPS_GetName($this->InstanceID), Debug::RECEIVESOCKETFAILED, 0);
					break;
				}

				if ($ret === 0) {
					$i--;
					continue;
				}
				
				$this->SendDebug(IPS_GetName($this->InstanceID), Debug::RECEIVEDDATA, 0);

				try {
					$proto = new Protocol();
					$proto->Decode($buffer);

					$controlId = $proto->GetControlId();
					$model = $proto->GetModel();
	
					if($model=='' || $controlId=='') {
						$this->SendDebug(IPS_GetName($this->InstanceID), Debug::INVALIDDATA, 0);
						$i--;
						continue;
					}
	
					$this->SendDebug(IPS_GetName($this->InstanceID), sprintf(Debug::FOUNDDEVICE, $controlId), 0);
	
					$devices[$controlId] = [
						Properties::IPADDRESS => $ipAddress,
						Properties::MODEL => $model
					];
				
				} catch (Exception $e) {
					$this->LogMessage(sprintf(Errors::UNEXPECTED, $e->getMessage()), KL_ERROR); 
					$this->SendDebug(IPS_GetName($this->InstanceID), Debug::INVALIDDATA, 0);
					$i--;
					continue;
				};
				
			}

			socket_close($socket);

			$this->SendDebug(IPS_GetName($this->InstanceID), Debug::ENDEDDISCOVERY, 0);
			
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