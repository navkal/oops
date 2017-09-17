<?php
  // Copyright 2017 Panel Spy.  All rights reserved.

  require_once $_SERVER['DOCUMENT_ROOT'] . '/../common/util.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/util/postSecurity.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/util/context.php';
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/define.php";

  error_log( '==> post=' . print_r( $_POST, true ) );
  error_log( '==> files=' . print_r( $_FILES, true ) );

  // Get attributes
  $sObjectType = $_POST['object_type'];
  $sParentId = quote( $_POST['parent_id'] );
  $sTail = $_POST['tail'];
  $sVoltageId = $_POST['voltage_id'];
  $sRoomId = quote( $_POST['room_id'] );
  $sDescription = quote( $_POST['description'] );

  // Handle image upload, if any
  $sFilename = '';
  $aMessages = [];
  $aSelectors = [];

  if ( isset( $_FILES['panel_photo_file'] ) )
  {
    // We got a file upload; check for size before accepting
    if ( $_FILES['panel_photo_file']['size'] <= ( UPLOAD_MAX_KB * 1000 ) )
    {
      // File is not too big; save it for further processing by backend
      $sFilename = tempnam( sys_get_temp_dir(), 'ps_' );
      move_uploaded_file( $_FILES['panel_photo_file']['tmp_name'], $sFilename );
    }
    else
    {
      // File is too big; reject
      array_push( $aMessages, 'Panel Photo (' . number_format( $_FILES['panel_photo_file']['size'] / 1000 ) . ' KB) exceeds maximum size of ' . number_format( UPLOAD_MAX_KB ) . ' KB' );
      $aSelectors = [ '#panel_photo_file' ];
    }
  }
  $sFilename = quote( $sFilename );

  if ( empty( $aMessages ) )
  {
    // Format command
    $command = quote( getenv( 'PYTHON' ) ) . ' ../database/' . $sOperation . 'CircuitObject.py 2>&1 -b ' . $_SESSION['panelSpy']['user']['username']
      . $sIdParam
      . ' -o ' . $sObjectType
      . ' -p ' . $sParentId
      . ' -t ' . $sTail
      . ' -v ' . $sVoltageId
      . ' -r ' . $sRoomId
      . ' -d ' . $sDescription
      . ' -f ' . $sFilename
      . $g_sContext;

    // Execute command
    error_log( '==> command=' . $command );
    exec( $command, $output, $status );
    error_log( '==> output=' . print_r( $output, true ) );

    // Extract result status
    $sStatus = $output[ count( $output ) - 1 ];

    // Decode result status
    $tStatus = json_decode( $sStatus );
    $aMessages = $tStatus->messages;

    // If there is an error message, indicate which dialog box fields to highlight.
    // Include the fields that make up the path, since (for now) those are the elements that can produce an error in this operation
    if ( ! empty( $aMessages ) )
    {
      $aSelectors = [ '#parent_path_container .selection', '#number', '#name' ];
    }
  }

  $tRsp =
  [
    'messages' => $aMessages,
    'selectors' => $aSelectors
  ];

  $sRsp = json_encode( $tRsp );

  // Echo status
  echo $sRsp;
?>
