<?php
  // Copyright 2018 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/postSecurity.php";

  $_SESSION['panelSpy']['user'] = [];

  echo json_encode( $_SESSION['panelSpy'] );
?>
