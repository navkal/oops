<?php
  // Copyright 2018 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"]."/../common/util.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/context.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/postSecurity.php";
  error_log( "==> post=" . print_r( $_POST, true ) );

  // Add note to Activity Log
  $command = quote( getenv( "PYTHON" ) ) . " ../database/addNote.py 2>&1 -u " . $_SESSION['panelSpy']['user']['username'] . " -t " . $_POST['object_type'] . " -i " . $_POST['object_id'] . " -n " . quote( $_POST['note'] ) . $g_sContext;
  error_log( "==> command=" . $command );
  exec( $command, $output, $status );
  error_log( "==> output=" . print_r( $output, true ) );

  $sResult = $output[ count( $output ) - 1 ];
  echo $sResult;
?>
