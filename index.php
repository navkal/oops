<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  error_log( '====> index.php rq=' . print_r( $_REQUEST, true ) );

  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  $iVersion = time();
  $sRecoverAdminTrigger = $_SERVER["DOCUMENT_ROOT"] . "/recoverAdmin.trg";

  if ( isset( $_SESSION['panelSpy']['user']['forceChangePassword'] ) && $_SESSION['panelSpy']['user']['forceChangePassword'] )
  {
    // Force user to change password
    include $_SERVER["DOCUMENT_ROOT"] . "/session/passwordPrompt.php";
?>
    <script src="session/keepAlive.js?version=<?=$iVersion?>"></script>
<?php
  }
  else if ( isset( $_SESSION['panelSpy']['user']['signInId'] ) )
  {
    // Determine which menu the user will see
    $sSuffix = '';
    switch( $_SESSION['panelSpy']['user']['role'] )
    {
      case 'Administrator':
        $sSuffix = 'Admin';
        break;
      case 'Technician':
        $sSuffix = 'Tech';
        break;
      case 'Visitor':
      default:
        $sSuffix = '';
        break;
    }

    global $g_sNavbarCsv;
    $g_sNavbarCsv = $_SERVER['DOCUMENT_ROOT'] . '/navbar' . $sSuffix . '.csv';

    error_log( '========> Showing main with context=' . print_r( $_SESSION['panelSpy']['context'], true ) );

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
  else if ( file_exists( $sRecoverAdminTrigger ) )
  {
    $_SESSION['panelSpy']['recoverAdminContext'] = makeContext();
    error_log( '---------> about to recover, recovery context=' . print_r( $_SESSION['panelSpy']['recoverAdminContext'], true ) );

    unlink( $sRecoverAdminTrigger );
    include $_SERVER["DOCUMENT_ROOT"] . "/util/recoverAdmin.php";
  }
  else
  {
    error_log( '---------> about to sign in, recovery context set? ' . isset( $_SESSION['panelSpy']['recoverAdminContext'] ) );
    if ( isset( $_SESSION['panelSpy']['recoverAdminContext'] ) )
    {
      error_log( '---------> about to sign in, recovery context=' . print_r( $_SESSION['panelSpy']['recoverAdminContext'], true ) );
      // Adopt data context from admin recovery operation
      $_SESSION['panelSpy']['context'] = $_SESSION['panelSpy']['recoverAdminContext'];
      unset( $_SESSION['panelSpy']['recoverAdminContext'] );
    }
    else
    {
      // Make new data context
      $_SESSION['panelSpy']['context'] = makeContext();
    }

    // Show sign-in prompt
    include $_SERVER["DOCUMENT_ROOT"] . "/session/signInPrompt.php";
  }

  // Initialize data context
  function makeContext()
  {
    $aContext = [];

    if ( isset( $_REQUEST['e'] ) && isset( $_REQUEST['f'] ) )
    {
      $aContext['enterprise'] = $_REQUEST['e'];
      $aContext['facility'] = $_REQUEST['f'];
    }
    else
    {
      $aContext['enterprise'] = 'default';
      $aContext['facility'] = 'default';
    }

    return $aContext;
  }
?>

<script src="util/util.js?version=<?=$iVersion?>"></script>
<script src="session/session.js?version=<?=$iVersion?>"></script>
