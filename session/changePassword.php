<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/postSecurity.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/context.php";

  // Log post array without password
  $sPasswordMask = '<PASSWORD>';
  $aPostMinusPassword = $_POST;
  $aPostMinusPassword['oldPassword'] = $sPasswordMask;
  $aPostMinusPassword['password'] = $sPasswordMask;
  error_log( "==> post=" . print_r( $aPostMinusPassword, true ) );

  // Get parameters
  $sUsername = $_POST['username'];
  $sOldPassword = quote( $_POST['oldPassword'], false );
  $sPassword = quote( $_POST['password'], false );

  // Execute command
  $command = quote( getenv( "PYTHON" ) ) . " ../database/changePassword.py 2>&1 -u " . $sUsername . ' -o ' . $sOldPassword . ' -p ' . $sPassword . $g_sContext;
  $sCommandMinusPassword = preg_replace( '/ -o .* -p/', ' -o ' . $sPasswordMask . ' -p', $command, 1 );
  $sCommandMinusPassword = preg_replace( '/ -p .* -y/', ' -p ' . $sPasswordMask . ' -y', $sCommandMinusPassword, 1 );
  error_log( "==> command=" . $sCommandMinusPassword );
  exec( $command, $output, $status );
  error_log( "==> output=" . print_r( $output, true ) );

  // Extract user from database output
  $sUser = $output[ count( $output ) - 1 ];
  $aUser = (array) json_decode( $sUser );

  // Update user
  if ( $aUser['signInId'] )
  {
    $_SESSION['panelSpy']['user'] = $aUser;
  }

  echo $sUser;
?>
