<?php
  // Copyright 2017 Panel Spy.  All rights reserved.

  function signIn( $sUsername, $sPassword )
  {
    if ( $bSignedIn = ( (time()%2)==0 ) )
    {
      // Initialize Panel Spy session storage
      $_SESSION['panelSpy'] = [];

      // Initialize session state
      $_SESSION['panelSpy']['session'] = [];
      $_SESSION['panelSpy']['session']['username'] = $sUsername;
      switch(time()%3)
      {
        case 0:
          $_SESSION['panelSpy']['session']['role'] = 'admin';
          break;
        case 1:
          $_SESSION['panelSpy']['session']['role'] = 'technician';
          break;
        case 2:
          $_SESSION['panelSpy']['session']['role'] = 'visitor';
          break;
      }

      error_log( '==> signIn() role=' . $_SESSION['panelSpy']['session']['role'] );
    }

    error_log( '==> signIn() signedin=' . $bSignedIn );
    return $bSignedIn;
  }

  function signedIn()
  {
    if ( $bSignedIn = isset( $_SESSION['panelSpy']['session'] ) )
    {
      // Determine which menu the user will see
      $sNavbarFragement = ( $_SESSION['panelSpy']['session']['role'] == 'admin' ) ? 'Admin' : '';

      global $g_sNavbarCsv;
      $g_sNavbarCsv = $_SERVER['DOCUMENT_ROOT'] . '/navbar' . $sNavbarFragement . '.csv';
    }

    error_log( '==> signedIn() signedin=' . $bSignedIn );

    return ( $bSignedIn );
  }

  function signOut()
  {
    // Clear Panel Spy session storage
    $_SESSION['panelSpy'] = [];

    // ========> SIGN OUT HERE <====================
  }
?>
