<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";

  $command = quote( getenv( "PYTHON" ) ) . " database/recoverAdmin.py 2>&1";
  error_log( "===> command=" . $command );
  exec( $command, $output, $status );
  error_log( "===> output=" . print_r( $output, true ) );

  // Extract user from database output
  $sUser = $output[ count( $output ) - 1 ];
  $_SESSION['panelSpy']['user'] = (array) json_decode( $sUser );

  // Delete single-use script and redirect
  unlink(__FILE__);
  header( "Location: /" );
?>
