<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<!DOCTYPE html>
<html>

  <!-- Head -->
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/head.php";
    require_once $_SERVER["DOCUMENT_ROOT"]."/util/abort.php";
    if ( ! ( isset( $_SESSION['panelSpy']['user']['forceChangePassword'] ) && $_SESSION['panelSpy']['user']['forceChangePassword'] ) )
    {
      abort();
    }
    require_once $_SERVER["DOCUMENT_ROOT"] . "/users/passwordRules.php";
  ?>

  <!-- Body -->
  <body>
    <div class="container">
      <div class="page-header">
        <h3>Change Password</h3>
      </div>
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <form onsubmit="handleClick(event); return false;" >
            <div class="form-group">
              <label for="username">Username</label>
              <input type="text" class="form-control" id="username" value="<?=$_SESSION['panelSpy']['user']['username']?>" disabled >
            </div>
            <div class="form-group">
              <label for="oldPassword">Old Password</label>
              <input type="password" maxlength="<?=MAX_PASSWORD_LENGTH+1?>" class="form-control" id="oldPassword" placeholder="Old Password" autofocus required >
            </div>
            <div class="form-group">
              <label for="password">New Password</label>
              <input type="password" maxlength="<?=MAX_PASSWORD_LENGTH+1?>" class="form-control" id="password" placeholder="New Password" >
            </div>
            <div class="form-group">
              <label for="confirm" >Confirm New Password</label>
              <input type="password" maxlength="<?=MAX_PASSWORD_LENGTH+1?>" class="form-control" id="confirm" placeholder="Confirm New Password" >
            </div>
            <div style="text-align:center;" >
              <button id="change" type="submit" onclick="g_sAction='change'" class="btn btn-primary" >Change Password</button>
              <button id="cancel" type="submit" onclick="g_sAction='cancel'" class="btn btn-default" >Cancel</button>
            </div>
          </form>
        </div>
      </div>

      <br/>

      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <div id="messages" class="alert alert-danger" style="display:none" role="alert">
            <ul id="messageList">
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="passwordErrorDialog" tabindex="-1" role="dialog" aria-labelledby="passwordErrorLabel">
      <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title text-danger" id="passwordErrorLabel">Password not Changed</h4>
          </div>
          <div class="modal-body bg-danger">
            <div class="row">
              <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <p class="text-danger" ><b>Old Password not valid.</b></p>
                <p class="text-danger">Please sign in again.</p>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <form submit="return false;">
              <input type="text" id="dummyInput" style="width:0px; height:0px; opacity:0%; border:none">
              <button type="submit" class="btn btn-primary" data-dismiss="modal" >OK</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <script>
      document.title = "Change Password - Panel Spy";
    </script>

  </body>
</html>


<script>
  $( '#passwordErrorDialog' ).on( 'shown.bs.modal', onShownPasswordErrorDialog );
  $( '#passwordErrorDialog' ).on( 'hide.bs.modal', onOkPasswordErrorDialog );

  function handleClick( tEvent )
  {
    switch ( g_sAction )
    {
      case 'change':
        if ( validateInput() )
        {
          changePassword();
        }
        break;

      case 'cancel':
      default:
        signOut();
        break;
    }
  }

  function validateInput()
  {
    clearMessages();
    var aMessages = validatePassword();
    showMessages( aMessages );
    return ( aMessages.length == 0 );
  }

  function changePassword()
  {
    // Post request to server
    var tPostData = new FormData();
    tPostData.append( "username", $( '#username' ).val() );
    tPostData.append( "oldPassword", $( '#oldPassword' ).val() );
    tPostData.append( "password", $( '#password' ).val() );

    $.ajax(
      "session/changePassword.php",
      {
        type: 'POST',
        processData: false,
        contentType: false,
        dataType : 'json',
        data: tPostData
      }
    )
    .done( handleChangePasswordRsp )
    .fail( handleAjaxError );
  }

  function handleChangePasswordRsp( tRsp, sStatus, tJqXhr )
  {
    if ( tRsp.signInId )
    {
      // Success: Show main page under new sign-in
      var tSession = JSON.parse( localStorage.getItem( 'panelSpy.session' ) );
      tSession.user = tRsp;
      showMain( tSession, sStatus, tJqXhr );
    }
    else
    {
      // Failure: Report error and sign out
      $( '#passwordErrorDialog' ).modal( { backdrop:'static' } )
    }
  }

  function onShownPasswordErrorDialog()
  {
    $( '#dummyInput' ).focus();
  }

  function onOkPasswordErrorDialog()
  {
    signOut();
  }
</script>
