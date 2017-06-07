<?php
  // Copyright 2017 Panel Spy.  All rights reserved.

  function signIn()
  {
    if ( $bSignedIn = ( (time()%2)==0 ) )
    {
      // Initialize Panel Spy session storage
      $_SESSION['panelSpy'] = [];
    }

    return $bSignedIn;
  }

  function signedIn()
  {
    // Determine which menu the user will see
    global $g_sNavbarCsv;
    $g_sNavbarCsv = $_SERVER['DOCUMENT_ROOT'].'/navbar'.((time()%3==0)?'Admin':'').'.csv';

    return ( (time()%2)==0 );
  }

  function signOut()
  {
    // Clear Panel Spy session storage
    $_SESSION['panelSpy'] = [];

    // ========> SIGN OUT HERE <====================
  }
?>
