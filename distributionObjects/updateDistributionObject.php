<?php
  // Copyright 2017 Panel Spy.  All rights reserved.

  require_once $_SERVER['DOCUMENT_ROOT'] . '/../common/util.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/util/postSecurity.php';

  $sId = quote( $_POST['id'] );
  $sIdParam = ' -i ' . $sId;
  $sOperation = 'update';

  require_once $_SERVER['DOCUMENT_ROOT'] . '/distributionObjects/addUpdateCommon.php';
?>
