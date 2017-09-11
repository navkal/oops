<?php
  // Copyright 2017 Panel Spy.  All rights reserved.

  require_once $_SERVER['DOCUMENT_ROOT'] . '/../common/util.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/util/postSecurity.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/util/context.php';
  error_log( '==> post=' . print_r( $_POST, true ) );

  // Get attributes
  $sObjectType = $_POST['object_type'];
  $sParentId = $_POST['parent_id'];
  $sTail = $_POST['tail'];
  $sVoltageId = $_POST['voltage_id'];
  $sRoomId = quote( $_POST['room_id'] );
  $sDescription = quote( $_POST['description'] );

  // Format command
  $command = quote( getenv( 'PYTHON' ) ) . ' ../database/addCircuitObject.py 2>&1 -b ' . $_SESSION['panelSpy']['user']['username']
    . ' -o ' . $sObjectType
    . ' -p ' . $sParentId
    . ' -t ' . $sTail
    . ' -v ' . $sVoltageId
    . ' -r ' . $sRoomId
    . ' -d ' . $sDescription
    . $g_sContext;


  // Execute command
  error_log( '==> command=' . $command );
  exec( $command, $output, $status );
  error_log( '==> output=' . print_r( $output, true ) );

  // Echo status
  $sStatus = $output[ count( $output ) - 1 ];

  echo $sStatus;
?>
