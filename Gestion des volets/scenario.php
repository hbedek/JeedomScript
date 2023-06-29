require_once dirname(__FILE__) . '/../../data/php/user.function.class.php';

$default = new VoletParametresDefaut();
$default->positionVoletFermee = 0;
$default->positionVoletOuvert =  99;
// Commande remontant l'état du positionnement du volet
$default->cmdPositionVoletEtatStr = '#[Cuisine][Volet][Etat]#';
// Commande permetant d'effectuer une action de type slider sur le positionnement du volet
$default->cmdPositionVoletActionStr = '#[Cuisine][Volet][Positionnement]#';


// ------ MODE PRESENCE ------ //
$presence = new VoletParametresPresence();
// Position à appliquer lors d'une absence
$presence->positionVoletAbsence = 30;
// Commande remontant la présence dans la maison. 1 si quelqu'un est présent 0 si la maison est vide
$presence->cmdPresenceStr = '#[Maison][Presence][Presence globale]#';
// Commande plugin Météo france remontant la température maximale pour la journée
$presence->cmdTempMaxJourStr = '#[Maison][Météo][Météo du Jour - Aujourdhui - Température Maximum]#';
// Température minimum pour laquelle la gestion de la présence s'applique.
// En dessous de cette température, les volets ne se fermeront plus lors d'une absence
$presence->tempMinPresence = 10;


// ------ MODE NUIT ------ //
$nuit = new VoletParametresNuit();
// Commande plugin héliotrope lorsque l'heure actuelle sera supérieur au coucher du soleil, le volet passera en mode nuit
$nuit->cmdCoucherSoletStr = '#[Maison][Position soleil][Coucher du Soleil]#';
// Commande plugin héliotrope lorsque l'heure actuelle sera inférieure àl'aube civile, le volet passera en mode nuit
$nuit->cmdAubeCivileStr = '#[Maison][Position soleil][Aube Civile]#';
// Position du volet à appliquer lorsque l'on passe en mode nuit
$nuit->positionVoletNuit = 0;


// ------ MODE POSITION SOLEIL ------ //
$azimuth = new VoletParametresAzimuth();
// Commande plugin héliotrope remontant la position azimuth du soleil
$azimuth->cmdAzimuthSoleilStr = '#[Maison][Position soleil][Azimuth 360 du Soleil]#';
// Commande plugin Météo france remontant la température maximale pour la journée
$azimuth->cmdTempMaxJourStr = '#[Maison][Météo][Météo du Jour - Aujourdhui - Température Maximum]#';
// Commande plugin Météo france remontant le poucentage de couverture nuageuse
$azimuth->cmdCouvertureNuageuseStr = '#[Maison][Météo][Météo Actuellement - Couverture Nuageuse]#';
// Commande plugin héliotrope remontant l'altitude du soleil
$azimuth->cmdElevationSoleilStr = '#[Maison][Position soleil][Altitude du Soleil]#';
// Position azimuth de la fenêtre à récupérer sur https://osmcompass.com/
// L'azimuth doit être perpandiculaire à la fenêtre
// Le sens en degrée compte. Les dégrées sont dans le sens des aiguille d'une montre avec 0 au nord, 90 à l'est, 180 au sud, 270 à l'ouest
$azimuth->azimuthFen = 115;
// Taille en m de la fenêtre.
$azimuth->tailleFen = 2;
// hauteur en m d'un éventuel encombrement en face de la fenêtre qui ferait de l'ombre naturellement
// Ici j'ai une haie qui fait à peu prêt 1m
$azimuth->hauteurEncombrement = 1.5;
// La distance à partir de laquelle l'ombrage doit commencer au sol
// Dans mon cas je ne veux que très peu de soleil au sol, l'ombre doit donc commencer à 0,1m.
$azimuth->distanceOmbrage = 0.5;
// Le mode position du soleil ne s'applique que si la couverture nuageuse est inférieure à la valeur ci dessous
$azimuth->pourcentageNuageMax = 70;
// Le mode position du soleil ne s'applique que si la température maximale de la journée est supérieure à la valeur ci dessous
$azimuth->tempMinActivation = 26;
// Angle gauche et droite a partir du milieu de la fenêtre pour lesquels le soleil est visible
$azimuth->angleVision = 60;


$message = userFunction::gestionVolet($default, $presence, $nuit, $azimuth);
$scenario->setLog($message);
