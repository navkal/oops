<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER['DOCUMENT_ROOT'].'/../common/util.php';
  error_log( "====> post=" . print_r( $_POST, true ) );

  $command = quote( getenv( "PYTHON" ) ) . " ../db/getSortableTable.py 2>&1 -o " . $_POST['object_type'];
  error_log( "===> command=" . $command );
  exec( $command, $output, $status );
  error_log( "===> output=" . print_r( $output, true ) );

  $sRows = $output[ count( $output ) - 1 ];

  echo $sRows;
?>
