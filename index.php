<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/session.php";

  if ( ! signedIn() && signIn() )
  {
    include "../common/main.php";
    $iVersion = time();
?>

    <!-- Panel Spy global utility script -->
    <script src="util/util.js?version=<?=$iVersion?>"></script>

    <!-- Panel Spy navbar styling -->
    <link rel="stylesheet" href="util/navbar.css?version=<?=$iVersion?>">

<?php
  }
  else
  {
    include "signin.php";
  }
?>
