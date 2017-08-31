<?php
  // Copyright 2017 Panel Spy.  All rights reserved.

  require_once $_SERVER['DOCUMENT_ROOT'] . '/../common/util.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/util/postSecurity.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/util/context.php';
  error_log( '==> post=' . print_r( $_POST, true ) );

  // Get attributes
  $sSourcePath = $_POST['source_path'];
  $sName = quote( $_POST['name'] );
  $sLocationId = $_POST['room_id'];

  // Format command
  // $command = quote( getenv( 'PYTHON' ) ) . ' ../database/addDevice.py 2>&1 -b ' . $_SESSION['panelSpy']['user']['username']
    // . ' -s ' . $sSourcePath
    // . ' -n ' . $sName
    // . ' -l ' . $sLocationId
    // . $g_sContext;

  // Execute command
  // error_log( '==> command=' . $command );
  // exec( $command, $output, $status );
  // error_log( '==> output=' . print_r( $output, true ) );

  // Echo status
  // $sStatus = $output[ count( $output ) - 1 ];

  $sStatus = json_encode( 'debug' );
  echo $sStatus;
?>
