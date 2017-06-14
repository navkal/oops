<?php
  // Copyright 2017 Panel Spy.  All rights reserved.

  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";

  $sSignInId = $_POST['signInId'];

  $bSignedIn =
    (
      isset( $_SESSION['panelSpy']['user']['signInId'] )
      &&
      ( $sSignInId == $_SESSION['panelSpy']['user']['signInId'] )
    );

  echo json_encode( $bSignedIn );
?>
