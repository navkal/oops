<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";

  $_SESSION['panelSpy']['user'] = [];

  echo json_encode( $_SESSION['panelSpy'] );
?>
