<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  error_log( "====> post=" . print_r( $_POST, true ) );

  $_SESSION['panelSpy']['context']['facilityDescr'] = $_POST['facilityDescr'];
  $_SESSION['panelSpy']['context']['fLower'] = $_POST['fLower'];

  echo json_encode( $_SESSION['panelSpy']['user'] );
?>
