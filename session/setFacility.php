<?php
  // Copyright 2018 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/postSecurity.php";

  error_log( "==> post=" . print_r( $_POST, true ) );

  $_SESSION['panelSpy']['context']['facilityFullname'] = $_POST['facilityFullname'];
  $_SESSION['panelSpy']['context']['facility'] = $_POST['facility'];

  echo json_encode( $_SESSION['panelSpy'] );
?>
