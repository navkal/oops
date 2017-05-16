<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER['DOCUMENT_ROOT'].'/../common/util.php';
  error_log( '====> post=' . print_r( $_POST, true ) );

  $sSearchText = $_POST['searchText'];
  $iRequestTime = $_POST['requestTime'];

  $command = quote( getenv( "PYTHON" ) ) . " ../db/search.py 2>&1 -s " . quote( $sSearchText, false );
  error_log( "===> command=" . $command );
  exec( $command, $output, $status );
  error_log( "===> output=" . print_r( $output, true ) );

  $sSearchResults = $output[ count( $output ) - 1 ];
  $tSearchResults = json_decode( $sSearchResults );
  $aSearchResults = $tSearchResults->searchResults;

  $tResult =
  [
    'requestTime' => $iRequestTime,
    'searchText' => $sSearchText,
    'searchResults' => $aSearchResults
  ];

  echo json_encode( $tResult );
?>
