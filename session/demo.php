<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<!DOCTYPE html>
<html>

  <!-- Head -->
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/head.php";
    require_once $_SERVER["DOCUMENT_ROOT"]."/util/abort.php";

    if ( ! isset( $_SESSION['panelSpy']['context']['enterprise'], $_SESSION['panelSpy']['context']['facility'] ) || (  $_SESSION['panelSpy']['context']['enterprise'] != 'demo' ) || (  $_SESSION['panelSpy']['context']['facility'] != 'demo' ) )
    {
      abort();
    }
  ?>

  <!-- Body -->
  <body>
  </body>

  <script>
    $( document ).ready( signInDemo );

    function signInDemo()
    {
      var sUsername = 'demo';
      var sPassword = 'demo';

      // Post request to server
      var tPostData = new FormData();
      tPostData.append( "username", sUsername );
      tPostData.append( "password", sPassword );

      if ( typeof showMain === 'function' )
      {
        $.ajax(
          "session/signIn.php",
          {
            type: 'POST',
            processData: false,
            contentType: false,
            dataType : 'json',
            data: tPostData
          }
        )
        .done( showMain )
        .fail( submitCredentialsFailed );
      }
      else
      {
        $( 'body' ).html( '<h2>Access denied</h2>' );
      }
    }
  </script>
</html>
