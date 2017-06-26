<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  error_log( "====> post=" . print_r( $_POST, true ) );

  // Initialize session storage
  $_SESSION['panelSpy']['user'] = [];

  // Look up user in database
  $sUsername = $_POST['username'];
  $sPassword = quote( $_POST['password'] );
  $command = quote( getenv( "PYTHON" ) ) . " ../database/signIn.py 2>&1 -u " . $sUsername . ' -p ' . $sPassword;
  error_log( "===> command=" . $command );
  exec( $command, $output, $status );
  error_log( "===> output=" . print_r( $output, true ) );

  // Extract user from database output
  $sUser = $output[ count( $output ) - 1 ];
  $aUser = (array) json_decode( $sUser );

  // If database assigned a signin id, load user information
  if ( $aUser['signInId'] )
  {
    $_SESSION['panelSpy']['user'] = $aUser;
  }
  
  error_log( '========> signed in with context=' . print_r( $_SESSION['panelSpy']['context'], true ) );

  echo $sUser;
?>
