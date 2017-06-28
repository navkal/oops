<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"]."/../common/util.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/context.php";
  error_log( "====> post=" . print_r( $_POST, true ) );

  // Save notes in database
  $command = quote( getenv( "PYTHON" ) ) . " ../database/saveNotes.py 2>&1 -u " . $_SESSION['panelSpy']['user']['username'] . " -t " . $_POST['targetTable'] . " -c " . $_POST['targetColumn'] . " -v " . $_POST['targetValue'] . " -n " . quote( $_POST['notes'] ) . $g_sContext;
  error_log( "===> command=" . $command );
  exec( $command, $output, $status );
  error_log( "===> output=" . print_r( $output, true ) );

  $sResult = $output[ count( $output ) - 1 ];
  echo $sResult;
?>
