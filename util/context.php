<?php
  // Copyright 2017 Panel Spy.  All rights reserved.

  $g_sContext = ' -y ' . quote( $_SESSION['panelSpy']['context']['enterprise'] );
  if ( isset( $_SESSION['panelSpy']['context']['facility'] ) )
  {
    $g_sContext .= ' -z ' . quote( $_SESSION['panelSpy']['context']['facility'] );
  }
?>
