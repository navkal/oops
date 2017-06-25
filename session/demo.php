<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<!DOCTYPE html>
<html>

  <!-- Head -->
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/head.php";
    $_SESSION['panelSpy']['context'] = [ 'enterprise' => 'demo', 'facility' => 'demo' ];
    error_log( "===========> In demo.php" );
  ?>

  <!-- Body -->
  <body>
    <script>
      $( document ).ready( signInDemo );

      function signInDemo()
      {
        console.log( 'about to sign in' );
        var sUsername = 'test';
        var sPassword = 'test';

        // Post request to server
        var tPostData = new FormData();
        tPostData.append( "username", sUsername );
        tPostData.append( "password", sPassword );

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
    </script>
  </body>
</html>
