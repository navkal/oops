<?php
  // Copyright 2017 Panel Spy.  All rights reserved.

  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";

  error_log( "====> post=" . print_r( $_POST, true ) );

  $sSignInId = $_POST['signInId'];


  error_log( '===> signedIn.php id=' . $sSignInId );

  $bSignedIn =
    (
      isset( $_SESSION['panelSpy']['session']['signInId'] )
      &&
      ( $sSignInId == $_SESSION['panelSpy']['session']['signInId'] )
    );

  error_log( '==> "' . $sSignInId . '" signed in? ' . ( $bSignedIn ? 'YES' : 'NO' ) );

  echo json_encode( $bSignedIn );
?>
