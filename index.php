<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"] . "/session/session.php";

  if ( signedIn() )
  {
    // Show application
    include "../common/main.php";
?>
    <script>
      // Append signout button to navbar
      $( document ).ready( makeSignOutButton );
      function makeSignOutButton()
      {
        var sSignOutHtml = '';
        sSignOutHtml += '<form class="navbar-form navbar-right">';
        sSignOutHtml += '<button type="button" class="btn btn-default" onclick="signOut();" >Sign Out</button>';
        sSignOutHtml += '</form>';
        $( '#navbar-collapse' ).append( sSignOutHtml );
      }
    </script>
<?php
  }
  else
  {
    // Show sign-in prompt
    include $_SERVER["DOCUMENT_ROOT"] . "/session/challenge.php";
  }

  $iVersion = time();
?>

<script src="util/util.js?version=<?=$iVersion?>"></script>
<script src="session/session.js?version=<?=$iVersion?>"></script>
<link rel="stylesheet" href="util/navbar.css?version=<?=$iVersion?>">
