<?php
  // Copyright 2017 Panel Spy.  All rights reserved.

  require_once $_SERVER["DOCUMENT_ROOT"] . "/session/session.php";
  echo json_encode( signedIn() );
?>
