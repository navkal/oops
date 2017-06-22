<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  error_log( "====> post=" . print_r( $_POST, true ) );

  // Get parameters
  $sUsername = $_POST['username'];
  $sOldPassword = quote( $_POST['oldPassword'] );
  $sPassword = quote( $_POST['password'] );

  // Execute command
  $command = quote( getenv( "PYTHON" ) ) . " ../database/changePassword.py 2>&1 -u " . $sUsername . ' -o ' . $sOldPassword . ' -p ' . $sPassword;
  error_log( "===> command=" . $command );
  exec( $command, $output, $status );
  error_log( "===> output=" . print_r( $output, true ) );

  // Extract user from database output
  $sUser = $output[ count( $output ) - 1 ];
  $aUser = (array) json_decode( $sUser );

  // Update user
  if ( $aUser['signInId'] )
  {
    $_SESSION['panelSpy']['user'] = $aUser;
  }

  echo $sUser;
?>
