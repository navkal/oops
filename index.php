<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  $iVersion = time();

  $bAuth = isset( $_SESSION['panelSpy']['session']['signInId'] );
  if ( $bAuth )
  {
    // Determine which menu the user will see
    global $g_sNavbarCsv;
    $sSuffix = ( $_SESSION['panelSpy']['session']['role'] == 'administrator' ) ? 'Admin' : '';
    $g_sNavbarCsv = $_SERVER['DOCUMENT_ROOT'] . '/navbar' . $sSuffix . '.csv';

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
        
        var bChangePassword = JSON.parse( localStorage.getItem( 'signedInUser' ) )['changePassword'];
        if ( bChangePassword ) alert( 'YOU MUST CHANGE YOUR PASSWORD!!!' );
      }
    </script>

    <script src="session/keepAlive.js?version=<?=$iVersion?>"></script>
    <link rel="stylesheet" href="util/navbar<?=$sSuffix?>.css?version=<?=$iVersion?>">
<?php
  }
  else
  {
    // Show sign-in prompt
    include $_SERVER["DOCUMENT_ROOT"] . "/session/challenge.php";
  }
?>

<script src="util/util.js?version=<?=$iVersion?>"></script>
<script src="session/session.js?version=<?=$iVersion?>"></script>
