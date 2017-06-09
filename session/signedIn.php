<?php
  // Copyright 2017 Panel Spy.  All rights reserved.

  error_log( "====> post=" . print_r( $_POST, true ) );

  require_once $_SERVER["DOCUMENT_ROOT"] . "/session/session.php";
  echo json_encode( signedIn( $_POST['signInId'] ) );
?>
