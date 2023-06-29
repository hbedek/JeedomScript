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

    public static function plop($_arg1 = '')
    {
        return 'Argument 1 : ' . $_arg1;
    }

    public static function gestionVolet(
        $voletParametreDefaut,
        $voletParametrePresence,
        $voletParametreNuit,
        $voletParametreAzimuth
   ) {
        $nom_volet = $voletParametreDefaut->nomVolet;
        $cmdPositionVoletEtatStr = $voletParametreDefaut->cmdPositionVoletEtatStr;
        $cmdPositionVoletActionStr = $voletParametreDefaut->cmdPositionVoletActionStr;
        $cmd_position_volet_etat = cmd::byString($cmdPositionVoletEtatStr);
        $cmd_position_volet_action = cmd::byString($cmdPositionVoletActionStr);
        $etat_volet = $cmd_position_volet_etat->execCmd();

        if ($voletParametrePresence != null) {
            $cmd_presence = cmd::byString($voletParametrePresence->cmdPresenceStr);
            $presence = $cmd_presence->execCmd();
            $cmd_temperature_max_jour = cmd::byString($voletParametrePresence->cmdTempMaxJourStr);
            $temps_max = $cmd_temperature_max_jour->execCmd();
            if ($presence == 0 && $temps_max > $voletParametrePresence->tempMinPresence) {
                if ($etat_volet != $voletParametrePresence->positionVoletAbsence) {
                    $options = array('slider' => $voletParametrePresence->positionVoletAbsence);
                    $cmd_position_volet_action->execCmd($options, $cache = 0);
                    return 'Application etat départ sur volet';
                }
                else {
                    return 'Etat départ déjà appliqué';
                }
            }
        }
        if ($voletParametreNuit != null) {
            $cmd_coucher_coleil = cmd::byString($voletParametreNuit->cmdCoucherSoletStr);
            $cmd_aube_civile = cmd::byString($voletParametreNuit->cmdAubeCivileStr);

            $aube = $cmd_aube_civile->execCmd();
            $coucher_soleil = $cmd_coucher_coleil->execCmd();
            $current_hour = date('H');
            $current_min = date('i');
            $current_time = $current_hour . $current_min;
            if ($aube > $current_time || $coucher_soleil < $current_time) {
                if ($etat_volet != $voletParametreNuit->positionVoletNuit) {
                    $options = array('slider' => $voletParametreNuit->positionVoletNuit);
                    $cmd_position_volet_action->execCmd($options, $cache = 0);
                    return 'Application etat nuit sur volet';
                }
                else {
                    return 'Etat nuit déjà appliqué';
                }
            }
        }
        if ($voletParametreAzimuth != null) {
            $cmd_azimuth_soleil = cmd::byString($voletParametreAzimuth->cmdAzimuthSoleilStr);
            $cmd_pourcentage_nuage = cmd::byString($voletParametreAzimuth->cmdCouvertureNuageuseStr);
            $cmd_elevation_soleil = cmd::byString($voletParametreAzimuth->cmdElevationSoleilStr);
            $cmd_temperature_max_jour = cmd::byString($voletParametreAzimuth->cmdTempMaxJourStr);
            $temps_max = $cmd_temperature_max_jour->execCmd();
            if ($temps_max < $voletParametreAzimuth->tempMinActivation) {
                if ($etat_volet != $voletParametreDefaut->positionVoletOuvert) {
                    $options = array('slider' => $voletParametreDefaut->positionVoletOuvert);
                    $cmd_position_volet_action->execCmd($options, $cache = 0);
                    return 'Température max inférieure au minimum application état ouvert';
                }
                else {
                    return 'Etat déjà appliqué température max inférieure au minimum';
                }
            }

            $azimuth_soleil = $cmd_azimuth_soleil->execCmd();
            $elevation_sol = $cmd_elevation_soleil->execCmd();
            $alpha = (pi() / 180) * $elevation_sol;
            $gamma = (pi() / 180) * ($voletParametreAzimuth->azimuthFen - $azimuth_soleil);
            $hauteur = ($voletParametreAzimuth->distanceOmbrage / cos($gamma)) * tan($alpha);

            if ($alpha <= 0 || abs($gamma) >= ((pi() / 180) * $voletParametreAzimuth->angleVision)) {
                if ($etat_volet != $voletParametreDefaut->positionVoletOuvert) {
                    $options = array('slider' => $voletParametreDefaut->positionVoletOuvert);
                    $cmd_position_volet_action->execCmd($options, $cache = 0);
                    return 'Soleil hors fenêtre application état ouvert';
                } else {
                    return 'Etat ouvert déjà appliqué soleil hors fenêtre';
                }
            }

            $nuage = $cmd_pourcentage_nuage->execCmd();
            if ($nuage >= $voletParametreAzimuth->pourcentageNuageMax) {
                if ($etat_volet != $voletParametreDefaut->positionVoletOuvert) {
                    $options = array('slider' => $voletParametreDefaut->positionVoletOuvert);
                    $cmd_position_volet_action->execCmd($options, $cache = 0);
                    return 'Couverture nuageuse supérieure application état ouvert';
                } else {
                    return 'Etat ouvert déjà appliqué couverture nuageuse';
                }
            }

            $percent = round(max(min(100 * ($hauteur / $voletParametreAzimuth->tailleFen), $voletParametreDefaut->positionVoletOuvert), round(100 * ($voletParametreAzimuth->hauteurEncombrement / $voletParametreAzimuth->tailleFen))));
            if ($etat_volet != $percent) {
                $options = array('slider' => $percent);
                $cmd_position_volet_action->execCmd($options, $cache = 0);
                return 'Application etat ensoleillement sur volet ' . $percent;
            }
        }
        else {
            if ($etat_volet != $voletParametreDefaut->positionVoletOuvert) {
                log::add('CustomVolet', 'info', $nom_volet . ' Ouverture du volet');
                $options = array('slider' => $voletParametreDefaut->positionVoletOuvert);
                $cmd_position_volet_action->execCmd($options, $cache = 0);
                return 'Ouverture du volet';
            }
        }
        return 'Aucune modification etat sur volet';
    }
}

class VoletParametresDefaut
{
    public $nomVolet;
    public $positionVoletFermee;
    public $positionVoletOuvert;
    public $cmdPositionVoletEtatStr;
    public $cmdPositionVoletActionStr;
}

class VoletParametresPresence
{
    public $positionVoletAbsence;
    public $cmdPresenceStr;
    public $cmdTempMaxJourStr;
    public $tempMinPresence;
}

class VoletParametresNuit
{
    public $cmdCoucherSoletStr;
    public $cmdAubeCivileStr;
    public $positionVoletNuit;
}

class VoletParametresAzimuth
{
    public $cmdAzimuthSoleilStr;
    public $cmdTempMaxJourStr;
    public $cmdCouvertureNuageuseStr;
    public $cmdElevationSoleilStr;
    public $azimuthFen;
    public $tailleFen;
    public $hauteurEncombrement;
    public $distanceOmbrage;
    public $pourcentageNuageMax;
    public $tempMinActivation;
    public $angleVision;
}