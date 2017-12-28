<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/context.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/abort.php";
  if ( count( $_POST ) == 0 )
  {
    abort();
  }

  // Log post array without password
  $sPasswordMask = '<PASSWORD>';
  $aPostMinusPassword = $_POST;
  $aPostMinusPassword['password'] = $sPasswordMask;
  error_log( "==> post=" . print_r( $aPostMinusPassword, true ) );

  // Initialize session storage
  $_SESSION['panelSpy']['user'] = [];

  // Look up user in database
  $sUsername = $_POST['username'];
  $sPassword = quote( $_POST['password'], false );
  $command = quote( getenv( "PYTHON" ) ) . " ../database/signIn.py 2>&1 -u " . $sUsername . ' -p ' . $sPassword . $g_sContext;
  error_log( "==> command=" . preg_replace( '/ -p .* -y/', ' -p ' . $sPasswordMask . ' -y', $command, 1 ) );
  exec( $command, $output, $status );
  error_log( "==> output=" . print_r( $output, true ) );

  // Extract user from database output
  $sUser = $output[ count( $output ) - 1 ];
  $aUser = (array) json_decode( $sUser );

  // If database assigned a signin id, load user information
  if ( isset( $aUser['signInId'] ) && $aUser['signInId'] )
  {
    // Move enterprise fullname from user structure to to context structure
    $_SESSION['panelSpy']['context']['enterpriseFullname'] = $aUser['enterprise_fullname'];
    unset( $aUser['enterprise_fullname'] );

    // Save user structure
    $_SESSION['panelSpy']['user'] = $aUser;

    // Clear failure flag
    $_SESSION['panelSpy']['signInFailed'] = false;

    error_log( '==> Signed in. Context=' . print_r( $_SESSION['panelSpy']['context'], true ) );
  }
  else
  {
    // Set failure flag
    $_SESSION['panelSpy']['signInFailed'] = true;

    error_log( '==> Sign-in failed. Context=' . print_r( $_SESSION['panelSpy']['context'], true ) );
  }

  echo json_encode( $_SESSION['panelSpy'] );
?>
