<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";

  $command = quote( getenv( "PYTHON" ) ) . " database/recoverAdmin.py 2>&1";
  error_log( "===> command=" . $command );
  exec( $command, $output, $status );
  error_log( "===> output=" . print_r( $output, true ) );

  $_SESSION['panelSpy']['user'] = [];

  // Redirect
  header( "Location: /" );
?>
