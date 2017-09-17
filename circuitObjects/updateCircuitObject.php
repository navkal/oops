<?php
  // Copyright 2017 Panel Spy.  All rights reserved.

  require_once $_SERVER['DOCUMENT_ROOT'] . '/../common/util.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/util/postSecurity.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/util/context.php';
  error_log( '==> post=' . print_r( $_POST, true ) );
  error_log( '==> files=' . print_r( $_FILES, true ) );

  // Get attributes
  $sId = quote( $_POST['id'] );
  $sObjectType = $_POST['object_type'];
  $sParentId = quote( $_POST['parent_id'] );
  $sTail = $_POST['tail'];
  $sVoltageId = $_POST['voltage_id'];
  $sRoomId = quote( $_POST['room_id'] );
  $sDescription = quote( $_POST['description'] );

  // Get uploaded file
  $sFilename = '';
  if ( isset( $_FILES['panel_photo_file'] ) && $_FILES['panel_photo_file']['size'] <= 5000000 )
  {
    $sFilename = tempnam( sys_get_temp_dir(), 'ps_' );
    move_uploaded_file( $_FILES['panel_photo_file']['tmp_name'], $sFilename );
  }
  $sFilename = quote( $sFilename );

  // Format command
  $command = quote( getenv( 'PYTHON' ) ) . ' ../database/updateCircuitObject.py 2>&1 -b ' . $_SESSION['panelSpy']['user']['username']
    . ' -i ' . $sId
    . ' -o ' . $sObjectType
    . ' -p ' . $sParentId
    . ' -t ' . $sTail
    . ' -v ' . $sVoltageId
    . ' -r ' . $sRoomId
    . ' -d ' . $sDescription
    . ' -f ' . $sFilename
    . $g_sContext;


  // Execute command
  error_log( '==> command=' . $command );
  exec( $command, $output, $status );
  error_log( '==> output=' . print_r( $output, true ) );

  // Echo status
  $sStatus = $output[ count( $output ) - 1 ];

  echo $sStatus;
?>
