<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  require_once $_SERVER["DOCUMENT_ROOT"] . "/session/session.php";

  error_log( "====> post=" . print_r( $_POST, true ) );

  $bSignedIn = signIn();

  error_log( '==> bSignedIn=' . $bSignedIn );

  echo json_encode( $bSignedIn );
?>
