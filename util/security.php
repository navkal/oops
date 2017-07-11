<?php
  function abort()
  {
    exit( '<h2>Access denied</h2>' );
  }
  
  if ( ! isset( $_SESSION['panelSpy']['user']['signInId'] ) )
  {
    abort();
  }
?>
