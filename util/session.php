<?php
  // Copyright 2017 Panel Spy.  All rights reserved.

  function signIn()
  {
    // Determine whether the user is signed in
    global $g_bSignedIn;

    if ( $g_bSignedIn = ( (time()%2)==0 ) )
    {
      // Determine which menu the user will see
      global $g_sNavbarCsv;
      $g_sNavbarCsv = $_SERVER['DOCUMENT_ROOT'].'/navbar'.((time()%3)?'Admin':'').'.csv';

      // Initialize Panel Spy session storage
      $_SESSION['panelSpy'] = [];
    }

    return $g_bSignedIn;
  }

  function signedIn()
  {
    global $g_bSignedIn;
    return $g_bSignedIn;
  }

  function signOut()
  {
    // Clear Panel Spy session storage
    $_SESSION['panelSpy'] = [];

    $g_bSignedIn = false;
    return $g_bSignedIn;
  }
?>
