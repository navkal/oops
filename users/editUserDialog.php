<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"] . "/session/passwordRules.php";
  define( 'MAX_USERNAME_LENGTH', 20 );
  define( 'MIN_USERNAME_LENGTH', 6 );
?>

<!-- Edit User dialog -->
<div class="modal fade" id="editUserDialog" tabindex="-1" role="dialog" aria-labelledby="editUserLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><span class="glyphicon glyphicon-remove"></span></span></button>
        <h4 class="modal-title" id="editUserLabel"></h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <form onsubmit="handleClick(event); return false;" >
              <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" maxlength="<?=MAX_USERNAME_LENGTH+1?>" placeholder="Username" >
              </div>
              <div class="form-group">
                <label for="password">Password</label>
                <input type="password" maxlength="<?=MAX_PASSWORD_LENGTH+1?>" class="form-control" id="password" placeholder="Password" >
              </div>
              <div class="form-group">
                <label for="confirm" >Confirm</label>
                <input type="password" maxlength="<?=MAX_PASSWORD_LENGTH+1?>" class="form-control" id="confirm" placeholder="Confirm Password" >
              </div>
              <div class="form-group">
                <label for="role" >Role</label>
                <select id="role" class="form-control">
                  <option value="visitor">Visitor</option>
                  <option value="technician">Technician</option>
                </select>
              </div>
              <div style="text-align:center;" >
                <button id="submit" type="submit" class="btn btn-primary" ></button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
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

      <div class="modal-footer">
      </div>
    </div>
  </div>
</div>

<script>
  var g_sAction = null;
  var g_sSubmitLabel = null;
  var g_sUsername = null;
  var g_sUsernameReadonly = null;
  var g_sFocusId = null;

  $( '#editUserDialog' ).on( 'show.bs.modal', onShow );
  $( '#editUserDialog' ).on( 'shown.bs.modal', onShown );
  $( '#editUserDialog' ).on( 'hide.bs.modal', onHide );
  $( '#editUserDialog' ).on( 'hidden.bs.modal', onHidden );

  function onShow()
  {
    console.log( 'onShow' );

    // Initialize input fields
    $( '#username' ).val( g_sUsername );
    $( '#username' ).prop( 'readonly', g_sUsernameReadonly );
    $( '#password' ).val( '' );
    $( '#confirm' ).val( '' );
    $( '#role' ).val( 'visitor' );
    $( '#submit' ).text( g_sSubmitLabel );

    // Clear messages
    clearMessages();
  }

  function onShown()
  {
    console.log( 'onShown' );
    $( '#' + g_sFocusId ).focus();
  }
  function onHide()
  {
    console.log( 'onHide' );
  }
  function onHidden()
  {
    console.log( 'onHidden' );
  }

  function handleClick()
  {
    console.log( 'submitUser: action=' + g_sAction );
    if ( validateInput() )
    {
      submitUser();
    }
  }

  function validateInput()
  {
    clearMessages();

    var aMessages = validateUsername();
    aMessages = aMessages.concat( validatePassword() );

    showMessages( aMessages );

    return ( aMessages.length == 0 );
  }

  function validateUsername()
  {
    var tUsername = $( '#username' );
    var sUsername = tUsername.val();
    var aMessages = [];

    if ( sUsername.length > <?=MAX_USERNAME_LENGTH?> )
    {
      aMessages.push( 'Username may contain at most <?=MAX_USERNAME_LENGTH?> characters.' );
    }

    if ( sUsername.length < <?=MIN_USERNAME_LENGTH?> )
    {
      aMessages.push( 'Username must contain at least <?=MIN_USERNAME_LENGTH?> characters.' );
    }

    if ( sUsername.indexOf( ' ' ) != -1 )
    {
      aMessages.push( 'Username may not contain spaces.' );
    }

    if ( ( sUsername.length > 0 ) && ! sUsername.match( /^[a-zA-Z0-9\-_]+$/ ) )
    {
      aMessages.push( 'Username can contain only alphanumeric, hyphen, and underscore characters.' );
    }

    if ( aMessages.length )
    {
      tUsername.parent().addClass( 'has-error' );
    }

    return aMessages;
  }

  function submitUser()
  {
    // Post request to server
    var tPostData = new FormData();
    tPostData.append( "username", $( '#username' ).val() );
    tPostData.append( "password", $( '#password' ).val() );
    tPostData.append( "role", $( '#role' ).val() );

    $.ajax(
      "users/" + g_sAction + "User.php",
      {
        type: 'POST',
        processData: false,
        contentType: false,
        dataType : 'json',
        data: tPostData
      }
    )
    .done( eval( g_sAction + 'Done' ) )
    .fail( handleAjaxError );
  }

  function addDone( tRsp, sStatus, tJqXhr )
  {
    if ( tRsp.unique )
    {
      location.reload();
    }
    else
    {
      var aMessages = [ "Username '" + tRsp.username + "' is not available." ];
      showMessages( aMessages );
    }
  }
</script>
