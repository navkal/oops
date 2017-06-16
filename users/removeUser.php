<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  error_log( "====> post=" . print_r( $_POST, true ) );

  // Get user attributes
  $sUsername = $_POST['username'];

  // Remove user
  $command = quote( getenv( "PYTHON" ) ) . " ../database/removeUser.py 2>&1 -u " . $sUsername;
  error_log( "===> command=" . $command );
  exec( $command, $output, $status );
  error_log( "===> output=" . print_r( $output, true ) );

  // Echo status
  $sStatus = $output[ count( $output ) - 1 ];
  echo $sStatus;
?>
