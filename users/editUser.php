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
        <?php
          include $_SERVER["DOCUMENT_ROOT"] . "/users/editUserForm.php";
        ?>
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
  var g_fnSubmitUserDone = null;
  var g_bDoValidation = null;

  $( '#editUserDialog' ).on( 'show.bs.modal', onShow );
  $( '#editUserDialog' ).on( 'shown.bs.modal', onShown );

  function initAdd()
  {
    g_sAction = 'add';
    g_sSubmitLabel = 'Add User';
    g_sUsername = '';
    g_sRole = 'Visitor';
    g_sUsernameReadonly = false;
    g_sFocusId = 'username';
    g_bDoValidation = true;
    g_fnSubmitUserDone = addDone;
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
    g_bDoValidation = false;
    g_fnSubmitUserDone = updateDone;
  }

  function onShow()
  {
    // Initialize input fields
    $( '#username' ).val( g_sUsername );
    $( '#username' ).prop( 'readonly', g_sUsernameReadonly );
    $( '#password' ).val( '' );
    $( '#confirm' ).val( '' );

    $( '#role,#readonlyRole' ).val( g_sRole );
    if ( g_sRole == 'Administrator' )
    {
      $( '#role' ).hide();
      $( '#readonlyRole' ).show();
      $( '#roleLabel' ).attr( 'for', 'readonlyRole' );
    }
    else
    {
      $( '#readonlyRole' ).hide();
      $( '#role' ).show();
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

<script src="../users/editUser.js?version=<?=time()?>"></script>
