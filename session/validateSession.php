<?php
  // Copyright 2017 Panel Spy.  All rights reserved.

  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";

  $sSignInId = $_POST['signInId'];
  $sEnterprise = $_POST['enterprise'];
  $sFacility = $_POST['facility'];

  // Check sign-in ID
  $bValid =
    (
      isset( $_SESSION['panelSpy']['user']['signInId'] )
      &&
      ( $sSignInId == $_SESSION['panelSpy']['user']['signInId'] )
    );

  // Check enterprise
  if ( $bValid && isset( $_SESSION['panelSpy']['context']['enterprise'] ) )
  {
    $bValid = ( $sEnterprise == $_SESSION['panelSpy']['context']['enterprise'] );
  }

  // Check facility
  if ( $bValid && isset( $_SESSION['panelSpy']['context']['facility'] ) )
  {
    $bValid = ( $sFacility == $_SESSION['panelSpy']['context']['facility'] );
  }

  echo json_encode( $bValid );
?>
