<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  require_once $_SERVER["DOCUMENT_ROOT"] . "/session/session.php";

  $iVersion = time();

  if ( signedIn() )
  {
    include "../common/main.php";
?>
    <link rel="stylesheet" href="util/navbar.css?version=<?=$iVersion?>">

<?php
  }
  else
  {
    include $_SERVER["DOCUMENT_ROOT"] . "/session/challenge.php";
  }
?>

<!-- Panel Spy global utility script -->
<script src="util/util.js?version=<?=$iVersion?>"></script>
