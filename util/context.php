<?php
  // Copyright 2018 Panel Spy.  All rights reserved.

  require_once $_SERVER["DOCUMENT_ROOT"]."/util/abort.php";
  if ( ! isset( $_SESSION['panelSpy']['context']['enterprise'] ) )
  {
    abort();
  }

  $g_sContext = ' -y ' . quote( $_SESSION['panelSpy']['context']['enterprise'] );
  if ( isset( $_SESSION['panelSpy']['context']['facility'] ) )
  {
    $g_sContext .= ' -z ' . quote( $_SESSION['panelSpy']['context']['facility'] );
  }
?>
