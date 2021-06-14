<?php

declare(strict_types=1);

class Properties {
    const IPADDRESS = 'IPAddress';
    const UPDATEINTERVAL= 'UpdateInterval';
    const ID = 'ControlId';
    const PASSWORD = 'Password';
    const MODEL = 'Model';
    const DISCOVERYTIMEOUT = 'DiscoveryTimeout';
}

class Errors {
    const UNEXPECTED = 'An unexpected error occured. The error was : %s';
    const MISSINGIP = "The device %s is missing information about it's ip address";
    const NOTRESPONDING = 'The device %s is not responding (%s)';
    const SOCKETCLOSED = 'The I/O instance %s is not in an open state';
}

class Timers {
    const UPDATE = 'Update';
}

class Profiles {
    const SPEED = 'TwinFresh.Speed';
    const SPEED_ICON = 'Intensity';
    const MODE = 'TwinFresh.Mode';
    const MODE_ICON = 'Ventilation';
    const BOOST = 'TwinFresh.Boost';
    const BOOST_ICON = 'Rocket';
    const REPLACEFILTER = 'TwinFresh.Filter';
    const REPLACEFILTER_ICON = 'Warning';
    const TIMER = 'TwinFresh.Timer';
    const TIMER_ICON = 'Clock';
    const SWITCH = '~Switch';
    const HUMIDITY = '~Humidity';
}

class Boost {
    const ACTIVE = true;
    const ACTIVE_TEXT = 'On'; 
    const INACTIVE = false;
    const INACTIVE_TEXT = 'Off';
}

class ReplaceFilter {
    const REPLACE = true;
    const REPLACE_TEXT = 'Replace'; 
    const OK = false;
    const OK_TEXT = 'Ok';
}

class Speed {
    const LOW = 1;
    const LOW_TEXT = 'Low';
    const MEDIUM = 2;
    const MEDIUM_TEXT = 'Medium';
    const HIGH = 3;
    const HIGH_TEXT = 'High';
}

class Mode {
    const VENTILATION = 0;
    const VENTILATION_TEXT = 'Ventilation';
    const RECOVERY = 1;
    const RECOVERY_TEXT = 'Heat Recovery';
    const SUPPLY = 2;
    const SUPPLY_TEXT = 'Supply';
}

class Variables {
    const POWER_IDENT = 'Power';
    const POWER_TEXT = 'Power';
    const SPEED_IDENT = 'Speed';
    const SPEED_TEXT = 'Speed';
    const MODE_IDENT = 'Mode';
    const MODE_TEXT = 'Mode';
    const HUMIDITY_IDENT = 'Humidity';
    const HUMIDITY_TEXT = 'Humidity';
    const FILTER_IDENT = 'FilterCountdown';
    const FILTER_TEXT = 'Filter Countdown';
    const REPLACEFILTER_IDENT = 'ReplaceFilter';
    const REPLACEFILTER_TEXT = 'Replace Filter';
    const TOTALTIME_IDENT = 'TotalTime';
    const TOTALTIME_TEXT = 'Total Time';
    const BOOSTMODE_IDENT = 'BoostMode';
    const BOOSTMODE_TEXT = 'Boost Mode';
}

class Buffers {
    const REPORT = 'report';
}

class Udp {
    const PORT = 4000;
}

class Messages {
    const DISCOVER = 'Discovering TwinFresh devices...';
}

class Debug {
    const INSTANCESCOMPLETED = 'GetTFInstances(): Building list of instances completed';
    const NUMBERFOUND = 'GetTFInstances(): Added %d instance(s) of TwinFresh device(s) to the list';
    const GETTINGINSTANCES = 'GetTFInstances(): Getting list of all created TwinFresh devices (module id: %s)';
    const WAITING ='DiscoverTFDevices(): Waiting for data...';
    const RECEIVEDDATA = 'DiscoverTFDevices(): Received data...';
    const INVALIDDATA = 'DiscoverTFDevices(): Invalid or incomplete data';
    const FOUNDDEVICE = 'DiscoverTFDevices(): "%s" reponded to the query. Adding it to the list';
    const ENDEDDISCOVERY = 'DiscoverTFDevices(): Ended discovery of TwinFresh devices';
    const STARTINGDISCOVERY = 'DiscoverTFDevices(): Starting discovery of TwinFresh devices';
    const CREATESOCKETFAILED = 'DiscoverTFDevices(): Creating socket failed!';
    const SENDSOCKETFAILED = 'DiscoverTFDevices(): Sending socket failed!';
    const FORMCOMPLETED = 'GetConfigurationForm(): The Configuration form build is complete';
    const ADDINGINSTANCE = 'GetConfigurationForm(): Added existing instance "%s" with InstanceId %d';
    const ADDINGEXISTINGINSTANCE = 'GetConfigurationForm(): Adding existing instances that are not discovered';
    const ADDINSTANCETODEVICE = 'GetConfigurationForm(): The discovered device "%s" exists as an instance. Setting InstanceId to %d';
    const ADDEDDISCOVEREDDEVICE = 'GetConfigurationForm(): Added discovered device "%s"';
    const NODEVICEDISCOVERED = 'GetConfigurationForm(): No discovered devices to add';
    const ADDINGDISCOVEREDDEVICE = 'GetConfigurationForm(): Adding discovered devices';
    const BUILDINGFORM = 'GetConfigurationForm(): Building Configuration form';
    const DECODEFAILED = 'ReceiveData(): Failed to decode the incoming message. The error was "%s"';
    const RECEIVEDDATAFROMPARENT = 'ReceiveData(): Received data. The data is "%s"';
    const SENDTTOPARENTFAILED = 'Send(): Failed to send message to parrent instance. The error was "%s"';
    const REQUESTACTIONFAILED = 'RequestAction(): Unexpected error. The error was: "%s"';
    const VERIFYINGIOINSTANCE = 'VerifyDevice(): Verifying I/O instance %s...';
    const INSTANCEVERIFIED = 'VerifyDevice(): The instance %s is OK';
    const POWER = 'RequestAction(): Handling Power...';
    const SPEED = 'RequestAction(): Handling Speed...';
    const MODE = 'RequestAction(): Handling Mode...';
}

class Modules {
    const TWINFRESH = '{E2CD88D8-5C7E-684D-0D20-27D5DC857197}';
}