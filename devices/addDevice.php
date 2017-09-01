<?php
  // Copyright 2017 Panel Spy.  All rights reserved.

  require_once $_SERVER['DOCUMENT_ROOT'] . '/../common/util.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/util/postSecurity.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/util/context.php';
  error_log( '==> post=' . print_r( $_POST, true ) );

  // Get attributes
  $sParentId = $_POST['parent_id'];
  $sName = quote( $_POST['name'] );
  $sRoomId = quote( $_POST['room_id'] );

  error_log( '===> addDevice: room_id=' . $sRoomId );

  // Format command
  $command = quote( getenv( 'PYTHON' ) ) . ' ../database/addDevice.py 2>&1 -b ' . $_SESSION['panelSpy']['user']['username']
    . ' -p ' . $sParentId
    . ' -n ' . $sName
    . ' -r ' . $sRoomId
    . $g_sContext;

  // Execute command
  error_log( '==> command=' . $command );
  exec( $command, $output, $status );
  error_log( '==> output=' . print_r( $output, true ) );

  // Echo status
  $sStatus = $output[ count( $output ) - 1 ];

  echo $sStatus;
?>
