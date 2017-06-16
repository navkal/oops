<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  error_log( "====> post=" . print_r( $_POST, true ) );

  // Get user attributes
  $sUsername = $_POST['username'];
  $sPassword = $_POST['password'];
  $sRole = $_POST['role'];

  // Determine whether to force target user to change password on next signin
  $bForceChangePassword = ( $sPassword != '' ) && ( $_SESSION['panelSpy']['user']['username'] != $sUsername );
  $iForceChangePassword = $bForceChangePassword ? 1 : 0;

  // Update user
  $command = quote( getenv( "PYTHON" ) ) . " ../database/updateUser.py 2>&1 -b " . $_SESSION['panelSpy']['user']['username'] . ' -u ' . $sUsername . ( ( $sPassword == '' ) ? '' : ( ' -p ' . $sPassword ) ) . ' -r ' . $sRole . ' -f ' . $iForceChangePassword;
  error_log( "===> command=" . $command );
  exec( $command, $output, $status );
  error_log( "===> output=" . print_r( $output, true ) );

  // Echo status
  $sStatus = $output[ count( $output ) - 1 ];
  echo $sStatus;
?>
