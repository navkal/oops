<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<!DOCTYPE html>
<html>

  <!-- Head -->
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/head.php";
  ?>

  <!-- Body -->
  <body>
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
