<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";

  error_log( "====> post=" . print_r( $_POST, true ) );

  $sUsername = $_POST['username'];
  $sPassword = $_POST['password'];

  // Initialize Panel Spy session storage
  $_SESSION['panelSpy'] = [];

  // Build temporary array
  $aSession = [];

  switch( $sUsername )
  {
    case 'admin':
      $aSession['role'] = 'administrator';
      break;

    case 'test':
      $aSession['role'] = 'technician';
      break;

    case 'user':
      $aSession['role'] = 'visitor';
      break;
  }

  $sSignInId = '';
  if ( isset( $aSession['role'] ) )
  {
    $aSession['username'] = $sUsername;
    $sSignInId = uniqid();
    $aSession['signInId'] = $sSignInId;
    $_SESSION['panelSpy']['session'] = $aSession;
  }

  error_log( '==> echoing sSignInId=' . $sSignInId );

  echo json_encode( $sSignInId );



















  function signIn( $sUsername, $sPassword )
  {
    // Initialize Panel Spy session storage
    $_SESSION['panelSpy'] = [];

    // Look up user in database
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
      $_SESSION['panelSpy']['session'] = [];
      $_SESSION['panelSpy']['session']['username'] = $sUsername;
      $_SESSION['panelSpy']['session']['role'] = $tUser->role;
      $_SESSION['panelSpy']['session']['signInId'] = $tUser->signInId;
    }

    error_log( '==> signIn() signedin=' . $tUser->signInId );
    return $tUser->signInId;
  }


?>
