<?php

class TideUtilities {
    public static $FEET_2_METERS = 0.30480061; // US feet to meters
	public static $COEFF_FOR_EPOCH = 0.017453292519943289;

    public static function startsWith (string $string, string $startString) : bool { 
        $len = strlen($startString); 
        return (substr($string, 0, $len) === $startString); 
    } 

    public static $COEFF_DEFINITION = array (
		"M2" => "Principal lunar semidiurnal constituent",
		"S2" => "Principal solar semidiurnal constituent",
		"N2" => "Larger lunar elliptic semidiurnal constituent",
		"K1" => "Lunar diurnal constituent",
		"M4" => "Shallow water overtides of principal lunar constituent",
		"O1" => "Lunar diurnal constituent",
		"M6" => "Shallow water overtides of principal lunar constituent",
		"MK3" => "Shallow water terdiurnal",
		"S4" => "Shallow water overtides of principal solar constituent",
		"MN4" => "Shallow water quarter diurnal constituent",
		"NU2" => "Larger lunar evectional constituent",
		"S6" => "Shallow water overtides of principal solar constituent",
		"MU2" => "Variational constituent",
		"2N2" => "Lunar elliptical semidiurnal second",
		"OO1" => "Lunar diurnal",
		"LAM2" => "Smaller lunar evectional constituent",
		"S1" => "Solar diurnal constituent",
		"M1" => "Smaller lunar elliptic diurnal constituent",
		"J1" => "Smaller lunar elliptic diurnal constituent",
		"MM" => "Lunar monthly constituent",
		"SSA" => "Solar semiannual constituent",
		"SA" => "Solar annual constituent",
		"MSF" => "Lunisolar synodic fortnightly constituent",
		"MF" => "Lunisolar fortnightly constituent",
		"RHO" => "Larger lunar evectional diurnal constituent",
		"Q1" => "Larger lunar elliptic diurnal constituent",
		"T2" => "Larger solar elliptic constituent",
		"R2" => "Smaller solar elliptic constituent",
		"2Q1" => "Larger elliptic diurnal",
		"P1" => "Solar diurnal constituent",
		"2SM2" => "Shallow water semidiurnal constituent",
		"M3" => "Lunar terdiurnal constituent",
		"L2" => "Smaller lunar elliptic semidiurnal constituent",
		"2MK3" => "Shallow water terdiurnal constituent",
		"K2" => "Lunisolar semidiurnal constituent",
		"M8" => "Shallow water eighth diurnal constituent",
		"MS4" => "Shallow water quarter diurnal constituent"
    );

	public static $ORDERED_COEFF = [ "M2", "S2", "N2", "K1", "M4", "O1", "M6", "MK3", "S4",
                                     "MN4", "NU2", "S6", "MU2", "2N2", "OO1", "LAM2", "S1", "M1",
                                     "J1", "MM", "SSA", "SA", "MSF", "MF", "RHO", "Q1", "T2",
                                     "R2", "2Q1", "P1", "2SM2", "M3", "L2", "2MK3", "K2", "M8",
                                     "MS4" ];

    public static function getWaterHeight(TideStation $ts, array $constSpeed, DateTime $when) : float {
        return 0;
    }

}