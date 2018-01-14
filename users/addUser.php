<?php
  // Copyright 2018 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/postSecurity.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/context.php";

  // Log post array without password
  $sPasswordMask = '<PASSWORD>';
  $aPostMinusPassword = $_POST;
  $aPostMinusPassword['password'] = $sPasswordMask;
  error_log( "==> post=" . print_r( $aPostMinusPassword, true ) );

  // Get user attributes
  $sUsername = $_POST['username'];
  $sPassword = quote( $_POST['password'], false );
  $sRole = $_POST['role'];
  $sAuthFacilities = quote( $_POST['auth_facilities'] );
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
    . ' -a ' . $sAuthFacilities
    . ' -s ' . $sStatus
    . ' -f ' . $sFirstName
    . ' -l ' . $sLastName
    . ' -e ' . $sEmailAddress
    . ' -g ' . $sOrganization
    . ' -d ' . $sDescription
    . $g_sContext;

  error_log( "==> command=" . preg_replace( '/ -p .* -r/', ' -p ' . $sPasswordMask . ' -r', $command, 1 ) );
  exec( $command, $output, $status );
  error_log( "==> output=" . print_r( $output, true ) );

  // Echo status
  $sStatus = $output[ count( $output ) - 1 ];
  echo $sStatus;
?>
