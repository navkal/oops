<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/context.php";
  error_log( "====> post=" . print_r( $_POST, true ) );

  // Execute command
  $command = quote( getenv( "PYTHON" ) ) . " ../database/getFacilities.py 2>&1 -u " . $_SESSION['panelSpy']['user']['username'] . $g_sContext;
  error_log( "===> command=" . $command );
  exec( $command, $output, $status );
  error_log( "===> output=" . print_r( $output, true ) );

  // Extract results from database output
  $sFacilities = $output[ count( $output ) - 1 ];

  echo $sFacilities;
?>