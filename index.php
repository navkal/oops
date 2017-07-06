<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  error_log( '====> index.php rq=' . print_r( $_REQUEST, true ) );

  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";
  $iVersion = time();
  $sRecoverAdminTrigger = $_SERVER["DOCUMENT_ROOT"] . "/recoverAdmin.trg";

  //
  // Determine context
  //
  $aUrlContext = [];
  if ( isset( $_REQUEST['e'] ) )
  {
    $aUrlContext['enterprise'] = strtolower( $_REQUEST['e'] );
  }

  // If URL explicitly changes enterprise, clear user and context
  error_log( '===> before unset decision, aUrlContext=' . print_r( $aUrlContext, true ) );
  if ( isset( $aUrlContext['enterprise'] ) && isset( $_SESSION['panelSpy']['context']['enterprise'] ) && ( $aUrlContext['enterprise'] != $_SESSION['panelSpy']['context']['enterprise'] ) )
  {
    $_SESSION['panelSpy']['user'] = [];
    unset( $_SESSION['panelSpy']['context'] );

    // If user has explicitly requested the demo, clear the context
    if ( $aUrlContext['enterprise'] == 'demo' )
    {
      $aUrlContext = [];
    }
  }


  ///////////////////////////////////
  // Decide how to render the view //
  ///////////////////////////////////

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
    if ( ! isset( $_SESSION['panelSpy']['context']['facility'] ) && ( $_SESSION['panelSpy']['user']['role'] != 'Administrator' ) )
    {
      include $_SERVER["DOCUMENT_ROOT"] . "/session/facilityPrompt.php";
    }
    else
    {
      // Determine which menu the user will see
      $sSuffix = '';
      if ( $_SESSION['panelSpy']['context']['enterprise'] == 'demo' )
      {
        $sSuffix = 'Demo';
      }
      else
      {
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
      }

      global $g_sNavbarCsv;
      $g_sNavbarCsv = $_SERVER['DOCUMENT_ROOT'] . '/navbar' . $sSuffix . '.csv';

      error_log( '========> Showing main with context=' . print_r( $_SESSION['panelSpy']['context'], true ) );

      // Format informational tooltips
      $sBrandTitle =
        isset( $_SESSION['panelSpy']['context']['facilityDescr'] )
        ?
          ( 'Facility: ' . $_SESSION['panelSpy']['context']['facilityDescr'] )
        :
          ( 'Enterprise: ' . $_SESSION['panelSpy']['context']['enterprise'] );

      $sSignoutTitle = 'Username: ' . $_SESSION['panelSpy']['user']['username'];

      // Show application
      include "../common/main.php";
?>
      <script>
        // Append signout button to navbar
        $( document ).ready( makeSignOutButton );
        function makeSignOutButton()
        {
          $( '.navbar-brand' ).attr( 'title', '<?=$sBrandTitle?>' );
<?php
      if ( $_SESSION['panelSpy']['context']['enterprise'] != 'demo' )
      {
?>
          var sSignOutHtml = '';
          sSignOutHtml += '<form class="navbar-form navbar-right">';
          sSignOutHtml += '<button type="button" class="btn btn-default" title="<?=$sSignoutTitle?>" onclick="signOut();" >Sign Out</button>';
          sSignOutHtml += '</form>';
          $( '#navbar-collapse' ).append( sSignOutHtml );
<?php
      }
?>
        }
      </script>

      <script src="session/keepAlive.js?version=<?=$iVersion?>"></script>
      <link rel="stylesheet" href="util/navbar<?=$sSuffix?>.css?version=<?=$iVersion?>">
<?php
    }
  }
  else
  {
    // Determine whether admin recovery trigger is present
    $bRecoverAdminTrigger = file_exists( $sRecoverAdminTrigger );
    @unlink( $sRecoverAdminTrigger );

    // Recover admin password if we found the trigger and we have an Enterprise
    if ( $bRecoverAdminTrigger && isset( $aUrlContext['enterprise'] ) )
    {
      $_SESSION['panelSpy']['recoverAdminContext'] = $aUrlContext;

      error_log( '---------> about to recover, recovery context=' . print_r( $_SESSION['panelSpy']['recoverAdminContext'], true ) );
      include $_SERVER["DOCUMENT_ROOT"] . "/util/recoverAdmin.php";
    }
    else
    {
      error_log( '---------> about to sign in, recovery context set? ' . isset( $_SESSION['panelSpy']['recoverAdminContext'] ) );
      // Determine context
      if ( isset( $_SESSION['panelSpy']['recoverAdminContext'] ) )
      {
        error_log( '---------> about to sign in, recovery context=' . print_r( $_SESSION['panelSpy']['recoverAdminContext'], true ) );
        // Adopt data context from admin recovery operation
        $_SESSION['panelSpy']['context'] = $_SESSION['panelSpy']['recoverAdminContext'];
        unset( $_SESSION['panelSpy']['recoverAdminContext'] );
      }
      else
      {
        // If context doesn't already exist, set it using URL
        if ( ! isset( $_SESSION['panelSpy']['context'] ) )
        {
          // Use new data context from URL
          $_SESSION['panelSpy']['context'] = $aUrlContext;
        }
      }

      if ( ( $_SESSION['panelSpy']['context'] != [] ) && ( $_SESSION['panelSpy']['context']['enterprise'] != 'demo' ) )
      {
        // Show sign-in prompt
        unset( $_SESSION['panelSpy']['context']['facilityDescr'] );
        unset( $_SESSION['panelSpy']['context']['facility'] );
        include $_SERVER["DOCUMENT_ROOT"] . "/session/signInPrompt.php";
      }
      else
      {
        // Load demo
        $_SESSION['panelSpy']['context'] = [ 'enterprise' => 'demo', 'facilityDescr' => 'Panel Spy Demo', 'facility' => 'demo' ];
        include $_SERVER["DOCUMENT_ROOT"] . "/session/demo.php";
      }
    }
  }
?>

<script src="util/util.js?version=<?=$iVersion?>"></script>
<script src="session/session.js?version=<?=$iVersion?>"></script>
