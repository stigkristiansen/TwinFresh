<?php

declare(strict_types=1);

class Properties {
    const IPADDRESS = 'IPAddress';
    const UPDATEINTERVAL= 'UpdateInterval';
    const ID = 'ControlId';
    const PASSWORD = 'Password';
}

class Errors {
    const UNEXPECTED = 'An unexpected error occured. The error was : %s';
    const MISSINGIP = "The device %s is missing information about it's ip address";
    const NOTRESPONDING = 'The device %s is not responding (%s)';
}

class Timers {
    const UPDATE = 'Update';
}

class Profiles {
    const SPEED = 'TwinFresh.Speed';
    const SPEED_ICON = 'Intensity';
    const MODE = 'TwinFresh.Mode';
    const MODE_ICON = 'Ventilation';
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
}

class Buffers {
    const REPORT = 'report';
}

class Udp {
    const PORT = 4000;
}


