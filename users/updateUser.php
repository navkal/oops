<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  error_log( "====> post=" . print_r( $_POST, true ) );

  // Get user attributes
  $sUsername = $_POST['username'];
  $sPassword = $_POST['password'];
  $sRole = $_POST['role'];
  $sStatus = $_POST['status'];
  $sFirstName = quote( $_POST['first_name'] );
  $sLastName = quote( $_POST['last_name'] );
  $sEmailAddress = quote( $_POST['email_address'] );
  $sOrganization = quote( $_POST['organization'] );
  $sDescription = quote( $_POST['user_description'] );

  // Update user
  $command = quote( getenv( "PYTHON" ) ) . " ../database/updateUser.py 2>&1 -b " . $_SESSION['panelSpy']['user']['username']
    . ' -u ' . $sUsername
    . ( ( $sPassword == '' ) ? '' : ( ' -p ' . $sPassword ) )
    . ' -r ' . $sRole
    . ' -s ' . $sStatus
    . ' -f ' . $sFirstName
    . ' -l ' . $sLastName
    . ' -e ' . $sEmailAddress
    . ' -o ' . $sOrganization
    . ' -d ' . $sDescription;

  error_log( "===> command=" . $command );
  exec( $command, $output, $status );
  error_log( "===> output=" . print_r( $output, true ) );

  // If signed-in user is same as updated user, update specific fields in session storage
  if ( $sUsername == $_SESSION['panelSpy']['user']['username'] )
  {
    $_SESSION['panelSpy']['user']['user_description'] = $sDescription;
    $_SESSION['panelSpy']['user']['role'] = $sRole;
    $_SESSION['panelSpy']['user']['status'] = $sStatus;
    $_SESSION['panelSpy']['user']['first_name'] = $sFirstName;
    $_SESSION['panelSpy']['user']['last_name'] = $sLastName;
    $_SESSION['panelSpy']['user']['email_address'] = $sEmailAddress;
    $_SESSION['panelSpy']['user']['organization'] = $sOrganization;
    $_SESSION['panelSpy']['user']['user_description'] = $sDescription;
  }

  // Echo user
  echo json_encode( $_SESSION['panelSpy']['user'] );
?>
