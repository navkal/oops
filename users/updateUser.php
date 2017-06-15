<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  error_log( "====> post=" . print_r( $_POST, true ) );

  // Get user attributes
  $sUsername = $_POST['username'];
  $sPassword = $_POST['password'];
  $sRole = $_POST['role'];

  // Force change password if requestor is admin and target is another user
  $bForceChangePassword = ( $sPassword != '' ) && ( $_SESSION['panelSpy']['user']['username'] == 'admin' ) && ( $sUsername != 'admin' );
  $sForceChangePassword = $bForceChangePassword ? 'True' : 'False';

  // Update user
  $command = quote( getenv( "PYTHON" ) ) . " ../database/updateUser.py 2>&1 -u " . $sUsername . ( ( $sPassword == '' ) ? '' : ( ' -p ' . $sPassword ) ) . ' -r ' . $sRole . ' -f ' . $sForceChangePassword;
  error_log( "===> command=" . $command );
  exec( $command, $output, $status );
  error_log( "===> output=" . print_r( $output, true ) );

  // Echo status
  $sStatus = $output[ count( $output ) - 1 ];
  echo $sStatus;
?>
