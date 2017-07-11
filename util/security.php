<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/abort.php";

  if ( ! isset( $_SESSION['panelSpy']['user']['signInId'] ) )
  {
    abort();
  }
?>
