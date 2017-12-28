<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/postSecurity.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/context.php";

  // Log post array without password
  $sPasswordMask = '<PASSWORD>';
  $aPostMinusPassword = $_POST;
  $aPostMinusPassword['oldPassword'] = ( $aPostMinusPassword['oldPassword'] == '' ) ? '' : $sPasswordMask;
  $aPostMinusPassword['password'] = ( $aPostMinusPassword['password'] == '' ) ? '' : $sPasswordMask;
  error_log( "==> post=" . print_r( $aPostMinusPassword, true ) );

  // Get user attributes
  $sUsername = $_POST['username'];
  $sOldPassword = $_POST['oldPassword'];
  $sPassword = $_POST['password'];
  $sRole = $_POST['role'];
  $sAuthFacilities = quote( $_POST['auth_facilities'] );
  $sStatus = $_POST['status'];
  $sFirstName = quote( $_POST['first_name'] );
  $sLastName = quote( $_POST['last_name'] );
  $sEmailAddress = quote( $_POST['email_address'] );
  $sOrganization = quote( $_POST['organization'] );
  $sDescription = quote( $_POST['user_description'] );

  // Update user
  $command = quote( getenv( "PYTHON" ) ) . " ../database/updateUser.py 2>&1 -b " . $_SESSION['panelSpy']['user']['username']
    . ' -u ' . $sUsername
    . ( ( $sOldPassword == '' ) ? '' : ( ' -o ' . quote( $sOldPassword, false ) ) )
    . ( ( $sPassword == '' ) ? '' : ( ' -p ' . quote( $sPassword, false ) ) )
    . ' -r ' . $sRole
    . ' -a ' . $sAuthFacilities
    . ' -s ' . $sStatus
    . ' -f ' . $sFirstName
    . ' -l ' . $sLastName
    . ' -e ' . $sEmailAddress
    . ' -g ' . $sOrganization
    . ' -d ' . $sDescription
    . $g_sContext;

  // Log command without password
  $sCommandMinusPassword = preg_replace( '/ -o .* -p/', ' -o ' . $sPasswordMask . ' -p', $command, 1 );
  $sCommandMinusPassword = preg_replace( '/ -p .* -r/', ' -p ' . $sPasswordMask . ' -r', $sCommandMinusPassword, 1 );
  error_log( "==> command=" . $sCommandMinusPassword );

  // Execute command
  exec( $command, $output, $status );
  error_log( "==> output=" . print_r( $output, true ) );

  $sUser = $output[ count( $output ) - 1 ];
  $tUser = json_decode( $sUser );

  // If signed-in user is same as updated user, update fields returned by update operation
  if ( $sUsername == $_SESSION['panelSpy']['user']['username'] )
  {
    $aUser = (array) $tUser->user;
    foreach( $aUser as $sKey => $sVal )
    {
      $_SESSION['panelSpy']['user'][$sKey] = $sVal;
    }
  }
  else
  {
    // Signed-in user is not same as updated user.  Report status without changing other user fields.
    $aMessages = (array) $tUser->messages;
    $_SESSION['panelSpy']['user']['messages'] = $aMessages;
  }

  // Echo session data
  echo $sUser;
?>
