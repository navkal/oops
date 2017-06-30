<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/context.php";
  error_log( "====> post=" . print_r( $_POST, true ) );

  // Get user attributes
  $sUsername = $_POST['username'];
  $sPassword = quote( $_POST['password'] );
  $sRole = $_POST['role'];
  $sStatus = $_POST['status'];
  $sFirstName = quote( $_POST['first_name'] );
  $sLastName = quote( $_POST['last_name'] );
  $sEmailAddress = quote( $_POST['email_address'] );
  $sOrganization = quote( $_POST['organization'] );
  $sDescription = quote( $_POST['user_description'] );

  // Add user
  $command = quote( getenv( "PYTHON" ) ) . " ../database/addUser.py 2>&1 -b " . $_SESSION['panelSpy']['user']['username']
    . ' -u ' . $sUsername
    . ' -p ' . $sPassword
    . ' -r ' . $sRole
    . ' -s ' . $sStatus
    . ' -f ' . $sFirstName
    . ' -l ' . $sLastName
    . ' -e ' . $sEmailAddress
    . ' -g ' . $sOrganization
    . ' -d ' . $sDescription
    . $g_sContext;

  error_log( "===> command=" . $command );
  exec( $command, $output, $status );
  error_log( "===> output=" . print_r( $output, true ) );

  // Echo status
  $sStatus = $output[ count( $output ) - 1 ];
  echo $sStatus;
?>
