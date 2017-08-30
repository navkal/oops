<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/postSecurity.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/context.php";
  error_log( "==> post=" . print_r( $_POST, true ) );

  // Execute command
  $command = quote( getenv( "PYTHON" ) ) . " ../database/getDeviceDropdowns.py 2>&1 " . $g_sContext;
  error_log( "==> command=" . $command );
  exec( $command, $output, $status );
  error_log( "==> output=" . print_r( $output, true ) );

  $aSourcePaths = [];
  $aLocations = [];

  for ( $i = 1; $i <= 500; $i ++ )
  {
    array_push( $aSourcePaths, $i );
    $aLocation =
    [
      'loc_new' => $i,
      'loc_old' => 'old-' . $i,
      'loc_descr' => 'descr ' . $i
    ];
    array_push( $aLocations, $aLocation );
  }

  $tResult =
  [
    'sourcePaths' => $aSourcePaths,
    'locations' => $aLocations
  ];

  $sResult = json_encode( $tResult );

  error_log( '=====> dropdowns=' . $sResult );

  echo $sResult;
?>
