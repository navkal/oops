<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"] . "/users/usernameRules.php";
  require_once $_SERVER["DOCUMENT_ROOT"] . "/users/passwordRules.php";
?>

<!-- Add User button -->
<button id="addUserButton" class="btn btn-default btn-sm pull-right" onclick="initAdd()" data-toggle="modal" data-target="#editUserDialog" data-backdrop="static" data-keyboard=false style="display:none" >
  <span class="glyphicon glyphicon-plus"></span> Add User
</button>

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
            <form id="editUserForm" onsubmit="handleClick(event); return false;" >
              <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" maxlength="<?=MAX_USERNAME_LENGTH+1?>" placeholder="Username" autocomplete="off" >
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
                <label id="roleLabel" >Role</label>
                <select id="role" class="form-control">
                  <option value="visitor">Visitor</option>
                  <option value="technician">Technician</option>
                </select>
                <input type="text" id="admin" class="form-control" readonly >
              </div>
            </form>
          </div>
        </div>

      </div>

      <div class="modal-footer">

        <div style="text-align:center;" >
          <button id="submit" type="submit" class="btn btn-primary" form="editUserForm" ></button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        </div>

        <br/>

        <div id="messages" class="alert alert-danger" style="text-align:left; display:none" role="alert">
          <ul id="messageList">
          </ul>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
  var g_sAction = null;
  var g_sSubmitLabel = null;
  var g_sUsername = null;
  var g_sRole = null;
  var g_sUsernameReadonly = null;
  var g_sFocusId = null;

  $( '#editUserDialog' ).on( 'show.bs.modal', onShow );
  $( '#editUserDialog' ).on( 'shown.bs.modal', onShown );

  function initAdd()
  {
    g_sAction = 'add';
    g_sSubmitLabel = 'Add User';
    g_sUsername = '';
    g_sRole = 'visitor';
    g_sUsernameReadonly = false;
    g_sFocusId = 'username';
  }

  function initUpdate( sUsername )
  {
    g_sAction = 'update';
    g_sSubmitLabel = 'Update User';
    g_sUsername = sUsername;
    g_sUsernameReadonly = true;
    g_sFocusId = 'password';

    var iRow = 0;
    var tRow = null;
    do
    {
      tRow = g_aSortableTableRows[iRow];
      iRow ++
    }
    while( ( iRow < g_aSortableTableRows.length ) && ( tRow.username != sUsername ) );

    g_sRole = tRow.role;
  }

  function onShow()
  {
    // Initialize input fields
    $( '#username' ).val( g_sUsername );
    $( '#username' ).prop( 'readonly', g_sUsernameReadonly );
    $( '#password' ).val( '' );
    $( '#confirm' ).val( '' );

    if ( g_sRole == 'administrator' )
    {
      $( '#role' ).hide();
      $( '#admin' ).show();
      $( '#admin' ).val( g_sRole );
      $( '#roleLabel' ).attr( 'for', 'admin' );
    }
    else
    {
      $( '#admin' ).hide();
      $( '#role' ).show();
      $( '#role' ).val( g_sRole );
      $( '#roleLabel' ).attr( 'for', 'role' );
    }

    $( '#editUserLabel' ).text( g_sSubmitLabel );
    $( '#submit' ).text( g_sSubmitLabel );

    // Clear messages
    clearMessages();
  }

  function onShown()
  {
    $( '#' + g_sFocusId ).focus();
  }

  function handleClick()
  {
    if ( validateUser() )
    {
      submitUser();
    }
  }

  function validateUser()
  {
    clearMessages();

    var aMessages = validateUsername();
    if ( ( g_sAction == 'add' ) || ( $( '#password' ).val().length > 0 ) || ( $( '#confirm' ).val().length > 0 ) )
    {
      aMessages = aMessages.concat( validatePassword() );
    }

    showMessages( aMessages );

    return ( aMessages.length == 0 );
  }

  function submitUser()
  {
    // Post request to server
    var tPostData = new FormData();
    tPostData.append( "username", $( '#username' ).val() );
    tPostData.append( "password", $( '#password' ).val() );
    var tRole = $( '#role' ).is( ':visible' ) ? $( '#role' ) : $( '#admin' );
    tPostData.append( "role", tRole.val() );

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

  function updateDone( tRsp, sStatus, tJqXhr )
  {
    location.reload();
  }
</script>
