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

function vog_civicrm_custom( $op, $groupID, $entityID, &$params ) {

  #watchdog('php', '<pre>--- START FUNCTION VOG ---</pre>', NULL, WATCHDOG_DEBUG);

  if ($groupID != 140 && $groupID != 181 && $op != 'create' && $op != 'edit') {		//	did we just create or edit a custom object?
    return;									        								//	if not, get out of here
  }

  if ($groupID == 140 OR $groupID == 181) {
    watchdog('php', '<pre>--- START EXTENSION VOG ---</pre>', NULL, WATCHDOG_DEBUG);
    #watchdog('php', '<pre>op:'. print_r($op, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    watchdog('php', '<pre>GroupID:'. print_r($groupID, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    watchdog('php', '<pre>entityID:'. print_r($entityID, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);

	$tableName1	= "civicrm_value_part_leid_vog_140";		//	table name for the custom group (each set of custom fields has a corresponding table in the database)
	$tableName2	= "civicrm_value_intake_181";				//	table name for the custom group (each set of custom fields has a corresponding table in the database)
	$tableName3	= "civicrm_value_part_leid_190";			//	table name for the custom group (each set of custom fields has a corresponding table in the database)
	$event_id = 135;										//  event_id of leiding 2018
}

  if ($groupID == 181 && $op == 'edit') {					//	group ID of the Curriculum custom field set
  	$result = civicrm_api3('Participant', 'get', array(
  	  'debug' => 1,
      'sequential' => 1,
      'return' => array("id", "contact_id", "custom_567", "custom_568", "custom_649"),
      'status_id' => array("Registered", "Deelgenomen", "Pending from pay later", "Pending from incomplete transaction", "Partially paid", "Pending refund"),
      'event_id' => $event_id,
      'contact_id' => $entityID,
    ));
  }
  if ($groupID == 140 && $op == 'edit') {					//	group ID of the Curriculum custom field set
    $result = civicrm_api3('Participant', 'get', array(
      'debug' => 1,
      'sequential' => 1,
      'return' => array("id", "contact_id", "custom_567", "custom_568", "custom_649"),
      'status_id' => array("Registered", "Deelgenomen", "Pending from pay later", "Pending from incomplete transaction", "Partially paid", "Pending refund"),
      'event_id' => $event_id,
      'id' => $entityID,
    ));
  }

  if ($groupID == 140 OR $groupID == 181 && $op == 'edit') {//	group ID of the Curriculum custom field set

	#watchdog('php', '<pre>result:'. print_r($result, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
  	if (isset($result['values'][0]) && is_array($result['values'])) {
  	} else {
  		watchdog('php', '<pre>--- EINDE EXTENSION VOG - NO PARTICIPANT ---</pre>', NULL, WATCHDOG_DEBUG); // RETURN IF CONTACT IS NO PARTICIPANT (LEIDING DIT JAAR)
   		return;
  	}

   	$contact_id = $result['values'][0]['contact_id'];
   	$part_id = $result['values'][0]['id'];
   	$part_welkkamp = $result['values'][0]['custom_567'];
   	$part_functie = $result['values'][0]['custom_568'];
   	$part_eerstekeer = $result['values'][0]['custom_649'];
    watchdog('php', '<pre>contact_id:'. print_r($contact_id, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    watchdog('php', '<pre>part_id:'. print_r($part_id, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    watchdog('php', '<pre>welkkamp:'. print_r($part_welkkamp, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    watchdog('php', '<pre>functie:'. print_r($part_functie, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    watchdog('php', '<pre>eerstekeer:'. print_r($part_eerstekeer, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);

   	$result = civicrm_api3('Event', 'get', array(
   		'sequential' => 1,
   		'return' => array("custom_681", "custom_682", "start_date", "event_type_id"),
   		'id' => $event_id,
   	));
   	#$event_hoofdleiding1 = $result['values'][0]['custom_681'];
   	#$event_hoofdleiding2 = $result['values'][0]['custom_682'];
   	$event_type_id = $result['values'][0]['event_type_id'];
   	$event_start_date = $result['values'][0]['start_date'];
   	watchdog('php', '<pre>event_type_id:'. print_r($event_type_id, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
   	watchdog('php', '<pre>event_start_date:'. print_r($event_start_date, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);

    $sql4 = "SELECT datum_meest_recente_vog_56 AS vogrecent, kenmerk_vog_68 AS kenmerkrecent FROM $tableName2 WHERE entity_id = '$contact_id'";
    #watchdog('php', '<pre>sql4:'. print_r($sql4, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    $dao4 = CRM_Core_DAO::executeQuery( $sql4 );
    #watchdog('php', '<pre>dao1:'. print_r($dao4, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    while ($dao4->fetch()) {
      $vogrecent		= $dao4->vogrecent;
      $kenmerkrecent	= $dao4->kenmerkrecent;
      watchdog('php', '<pre>vogrecent:'. print_r($vogrecent, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    }
    $date1				= date_create($event_start_date);
    $date2				= date_create($vogrecent);
    $diff 				= date_diff($date1,$date2);
    $diffyears			= $diff->y;
    $diffmonths			= $diff->m;
    $diffmonthstotal	= $diffmonths + (12*$diffyears);
    watchdog('php', '<pre>diffmonthstotal:'. print_r($diffmonthstotal, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);

    if ($vogrecent && $diffmonthstotal >  34)   	{ $vognodig = 'opnieuw'; }
    if ($vogrecent && $diffmonthstotal <= 34) 		{ $vognodig = 'noggoed'; }
    #watchdog('php', '<pre>vognodig1:'. print_r($vognodig, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);

    #if (stristr($part_eerstekeer, 'eerstekeer') !== FALSE) { $vognodig = 'eerstex'; }
	//if ($part_eerstekeer) 							{ $vognodig = 'eerstex'; }
	if (in_array("eerstekeer", $part_eerstekeer)) 	{ $vognodig = 'eerstex'; }
    if (!$vogrecent OR $vogrecent == NULL)			{ $vognodig = 'eerstex'; }
    #watchdog('php', '<pre>vognodig2:'. print_r($vognodig, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);

    if ($part_functie == 'hoofdleiding')			{ $vognodig = 'elkjaar'; }
    if ($part_functie == 'bestuurslid')	 			{ $vognodig = 'elkjaar'; }

    #watchdog('php', '<pre>date1:'. print_r($date1, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    #watchdog('php', '<pre>date2:'. print_r($date2, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    #watchdog('php', '<pre>diff:'. print_r($diff, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    #watchdog('php', '<pre>diffyears:'. print_r($diffyears, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    #watchdog('php', '<pre>diffmonths:'. print_r($diffmonths, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    watchdog('php', '<pre>vognodig3:'. print_r($vognodig, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
  }

  if ($groupID == 140 OR $groupID == 181 && $op == 'edit') {					//	group ID of the Curriculum custom field set

    $sql1 = "SELECT vp.datum_laatste_vog_603 AS voglaatste, vp.kenmerk_vog_602 AS vogkenmerk, vp.scan_foto_van_je_vog_604 AS vogscan, pt.contact_id AS contactid, pt.event_id AS eventid FROM $tableName1 AS vp INNER JOIN `civicrm_participant` AS pt ON vp.entity_id = pt.id WHERE vp.entity_id = '$part_id'";
    #watchdog('php', '<pre>sql1:'. print_r($sql1, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    $dao1 = CRM_Core_DAO::executeQuery( $sql1 );
    #watchdog('php', '<pre>dao1:'. print_r($dao1, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);

    while ($dao1->fetch()) {
      $contactid       = $dao1->contactid;
      $eventid         = $dao1->eventid;
      $laatstevog      = $dao1->voglaatste;
      watchdog('php', '<pre>laatstevog:'. print_r($laatstevog, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
      $kenmerkvog      = $dao1->vogkenmerk;
      $scanvog         = $dao1->vogscan;
    }

    $sql5 	= "UPDATE $tableName1 SET vog_nodig_586 = '$vognodig' WHERE entity_id = '$part_id'";
    watchdog('php', '<pre>sql5:'. print_r($sql5, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    $dao5	= CRM_Core_DAO::executeQuery( $sql5 );

	if ($laatstevog) {
    	#$sql6 	= "UPDATE $tableName2 SET datum_meest_recente_vog_56 = '$laatstevog', kenmerk_vog_68 = '$kenmerkvog', scan_foto_van_je_vog_364 = '$scanvog' WHERE entity_id = '$contactid' AND (datum_meest_recente_vog_56 <= '$laatstevog' OR datum_meest_recente_vog_56 IS NULL)";
    	$sql6 	= "UPDATE $tableName2 SET datum_meest_recente_vog_56 = '$laatstevog', kenmerk_vog_68 = '$kenmerkvog' WHERE entity_id = '$contact_id' AND (datum_meest_recente_vog_56 <= '$laatstevog' OR datum_meest_recente_vog_56 IS NULL)";
    	watchdog('php', '<pre>sql6:'. print_r($sql6, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    	#watchdog('php', '<pre>contactid:'. print_r($contactid, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    	#watchdog('php', '<pre>kenmerkvog:'. print_r($kenmerkvog, TRUE) .'</pre>', NULL, WATCHDOG_DEBUG);
    	$dao6	= CRM_Core_DAO::executeQuery( $sql6 );
    }

    if ($vogrecent AND $vognodig == 'noggoed') {
      	$sql7   = "UPDATE $tableName1 SET datum_laatste_vog_603 = '$vogrecent', kenmerk_vog_602 = '$kenmerkrecent' WHERE entity_id = '$part_id' AND datum_laatste_vog_603 iS NULL";
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
