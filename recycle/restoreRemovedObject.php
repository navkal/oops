<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/postSecurity.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/context.php";

  error_log( "==> post=" . print_r( $_POST, true ) );

  // Get posted values
  $sId = quote( $_POST['id'] );
  $sParentId = quote( isset( $_POST['parent_id'] ) ? $_POST['parent_id'] : '' );
  $sRoomId = quote( isset( $_POST['room_id'] ) ? $_POST['room_id'] : '' );


  // Restore object
  $command = quote( getenv( "PYTHON" ) ) . " ../database/restoreRemovedObject.py 2>&1 -b " . $_SESSION['panelSpy']['user']['username']
    . ' -i ' . $sId
    . ' -p ' . $sParentId
    . ' -r ' . $sRoomId
    . $g_sContext;

  error_log( "==> command=" . $command );
  exec( $command, $output, $status );
  error_log( "==> output=" . print_r( $output, true ) );

  // Echo status
  $sStatus = $output[ count( $output ) - 1 ];
  echo $sStatus;
?>
