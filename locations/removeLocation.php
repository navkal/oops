<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/postSecurity.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/context.php";
  error_log( "==> post=" . print_r( $_POST, true ) );

  // Get user attributes
  $sId = quote( $_POST['id'] );
  $sComment = quote( $_POST['comment'] );

  // Remove location
  $command = quote( getenv( "PYTHON" ) ) . " ../database/removeLocation.py 2>&1 -b " . $_SESSION['panelSpy']['user']['username']
    . ' -i ' . $sId
    . ' -c ' . $sComment
    . $g_sContext;

  error_log( "==> command=" . $command );
  exec( $command, $output, $status );
  error_log( "==> output=" . print_r( $output, true ) );

  // Echo status
  $sStatus = $output[ count( $output ) - 1 ];
  echo $sStatus;
?>
