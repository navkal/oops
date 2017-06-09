<?php
  // Copyright 2017 Panel Spy.  All rights reserved.

  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";

  function signIn( $sUsername, $sPassword )
  {
    // Initialize Panel Spy session storage
    $_SESSION['panelSpy'] = [];

    // Look up user in database
    $command = quote( getenv( "PYTHON" ) ) . " ../database/signIn.py 2>&1 -u " . $sUsername . ' -p ' . $sPassword;
    error_log( "===> command=" . $command );
    exec( $command, $output, $status );
    error_log( "===> output=" . print_r( $output, true ) );

    // Extract user from database output
    $sUser = $output[ count( $output ) - 1 ];
    error_log( '===> user=' . $sUser );
    $tUser = json_decode( $sUser );
    error_log( '===> user=' . print_r( $tUser, true ) );

    // If database assigned a signin id, load session state
    if ( $tUser->signInId )
    {
      $_SESSION['panelSpy']['session'] = [];
      $_SESSION['panelSpy']['session']['username'] = $sUsername;
      $_SESSION['panelSpy']['session']['role'] = $tUser->role;
      $_SESSION['panelSpy']['session']['signInId'] = $tUser->signInId;
    }

    error_log( '==> signIn() signedin=' . $tUser->signInId );
    return $tUser->signInId;
  }

  function signedIn( $sSignInId )
  {
    $bSignedIn =
      (
        isset( $_SESSION['panelSpy']['session'] )
        &&
        ( $sSignInId != '' )
        &&
        (
          ( $sSignInId == 'initial' )
          ||
          ( $sSignInId == $_SESSION['panelSpy']['session']['signInId'] )
        )
      );

    error_log( '===> signedIn()' );
    if ( $bSignedIn )
    {
      error_log( 'YES' );
      // Determine which menu the user will see
      $sSuffix = ( $_SESSION['panelSpy']['session']['role'] == 'administrator' ) ? 'Admin' : '';

      global $g_sNavbarCsv;
      $g_sNavbarCsv = $_SERVER['DOCUMENT_ROOT'] . '/navbar' . $sSuffix . '.csv';
    }
    else error_log( 'NO' );

    error_log( '==> signedIn() signedin=' . $bSignedIn );

    return ( $bSignedIn );
  }

  function signOut()
  {
    // Clear Panel Spy session storage
    $_SESSION['panelSpy'] = [];
  }
?>
