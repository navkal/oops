<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/session/session.php";

  error_log( "====> post=" . print_r( $_POST, true ) );

  $sSignInId = signIn( $_POST['username'], $_POST['password'] );

  error_log( '==> echoing sSignInId=' . $sSignInId );

  echo json_encode( $sSignInId );
?>
