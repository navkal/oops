<?php
  if ( session_status() != PHP_SESSION_ACTIVE )
  {
    exit( '<h2>Access denied</h2>' );
  }
?>
