<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  error_log( "====> post=" . print_r( $_POST, true ) );

  // Initialize session storage
  $_SESSION['panelSpy']['reservedDelimiter'] = '-_-_-';
  $_SESSION['panelSpy']['session'] = [];

  // Look up user in database
  $sUsername = $_POST['username'];
  $sPassword = $_POST['password'];
  $command = quote( getenv( "PYTHON" ) ) . " ../database/signIn.py 2>&1 -u " . $sUsername . ' -p ' . $sPassword;
  error_log( "===> command=" . $command );
  exec( $command, $output, $status );
  error_log( "===> output=" . print_r( $output, true ) );

  // Extract user from database output
  $sUser = $output[ count( $output ) - 1 ];
  error_log( '===> user=' . $sUser );
  $tUser = json_decode( $sUser );
  error_log( '===> user=' . print_r( $tUser, true ) );

  // If database assigned a signin id, load session state
  if ( $tUser->signInId )
  {
    $_SESSION['panelSpy']['session']['username'] = $sUsername;
    $_SESSION['panelSpy']['session']['role'] = $tUser->role;
    $_SESSION['panelSpy']['session']['changePassword'] = $tUser->changePassword;
    $_SESSION['panelSpy']['session']['signInId'] = $tUser->signInId;
  }

  error_log( '==> signInId=' . $tUser->signInId );
  echo json_encode( $tUser->signInId );
?>
