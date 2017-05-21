<?php
  // Copyright 2016 Panel Spy.  All rights reserved.

  require_once $_SERVER["DOCUMENT_ROOT"]."/../common/util.php";

  $_SESSION["downloadDone"] = false;

  $g_iTestDepth = 0;
  $sMsgDepth = " tree dump to depth: " . ( $g_iTestDepth ? $g_iTestDepth : "Full" );

  $g_iStartTime = time();
  error_log( "===> [" . $g_iStartTime . "] Starting" . $sMsgDepth );

  $g_sDumpId = base_convert ( $g_iStartTime , 10, 36 );

  $g_aTree = [];
  walkSubtree( "" );
  downloadTree();

  $g_iEndTime = time();
  error_log( "===> [" . $g_iEndTime . "] Finished" . $sMsgDepth );

  $iElapsedSec = $g_iEndTime - $g_iStartTime;
  error_log( "===> Elapsed time: " . $iElapsedSec . " seconds" );
  $_SESSION["downloadDone"] = true;

  exit();


  function walkSubtree( $sPath )
  {
    // Retrieve object
    $sSelector = ( $sPath == "" ) ? "" : ' -p ' . $sPath;
    $sCommand = quote( getenv( "PYTHON" ) ) . " ../database/getObject.py 2>&1 -t cirobj " . $sSelector;
    // error_log( "===> command=" . $sCommand );
    exec( $sCommand, $sOutput, $sStatus );
    // error_log( "===> output=" . print_r( $sOutput, true ) );

    // Decode object
    $sJson = $sOutput[ count( $sOutput ) - 1 ];
    $tObject = json_decode( $sJson );

    // Extract path from object
    $sPath = $tObject->path;

    // Detect path format error
    $sError = '';
    if ( strpos( $sPath, $_SESSION["reservedDelimiter"] ) !== false )
    {
      $sError = " (Error: Path contains reserved substring '" . $_SESSION["reservedDelimiter"] . "')";
    }

    // Set up indentation
    $nDepth = count( explode( ".", $sPath ) );
    $sIndent = str_repeat( '-', $nDepth - 1 );

    // Insert path in tree
    global $g_aTree;
    $g_aTree[$sPath] = $sIndent . $sPath . $sError . PHP_EOL;

    // Insert devices in tree
    $aDevices = $tObject->devices;
    foreach( $aDevices as $aDevice )
    {
      $iDeviceId = $aDevice[0];
      $iDeviceLoc = ( $aDevice[1] != "" ) ? $aDevice[1] : $aDevice[2];
      $sDeviceDescr = $aDevice[3];
      $sDevName = $iDeviceId . "," . $iDeviceLoc . "," . $sDeviceDescr;
      $sDevicePath = $sPath . " " . $sDevName;
      $sIndent = str_repeat( ' ', $nDepth );
      $g_aTree[$sDevicePath] = $sIndent . "[" . $sDevName . "]" . PHP_EOL;
    }

    global $g_iTestDepth;
    if ( ! $g_iTestDepth || ( $nDepth < $g_iTestDepth ) )
    {
      // Traverse children
      $aChildren = $tObject->children;
      foreach( $aChildren as $aChild )
      {
        $sChildPath = $aChild[1];
        walkSubtree( $sChildPath );
      }
    }
  }

  function downloadTree()
  {
    // Generate the file
    global $g_sDumpId;
    $sFilename = sys_get_temp_dir() . "/" . "tree_" . $g_sDumpId . ".txt";
    $file = fopen( $sFilename, "w" ) or die( "Unable to open file: " . $sFilename );
    writeTree( $file );
    fclose( $file );

    // Download the file
    downloadFile( $sFilename, "", "text/plain" );

    // Delete the file
    unlink( $sFilename );
  }

  function writeTree( $file )
  {
    global $g_aTree;
    uksort( $g_aTree, "compareKeys" );

    foreach ( $g_aTree as $sKey => $sPath )
    {
      // error_log( "==> key=" . $sKey . " path=" . $sPath );
      fwrite( $file, $g_aTree[$sKey] );
    }
  }

  function compareKeys( $sKey1, $sKey2 )
  {
    // Extract path and device strings
    $aSplit1 = explode( ' ', $sKey1 );
    $aSplit2 = explode( ' ', $sKey2 );
    $sPath1 = array_shift( $aSplit1 );
    $sPath2 = array_shift( $aSplit2 );
    $sDev1 = implode( ' ', $aSplit1 );
    $sDev2 = implode( ' ', $aSplit2 );

    // Split path into fragments
    $aPath1 = explode( '.', $sPath1 );
    $aPath2 = explode( '.', $sPath2 );

    // Compare paths
    $iResult = 0;
    for ( $iFragment = 0; ( $iResult == 0 ) && ( $iFragment < count( $aPath1 ) ) && ( $iFragment < count( $aPath2 ) ); $iFragment++ )
    {
      $sFragment1 = $aPath1[$iFragment];
      $sFragment2 = $aPath2[$iFragment];

      // Extract leading digits
      $iNum1 = substr( $sFragment1, 0, strspn( $sFragment1, "0123456789") );
      $iNum2 = substr( $sFragment2, 0, strspn( $sFragment2, "0123456789") );

      // If both fragments have leading digits, compare numerically
      if ( $iNum1 != "" && $iNum2 != "" )
      {
        $iResult = $iNum1 - $iNum2;
      }

      // If no difference found yet, compare alphabetically
      if ( $iResult == 0 )
      {
        $iResult = strcmp( $sFragment1, $sFragment2 );
      }
    }

    // If leading parts of paths are the same, compare their lengths
    if ( $iResult == 0 )
    {
      $iResult = count( $aPath1 ) - count( $aPath2 );
    }

    // If paths are the same, compare devices
    if ( $iResult == 0 )
    {
      if ( $sDev1 == $sDev2 )
      {
        $iResult = 0;
      }
      else if ( $sDev1 == "" )
      {
        $iResult = -1;
      }
      else if ( $sDev2 == "" )
      {
        $iResult = 1;
      }
      else
      {
        // Compare device IDs, which are guaranteed to be numeric and unique
        $aDev1 = explode( ',', $sDev1 );
        $aDev2 = explode( ',', $sDev2 );
        $iResult = $aDev1[0] - $aDev2[0];
      }
    }

    return $iResult;
  }
?>
