<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<!DOCTYPE html>
<html>

  <!-- Head -->
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/head.php";
    require_once $_SERVER["DOCUMENT_ROOT"]."/util/abort.php";
    if ( ! isset( $_SESSION['panelSpy']['context']['enterprise'] ) )
    {
      abort();
    }
  ?>

  <!-- Body -->
  <body>
    <div class="container">
      <div class="page-header">
        <img src="brand.ico" class="img-responsive" alt="Panel Spy" style="width:50%; max-width:250px; margin:auto">
      </div>
      <div class="row">
        <div class="col-sm-6 col-sm-offset-3">
          <form onsubmit="submitCredentials(); return false;" >
            <div style="text-align:center;" >
              <input type="text" id="username" class="form-control" placeholder="Username" required autofocus >
              <input type="password" id="password" class="form-control" placeholder="Password" required >
              <button type="submit" class="btn btn-primary btn-block" >Sign In</button>
            </div>
          </form>
          <br/>
          <div id="signInFailed" class="alert alert-danger" style="text-align:center; display:none;" role="alert">
            Username or Password is incorrect.
          </div>
        </div>
      </div>
    </div>

    <script>
      document.title = "Sign In - Panel Spy";
    </script>

  </body>

  <script>
    $( document ).ready( showSignInFailed );

    function showSignInFailed()
    {
      var tSession = JSON.parse( localStorage.getItem( 'panelSpy.session' ) );
      if ( tSession.signInFailed )
      {
        delete tSession.signInFailed;
        localStorage.setItem( 'panelSpy.session', JSON.stringify( tSession ) );
        $( '#signInFailed' ).show();
      }
    }
  </script>

</html>
