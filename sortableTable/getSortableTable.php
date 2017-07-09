<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER['DOCUMENT_ROOT'].'/../common/util.php';
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/context.php";
  error_log( "==> post=" . print_r( $_POST, true ) );

  $command = quote( getenv( "PYTHON" ) ) . " ../database/getSortableTable.py 2>&1 -o " . $_POST['object_type'] . $g_sContext;;
  error_log( "==> command=" . $command );
  exec( $command, $output, $status );
  error_log( "==> output=" . print_r( $output, true ) );

  $sRows = $output[ count( $output ) - 1 ];

  echo $sRows;
?>
