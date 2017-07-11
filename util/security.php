<?php
  if ( ! isset( $_SESSION['panelSpy']['user']['signInId'] ) )
  {
    exit( '<h2>Access denied</h2>' );
  }
?>
