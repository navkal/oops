<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  $iVersion = time();

  if ( isset( $_SESSION['panelSpy']['session']['changePassword'] ) && $_SESSION['panelSpy']['session']['changePassword'] )
  {
    // Force user to change password
    include $_SERVER["DOCUMENT_ROOT"] . "/session/passwordPrompt.php";
  }
  else if ( isset( $_SESSION['panelSpy']['session']['signInId'] ) )
  {
    // Determine which menu the user will see
    $sSuffix = '';
    switch( $_SESSION['panelSpy']['session']['role'] )
    {
      case 'administrator':
        $sSuffix = 'Admin';
        break;
      case 'technician':
        $sSuffix = 'Tech';
        break;
      case 'user':
      default:
        $sSuffix = '';
        break;
    }

    global $g_sNavbarCsv;
    $g_sNavbarCsv = $_SERVER['DOCUMENT_ROOT'] . '/navbar' . $sSuffix . '.csv';

    // Show application
    include "../common/main.php";
?>
    <script>
      // Append signout button to navbar
      $( document ).ready( makeSignOutButton );
      function makeSignOutButton()
      {
        var sUsername = JSON.parse( localStorage.getItem( 'signedInUser' ) )['username'];
        var sSignedInAs = "Signed in as '" + sUsername + "'";
        var sSignOutHtml = '';
        sSignOutHtml += '<form class="navbar-form navbar-right">';
        sSignOutHtml += '<button type="button" class="btn btn-default" title="' + sSignedInAs + '" onclick="signOut();" >Sign Out</button>';
        sSignOutHtml += '</form>';
        $( '#navbar-collapse' ).append( sSignOutHtml );
        $( '.navbar-brand' ).attr( 'title', sSignedInAs );
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
