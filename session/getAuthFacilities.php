<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/context.php";
  error_log( "====> post=" . print_r( $_POST, true ) );

  // Execute command
  $command = quote( getenv( "PYTHON" ) ) . " ../database/getAuthFacilities.py 2>&1 -u " . $_SESSION['panelSpy']['user']['username'] . $g_sContext;
  error_log( "===> command=" . $command );
  exec( $command, $output, $status );
  error_log( "===> output=" . print_r( $output, true ) );

  // Extract results from database output
  $sFacilities = $output[ count( $output ) - 1 ];
  $tFacilities = json_decode( $sFacilities );
  $aMap = (array) $tFacilities->name_map;

  if ( count( $aMap ) == 1 )
  {
    $sKey = array_keys( $aMap )[0];
    $sVal = $aMap[$sKey];

    $_SESSION['panelSpy']['context']['facility'] = $sKey;
    $_SESSION['panelSpy']['context']['facilityFullname'] = $sVal;
    $sResult = json_encode( '' );
  }
  else
  {
    $sResult = $sFacilities;
  }

  echo $sResult;
?>
