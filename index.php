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
    <script>
      $( document ).ready( init );
      function init()
      {
        var sSignOutHtml = '';
        sSignOutHtml += '<form class="navbar-form navbar-right">';
        sSignOutHtml += '<button type="button" class="btn btn-default" onclick="signOut();" >Sign Out</button>';
        sSignOutHtml += '</form>';
        $( '#navbar-collapse' ).append( sSignOutHtml );
      }

      function signOut()
      {
        // Post request to server
        var tPostData = new FormData();

        $.ajax(
          "session/signOut.php",
          {
            type: 'POST',
            processData: false,
            contentType: false,
            dataType : 'json',
            data: tPostData
          }
        )
        .done( showMain )
        .fail( handleAjaxError );

      }

      function showMain()
      {
        location.assign( '/' );
      }
    </script>

<?php
  }
  else
  {
    include $_SERVER["DOCUMENT_ROOT"] . "/session/challenge.php";
  }
?>

<!-- Panel Spy global utility script -->
<script src="util/util.js?version=<?=$iVersion?>"></script>
