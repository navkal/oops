<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  error_log( "====> post=" . print_r( $_POST, true ) );

  // Update password for specified user
  $sUsername = $_POST['username'];
  $sPassword = $_POST['password'];
  $command = quote( getenv( "PYTHON" ) ) . " ../database/changePassword.py 2>&1 -u " . $sUsername . ' -p ' . $sPassword;
  error_log( "===> command=" . $command );
  exec( $command, $output, $status );
  error_log( "===> output=" . print_r( $output, true ) );

  // Extract user from database output
  $sUser = $output[ count( $output ) - 1 ];
  $aUser = (array) json_decode( $sUser );
  error_log( '===> user=' . print_r( $aUser, true ) );

  // Update user
  if ( $aUser['signInId'] )
  {
    $_SESSION['panelSpy']['user'] = $aUser;
  }
  else
  {
    $_SESSION['panelSpy']['user'] = [];
  }

  echo $sUser;
?>
