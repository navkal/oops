<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  error_log( "====> post=" . print_r( $_POST, true ) );

  // Add user
  $sUsername = $_POST['username'];
  $sPassword = $_POST['password'];
  $sRole = $_POST['role'];
  $command = quote( getenv( "PYTHON" ) ) . " ../database/addUser.py 2>&1 -u " . $sUsername . ' -p ' . $sPassword . ' -r ' . $sRole;
  error_log( "===> command=" . $command );
  exec( $command, $output, $status );
  error_log( "===> output=" . print_r( $output, true ) );

  echo json_encode( '' );
?>
