<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  error_log( "====> post=" . print_r( $_POST, true ) );

  // Get user attributes
  $sUsername = $_POST['username'];
  $sPassword = $_POST['password'];
  $sRole = $_POST['role'];
  $sDescription = $_POST['description'];

  // Add user
  $command = quote( getenv( "PYTHON" ) ) . " ../database/addUser.py 2>&1 -b " . $_SESSION['panelSpy']['user']['username'] . ' -u ' . $sUsername . ' -p ' . $sPassword . ' -r ' . $sRole . ' -d ' . quote( $sDescription );
  error_log( "===> command=" . $command );
  exec( $command, $output, $status );
  error_log( "===> output=" . print_r( $output, true ) );

  // Echo status
  $sStatus = $output[ count( $output ) - 1 ];
  echo $sStatus;
?>
