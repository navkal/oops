<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  error_log( "====> post=" . print_r( $_POST, true ) );

  // Get user attributes
  $sUsername = $_POST['username'];
  $sPassword = $_POST['password'];
  $sRole = $_POST['role'];
  $sDescription = $_POST['description'];

  // Determine whether to force target user to change password on next signin
  $bForceChangePassword = ( $sPassword != '' ) && ( $_SESSION['panelSpy']['user']['username'] != $sUsername );
  $iForceChangePassword = $bForceChangePassword ? 1 : 0;

  // Update user
  $command = quote( getenv( "PYTHON" ) ) . " ../database/updateUser.py 2>&1 -b " . $_SESSION['panelSpy']['user']['username'] . ' -u ' . $sUsername . ( ( $sPassword == '' ) ? '' : ( ' -p ' . $sPassword ) ) . ' -r ' . $sRole  . ' -d ' . quote( $sDescription ) . ' -f ' . $iForceChangePassword;
  error_log( "===> command=" . $command );
  exec( $command, $output, $status );
  error_log( "===> output=" . print_r( $output, true ) );

  // If signed-in user is same as updated user, update specific fields in session storage
  if ( $sUsername == $_SESSION['panelSpy']['user']['username'] )
  {
    $_SESSION['panelSpy']['user']['description'] = $sDescription;
  }

  // Echo user
  echo json_encode( $_SESSION['panelSpy']['user'] );
?>
