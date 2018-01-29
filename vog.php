<?php

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

require_once 'vog.civix.php';

/**
 * Implementation of hook_civicrm_custom
 * 
 * This is needed only if there is a computed (View Only) custom field in this set.
 */

function vog_civicrm_post( $op, $objectName, $objectId, &$objectRef ) {
  if ($objectName == "Participant") {
    #watchdog('callhooks',"callhooks_civicrm_post_${op}_${objectName}(\$objectId, &\$objectRef)<pre>\n\$objectId=$objectId\n\$objectRef=" . var_export($objectRef,1) . '</pre>');
    #watchdog('php', '<pre>--- START VOG CIVICRM POST ---</pre>', NULL, WATCHDOG_DEBUG);
    #if (function_exists("callhooks_civicrm_post_${op}_$objectName"))
    # { $tz = date_default_timezone_get();
    #  date_default_timezone_set('UTC');
    # call_user_func("callhooks_civicrm_post_${op}_$objectName", $objectId, $objectRef);
    # date_default_timezone_set($tz);
    #}
  }
}

function vog_civicrm_custom( $op, $groupID, $entityID, &$params ) {

  #watchdog('php', '<pre>--- START FUNCTION VOG ---</pre>', NULL, WATCHDOG_DEBUG);

  if ( $groupID != 140 && $op != 'create' && $op != 'edit' ) {				//	did we just create or edit a custom object?
    return;									        //	if not, get out of here
  }
  if ($groupID == 140 && $op == 'edit') {						//	group ID of the Curriculum custom field set
    watchdog('php', '<pre>op:'. print_r($op, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    watchdog('php', '<pre>id:'. print_r($groupID, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);

    watchdog('php', '<pre>--- START EXTENSION VOG ---</pre>', NULL, WATCHDOG_DEBUG);
	$tableName1	= "civicrm_value_part_leid_vog_140";			//	table name for the custom group (each set of custom fields has a corresponding table in the database)
	$tableName2	= "civicrm_value_intake_181";				//	table name for the custom group (each set of custom fields has a corresponding table in the database)
	

    $sql1 = "SELECT vp.datum_laatste_vog_603 AS voglaatste, vp.kenmerk_vog_602 AS vogkenmerk, vp.scan_foto_van_je_vog_604 AS vogscan, pt.contact_id AS contactid, pt.event_id AS eventid FROM $tableName1 AS vp INNER JOIN `civicrm_participant` AS pt ON vp.entity_id = pt.id WHERE vp.entity_id = '$entityID'";
    watchdog('php', '<pre>sql1:'. print_r($sql1, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    $dao1 = CRM_Core_DAO::executeQuery( $sql1 );
    #watchdog('php', '<pre>dao1:'. print_r($dao1, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);

    $sql2 = "SELECT lp.functie_568 AS kampfunctie, lp.voor_het_eerst_649 AS eerstekeer, vp.entity_id FROM $tableName1 AS vp INNER JOIN `civicrm_value_leid_part_125` AS lp ON vp.entity_id = lp.entity_id WHERE vp.entity_id = '$entityID'";
    watchdog('php', '<pre>sql2:'. print_r($sql2, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    $dao2 = CRM_Core_DAO::executeQuery( $sql2 );
    #watchdog('php', '<pre>dao2:'. print_r($dao2, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    
    while ($dao1->fetch()) {
      $contactid       = $dao1->contactid;
      $eventid         = $dao1->eventid;
      $laatstevog      = $dao1->voglaatste;
      $kenmerkvog      = $dao1->vogkenmerk;
      $scanvog         = $dao1->vogscan;
    }
    while ($dao2->fetch()) {
      $functiekamp     = $dao2->kampfunctie;
      $eerstekeer      = $dao2->eerstekeer;
    }

    $sql3 = "SELECT start_date AS eventstart FROM civicrm_event WHERE id = '$eventid'";
    watchdog('php', '<pre>sql3:'. print_r($sql3, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    $dao3 = CRM_Core_DAO::executeQuery( $sql3 );
    #watchdog('php', '<pre>dao3:'. print_r($dao3, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);

    $sql4 = "SELECT datum_meest_recente_vog_56 AS vogrecent, kenmerk_vog_68 AS kenmerkrecent FROM `civicrm_value_intake_181` WHERE entity_id = '$contactid'";
    watchdog('php', '<pre>sql4:'. print_r($sql4, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    $dao4 = CRM_Core_DAO::executeQuery( $sql4 );
    #watchdog('php', '<pre>dao1:'. print_r($dao4, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);

    while ($dao3->fetch()) {
      $eventstart	= $dao3->eventstart;
    }
    while ($dao4->fetch()) {
      $vogrecent	= $dao4->vogrecent;
      $kenmerkrecent	= $dao4->kenmerkrecent;
    }

    $date1		= date_create($eventstart);
    $date2		= date_create($vogrecent);
    $diff 		= date_diff($date1,$date2);
    $diffyears		= $diff->y;
    $diffmonths		= $diff->m;
    $diffmonthstotal	= $diffmonths + (12*$diffyears);

    if ($diffmonthstotal >  34)        	{ $vognodig = 'opnieuw'; }
    if ($diffmonthstotal <= 34) 	{ $vognodig = 'noggoed'; }
    if ($functiekamp == 'hoofdleiding')	{ $vognodig = 'elkjaar'; }
    if ($functiekamp == 'bestuurslid')	{ $vognodig = 'elkjaar'; }
    if ($vogrecent   == NULL)		{ $vognodig = 'eerstex'; }
    if (stristr($eerstekeer, 'eerstekeer') !== FALSE) { $vognodig = 'eerstex'; }

    watchdog('php', '<pre>date1:'. print_r($date1, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    watchdog('php', '<pre>date2:'. print_r($date2, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    watchdog('php', '<pre>diff:'. print_r($diff, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    watchdog('php', '<pre>eerstekeer:'. print_r($eerstekeer, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    watchdog('php', '<pre>diffyears:'. print_r($diffyears, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    watchdog('php', '<pre>diffmonths:'. print_r($diffmonths, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    watchdog('php', '<pre>diffmonthstotal:'. print_r($diffmonthstotal, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    watchdog('php', '<pre>vognodig:'. print_r($vognodig, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    watchdog('php', '<pre>functie:'. print_r($functiekamp, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    $sql5 	= "UPDATE $tableName1 SET vog_nodig_586 = '$vognodig' WHERE entity_id = '$entityID'";
    watchdog('php', '<pre>sql5:'. print_r($sql5, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    $dao5	= CRM_Core_DAO::executeQuery( $sql5 );

    #$sql6 	= "UPDATE $tableName2 SET datum_meest_recente_vog_56 = '$laatstevog', kenmerk_vog_68 = '$kenmerkvog', scan_foto_van_je_vog_364 = '$scanvog' WHERE entity_id = '$contactid' AND (datum_meest_recente_vog_56 <= '$laatstevog' OR datum_meest_recente_vog_56 IS NULL)";
    $sql6 	= "UPDATE $tableName2 SET datum_meest_recente_vog_56 = '$laatstevog', kenmerk_vog_68 = '$kenmerkvog' WHERE entity_id = '$contactid' AND (datum_meest_recente_vog_56 <= '$laatstevog' OR datum_meest_recente_vog_56 IS NULL)";
    watchdog('php', '<pre>sql6:'. print_r($sql6, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    #watchdog('php', '<pre>contactid:'. print_r($contactid, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    #watchdog('php', '<pre>kenmerkvog:'. print_r($kenmerkvog, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    $dao6	= CRM_Core_DAO::executeQuery( $sql6 );

    if ($vognodig == 'noggoed') {
        $sql7   = "UPDATE $tableName1 SET datum_laatste_vog_603 = '$vogrecent', kenmerk_vog_602 = '$kenmerkrecent' WHERE entity_id = '$entityID' AND datum_laatste_vog_603 iS NULL";
    	watchdog('php', '<pre>sql7:'. print_r($sql7, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    	$dao7	= CRM_Core_DAO::executeQuery( $sql7 );
    }


  watchdog('php', '<pre>--- EINDE EXTENSION VOG ---</pre>', NULL, WATCHDOG_DEBUG);
  }
}

/**
 * Implementation of hook_civicrm_config
 */
function vog_civicrm_config(&$config) {
  _vog_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function vog_civicrm_xmlMenu(&$files) {
  _vog_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function vog_civicrm_install() {
  #CRM_Utils_File::sourceSQLFile(CIVICRM_DSN, __DIR__ . '/sql/auto_install.sql');
  return _vog_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function vog_civicrm_uninstall() {
  #CRM_Utils_File::sourceSQLFile(CIVICRM_DSN, __DIR__ . '/sql/auto_uninstall.sql');
  return _vog_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function vog_civicrm_enable() {
  return _vog_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function vog_civicrm_disable() {
  return _vog_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function vog_civicrm_managed(&$entities) {
  return _vog_civix_civicrm_managed($entities);
}

?>
