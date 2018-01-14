<!-- Copyright 2018 Panel Spy.  All rights reserved. -->

<!DOCTYPE html>
<html>

  <!-- Head -->
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/head.php";
    require_once $_SERVER["DOCUMENT_ROOT"]."/util/abort.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/util/defineDemo.php";

    if ( ! isset( $_SESSION['panelSpy']['context']['enterprise'], $_SESSION['panelSpy']['context']['facility'] ) || ( $_SESSION['panelSpy']['context']['enterprise'] != PANEL_SPY_DEMO ) || ( $_SESSION['panelSpy']['context']['facility'] != PANEL_SPY_DEMO ) )
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
      if ( ( typeof showMain === 'function' ) && ( typeof submitCredentialsFailed === 'function' ) )
      {
        var sUsername = '<?=PANEL_SPY_DEMO?>';
        var sPassword = '<?=PANEL_SPY_DEMO?>';

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
    }
  </script>
</html>
