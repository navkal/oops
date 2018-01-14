<?php
  // Copyright 2018 Panel Spy.  All rights reserved.

  require_once $_SERVER["DOCUMENT_ROOT"]."/util/abort.php";
  if ( ! isset( $_SESSION['panelSpy']['recoverAdminContext'] ) )
  {
    abort();
  }

  $command = quote( getenv( "PYTHON" ) ) . " database/recoverAdmin.py 2>&1 -y " . quote( $_SESSION['panelSpy']['recoverAdminContext']['enterprise'] );
  error_log( "==> command=" . $command );
  exec( $command, $output, $status );
  error_log( "==> output=" . print_r( $output, true ) );

  $_SESSION['panelSpy']['user'] = [];

  // Redirect
  header( "Location: /" );
?>
