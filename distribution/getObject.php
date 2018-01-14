<?php
  // Copyright 2018 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"]."/../common/util.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/define.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/context.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/postSecurity.php";

  error_log( "==> post=" . print_r( $_POST, true ) );

  // Get posted values
  $postType = $_POST['object_type'];
  $postSelector = quote( $_POST['object_id'] );

  $command = quote( getenv( "PYTHON" ) ) . " ../database/getObject.py 2>&1 -t " . $postType . ' -i ' . $postSelector  . ' -r ' . $_SESSION['panelSpy']['user']['role'] . $g_sContext;
  error_log( "==> command=" . $command );
  exec( $command, $output, $status );
  error_log( "==> output=" . print_r( $output, true ) );

  $sResult = $output[ count( $output ) - 1 ];

  // Check for illegal path format
  $tResult = json_decode( $sResult );
  if ( isset( $tResult->path ) )
  {
    $sPath = $tResult->path;

    if ( strpos( $sPath, RESERVED_DELIMITER ) !== false )
    {
      $tResult->error = "Path contains reserved substring '" . RESERVED_DELIMITER . "'";
      $sResult = json_encode( $tResult );
    }
  }

  echo $sResult;
?>
