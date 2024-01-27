<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__ . '/../../core/php/core.inc.php';

class userFunction
{
    public static function estPresentDansLieu($presenceGPS, $emplacementLieuGPS)
    {
        $presence_latlong = cmd::byString($presenceGPS->latitudeLongitude)->execCmd();
        $presence_latlong_arr = explode(",", $presence_latlong);
        $presence_lat = $presence_latlong_arr[0];
        $presence_long = $presence_latlong_arr[1];   
        if (
            $presence_lat < $emplacementLieuGPS->latitudeHaut 
            && $presence_lat > $emplacementLieuGPS->latitudeBas  
            && $presence_long > $emplacementLieuGPS->longitudeGauche 
            && $presence_long < $emplacementLieuGPS->longitudeDroite
            ) {
            // Presence dans lieu
            return true;
        } else {
            // Absence dans lieu
            return false;
        }
    }

    public static function gestionPresence(
        $presenceDefaut,
        $presenceBLE, 
        $presenceWIFI, 
        $presenceGPS, 
        $emplacementLieuGPS
        ) {
        $cmdPresence = cmd::byString($presenceDefaut->cmdEtatPresence);
        $presence = $cmdPresence->execCmd();
        $log = "";
        if ($presence == 0) {
            // VERIFICATION BLE ?
            if ($presenceBLE != null) {
                $presenceBLEValue = cmd::byString($presenceBLE->cmdPresenceBLE)->execCmd();
                if ($presenceBLEValue == 1) {
                    $cmdPresence->event("1");
                    return "Presence BLE";
                }
            }
            // PRESENCE WIFI ?
            if ($presenceWIFI != null) {
                $presenceWIFIValue = cmd::byString($presenceWIFI->cmdPresenceWIFI)->execCmd();
                if ($presenceWIFIValue == 1) {
                    $cmdPresence->event("1");
                    return "Presence WIFI";
                }
            }
            // PRESENCE GPS ?
            if ($presenceGPS != null && $emplacementLieuGPS != null) {
                if (userFunction::estPresentDansLieu($presenceGPS, $emplacementLieuGPS)) {
                    $cmdPresence->event("1");
                    return "Presence GPS";
                }
            }
        } else {
            $compteur = 0;
            if ($presenceBLE != null) {
                $compteur = $compteur + 1;
                $presenceBLEValue = cmd::byString($presenceBLE->cmdPresenceBLE)->execCmd();
                if ($presenceBLEValue == 0) {
                    $log = $log . "- Pas présence BLE -";
                    $compteur = $compteur - 1;
                } else {
                    $log = $log . "- Présence BLE -";
                }
            }
            if ($presenceWIFI != null) {
                $compteur = $compteur + 1;
                $presenceWIFIValue = cmd::byString($presenceWIFI->cmdPresenceWIFI)->execCmd();
                if ($presenceWIFIValue == 0) {
                    $log = $log . "- Pas présence WIFI -";
                    $compteur = $compteur - 1;
                } else {
                    $log = $log . "- Présence WIFI -";
                }
            }
            if ($presenceGPS != null && $emplacementLieuGPS != null) {
                $compteur = $compteur + 1;
                if (userFunction::estPresentDansLieu($presenceGPS, $emplacementLieuGPS)) {
                    $log = $log . "- Pas présence GEOLOC -";
                    $compteur = $compteur - 1;
                } else {
                    $log = $log . "- Présence GEOLOC -";
                }
            }
            if ($compteur == 0) {
                $cmdPresence->event("0");
                $log = $log . "- Absence de présence";
                return $log;
            }
        }
        $log = $log . "- Aucune modification de présence";
        return $log;
    }
}

class PresenceDefaut
{
    public $cmdEtatPresence;
}
class PresenceGPS
{
    public $latitudeLongitude;
}

class EmplacementLieuGPS
{
    public $longitudeGauche;
    public $longitudeDroite;
    public $latitudeBas;
    public $latitudeHaut;
}

class PresenceBLE
{
    public $cmdPresenceBLE;
}

class PresenceWIFI
{
    public $cmdPresenceWIFI;
}