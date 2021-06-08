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
    const BOOST = 'TwinFresh.Boost';
    const BOOST_ICON = 'Rocket';
    const REPLACEFILTER = 'TwinFresh.Filter';
    const REPLACEFILTER_ICON = 'Warning';
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
    const FILTERREPLACE_IDENT = 'ReplaceFilter';
    const FILTERREPLACE_TEXT = 'Replace filter';
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


