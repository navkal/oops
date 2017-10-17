<?php
  // Copyright 2017 Panel Spy.  All rights reserved.

  require_once $_SERVER['DOCUMENT_ROOT'] . '/../common/util.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/util/postSecurity.php';

  error_log( '==> post=' . print_r( $_POST, true ) );
  error_log( '==> files=' . print_r( $_FILES, true ) );

  $sId = quote( $_POST['id'] );
  $sIdParam = ' -i ' . $sId;
  $sOperation = 'update';

  require_once $_SERVER['DOCUMENT_ROOT'] . '/circuitObjects/addUpdateCommon.php';
?>
