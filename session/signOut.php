<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";

  $_SESSION['panelSpy']['session'] = [];

  $tRsp = [ 'signInId' => '' ];
  echo json_encode( $tRsp );
?>
