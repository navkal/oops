<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/postSecurity.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/context.php";
  error_log( "==> post=" . print_r( $_POST, true ) );

  // Get attributes
  $sNew = $_POST['loc_new'];
  $sOld = quote( $_POST['loc_old'] );
  $sDescr = $_POST['loc_descr'];

  // Add Location
  $command = quote( getenv( "PYTHON" ) ) . ' ../database/submitLocation.py 2>&1'
    . ' -n ' . $sNew
    . ' -o ' . $sOld
    . ' -d ' . $sDescr
    . $g_sContext;

  error_log( "==> command=" . $command );
  exec( $command, $output, $status );
  error_log( "==> output=" . print_r( $output, true ) );

  // Echo status
  $sStatus = $output[ count( $output ) - 1 ];
  echo $sStatus;
?>
