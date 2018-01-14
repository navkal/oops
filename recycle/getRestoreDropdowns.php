<?php
  // Copyright 2018 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/postSecurity.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/context.php";
  error_log( "==> post=" . print_r( $_POST, true ) );

  // Execute command
  $command = quote( getenv( "PYTHON" ) ) . ' ../database/getRestoreDropdowns.py 2>&1'
    . ' -i ' . quote( $_POST['dist_object_id'] )
    . ' -t ' . $_POST['object_type']
    . $g_sContext;

  error_log( "==> command=" . $command );
  exec( $command, $output, $status );
  error_log( "==> output=" . print_r( $output, true ) );

  $sResult = $output[ count( $output ) - 1 ];

  echo $sResult;
?>
