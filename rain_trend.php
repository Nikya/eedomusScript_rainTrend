<?php

/*******************************************************************************
* Un script pour la box domotique eedomus
* Pour connaitre les prévisions pluvieuses des prochaines 48h
********************************************************************************
* Version :
*	1.1
*
* Auteur :
*	Nikya
*	https://github.com/Nikya/
*
* Documentation complète et aide :
* 	https://github.com/Nikya/eedomusScript_rainTrend
*
* Param :
*	cityId : identifiant de la ville
*	slotCnt : Nombre de slot (de 3h) à interpréter
*
* Retour :
* 	XML : Résultat formaté au format XML
*
*******************************************************************************/

// Seulement utile en mode test
// require_once ("../eedomusScriptsEmulator.php");

////////////////////////////////////////////////////////////////////////////////
// Lecture du paramêtre du script
$cityId = getArg('cityId', true);
$slotCnt = getArg('slotCnt', false, 4);
$slotCnt = $slotCnt >= 1 ? $slotCnt : 1;

////////////////////////////////////////////////////////////////////////////////
// Appel de l'API Météo
$url = "http://www.meteo-france.mobi/ws/getDetail/france/$cityId.json";
$jsonStr = httpQuery($url);
$jData = sdk_json_decode($jsonStr);

////////////////////////////////////////////////////////////////////////////////
// Analyse du résultat
$cityName = $jData['result']['ville']['nom'];
$rainyTrend = false;
$previsions48h = $jData['result']['previsions48h'];
$inXml = '';
$i = 0;


if ($previsions48h==null) {
	echo "Id de ville inconnue : '$cityId' ";
	exit -1;
}

// Pour chaque slot
foreach ($previsions48h as $slot => $data){
	$description = $data['description'];
	$probaPluie = $data['probaPluie'];
	if($probaPluie>60)
		$rainyTrend = true;

	$inXml .= <<<IN_XML

		<slot>
			<slotname>$slot</slotname>
			<description>$description</description>
			<probapluie>$probaPluie</probapluie>
		</slot>
IN_XML;

	// Arret de la lecture si le nombre de slot voulue est lue
	$i++;
	if ($i>=$slotCnt)
		break;
}

////////////////////////////////////////////////////////////////////////////////
// Formatage et renvoie du resultat en XML
$content_type = 'text/xml';
sdk_header($content_type);

$rainyTrendStr = $rainyTrend ? '1' : '0';

echo <<<OUT_XML
<data>
	<cityId>$cityId</cityId>
	<slotCnt>$slotCnt</slotCnt>
	<cityName>$cityName</cityName>
	<rainyTrend>$rainyTrendStr</rainyTrend>
	<slots>
		$inXml
	</slots>
</data>
OUT_XML;
?>
