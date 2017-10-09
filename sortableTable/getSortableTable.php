<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER['DOCUMENT_ROOT'].'/../common/util.php';
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/context.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/postSecurity.php";
  error_log( "==> post=" . print_r( $_POST, true ) );

  $sTargetObjectType = isset( $_POST['target_object_type'] ) ? ( ' -t ' . $_POST['target_object_type'] ) : '';
  $sTargetObjectId = isset( $_POST['target_object_id'] ) ? ( ' -i ' . $_POST['target_object_id'] ) : '';

  $command = quote( getenv( "PYTHON" ) ) . " ../database/getSortableTable.py 2>&1 "
    . ' -o ' . $_POST['object_type']
    . ' -r ' . $_SESSION['panelSpy']['user']['role']
    . $sTargetObjectType
    . $sTargetObjectId
    . $g_sContext;

  error_log( "==> command=" . $command );
  exec( $command, $output, $status );
  error_log( "==> output=" . print_r( $output, true ) );

  $sRows = $output[ count( $output ) - 1 ];

  echo $sRows;
?>
