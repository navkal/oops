<!-- Copyright 2017 Panel Spy.  All rights reserved. -->
<?php
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  require_once $_SERVER["DOCUMENT_ROOT"] . "/session/session.php";
  $iVersion = time();
?>

<script src="util/util.js?version=<?=$iVersion?>"></script>
<script src="session/session.js?version=<?=$iVersion?>"></script>

<?php
  if ( signedIn() )
  {
    include "../common/main.php";
?>
    <link rel="stylesheet" href="util/navbar.css?version=<?=$iVersion?>">
    <script>
      $( document ).ready( makeSignOutButton );
    </script>
<?php
  }
  else
  {
    include $_SERVER["DOCUMENT_ROOT"] . "/session/challenge.php";
  }
?>
