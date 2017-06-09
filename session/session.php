<?php
  // Copyright 2017 Panel Spy.  All rights reserved.

  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";

  function signIn( $sUsername, $sPassword )
  {
    $sSignInId = false;

    if ( $bSignedIn = ( strlen( $sUsername ) <= 5 ) )
    {
      $sSignInId = uniqid();

      // Initialize Panel Spy session storage
      $_SESSION['panelSpy'] = [];

      // Initialize session state
      $_SESSION['panelSpy']['session'] = [];
      $_SESSION['panelSpy']['session']['signInId'] = $sSignInId;
      $_SESSION['panelSpy']['session']['username'] = $sUsername;

      switch( strtolower( $sUsername ) )
      {
        case 'admin':
          $_SESSION['panelSpy']['session']['role'] = 'admin';
          break;

        case 'tech':
          $_SESSION['panelSpy']['session']['role'] = 'technician';
          break;

        default:
          $_SESSION['panelSpy']['session']['role'] = 'visitor';
          break;
      }

      error_log( '==> signIn() role=' . $_SESSION['panelSpy']['session']['role'] );
    }

    error_log( '==> signIn() signedin=' . $sSignInId );
    return $sSignInId;
  }

  function signedIn( $sSignInId = '' )
  {
    if ( $bSignedIn = isset( $_SESSION['panelSpy']['session'] ) && ( ( $sSignInId == '' ) || ( $sSignInId == $_SESSION['panelSpy']['session']['signInId'] ) ) )
    {
      // Determine which menu the user will see
      $sSuffix = ( $_SESSION['panelSpy']['session']['role'] == 'admin' ) ? 'Admin' : '';

      global $g_sNavbarCsv;
      $g_sNavbarCsv = $_SERVER['DOCUMENT_ROOT'] . '/navbar' . $sSuffix . '.csv';
    }

    error_log( '==> signedIn() signedin=' . $bSignedIn );

    return ( $bSignedIn );
  }

  function signOut()
  {
    // Clear Panel Spy session storage
    $_SESSION['panelSpy'] = [];
  }
?>
