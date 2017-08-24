<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";
  require_once $_SERVER["DOCUMENT_ROOT"] . "/users/usernameRules.php";
  require_once $_SERVER["DOCUMENT_ROOT"] . "/users/passwordRules.php";
?>

<!-- Edit User dialog -->
<div class="modal fade" id="editDialog" tabindex="-1" role="dialog" aria-labelledby="editDialogTitle">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><span class="glyphicon glyphicon-remove"></span></span></button>
        <h4 class="modal-title" id="editDialogTitle"></h4>
      </div>
      <div class="modal-body">
        <?php
          include $_SERVER["DOCUMENT_ROOT"] . "/users/editUserForm.php";
        ?>
      </div>

      <div class="modal-footer">
        <div style="text-align:center;" >
          <button id="editDialogSubmit" type="submit" class="btn btn-primary" form="editUserForm" ></button>
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
  var g_sStatus = null;
  var g_sFirstName = null;
  var g_sLastName = null;
  var g_sEmailAddress = null;
  var g_sOrganization = null;
  var g_sDescription = null;

  var g_tAuthFacilities = null;

  var g_bUsernameDisabled = null;
  var g_sFocusId = null;
  var g_fnSubmitUserDone = null;
  var g_bDoValidation = null;

  function initAddDialog()
  {
    g_sAction = 'add';
    g_sUsername = '';
    formatLabels( 3 );

    g_sSubmitLabel = 'Add User';
    g_sRole = 'Visitor';
    g_sStatus = 'Enabled';
    g_sFirstName = '';
    g_sLastName = '';
    g_sEmailAddress = '';
    g_sOrganization = '';
    g_sDescription = '';
    g_tAuthFacilities = null;

    $( '.adminHide' ).show();

    g_bUsernameDisabled = false;
    g_bDoValidation = true;
    g_fnSubmitUserDone = addUserDone;

    getAllFacilities();
  }

  function initUpdateDialog( sUsername )
  {
    g_sAction = 'update';
    g_sUsername = sUsername;
    formatLabels( 4 );

    g_sSubmitLabel = 'Update User';
    g_bUsernameDisabled = true;

    var iRow = 0;
    var tRow = null;
    do
    {
      tRow = g_aSortableTableRows[iRow];
      iRow ++
    }
    while( ( iRow < g_aSortableTableRows.length ) && ( tRow.username != sUsername ) );

    // Load fields from the row
    g_sRole = tRow.role;
    g_sStatus = tRow.status;
    g_sFirstName = tRow.first_name;
    g_sLastName = tRow.last_name;
    g_sEmailAddress = tRow.email_address;
    g_sOrganization = tRow.organization;
    g_sDescription = tRow.user_description;
    g_tAuthFacilities = tRow.facilities_maps;

    g_bDoValidation = false;
    g_fnSubmitUserDone = updateUserDone;

    if ( g_sRole != 'Administrator' )
    {
      getAllFacilities();
    }
    else
    {
      clearAllFacilities()
    }
  }

  function onShowEditDialog()
  {
    // Initialize input fields
    $( '#username' ).val( g_sUsername );
    $( '#username' ).prop( 'disabled', g_bUsernameDisabled );
    $( '#oldPassword' ).val( '' );
    $( '#password' ).val( '' );
    $( '#confirm' ).val( '' );
    $( '#role' ).val( g_sRole );
    $( '#status' ).val( g_sStatus );
    $( '#first_name' ).val( g_sFirstName );
    $( '#last_name' ).val( g_sLastName );
    $( '#email_address' ).val( g_sEmailAddress );
    $( '#organization' ).val( g_sOrganization );
    $( '#user_description' ).val( g_sDescription );

    if ( g_sRole == 'Administrator' )
    {
      // Show role, but user can't edit it
      $( '#role,#status' ).prop( 'disabled', true );
      $( '#role option[value=Administrator]' ).show();

      // Hide fields that don't apply to admin
      $( '.adminHide' ).hide();
    }
    else
    {
      // Allow editing of role, but user can't choose 'Administrator'
      $( '#role,#status' ).prop( 'disabled', false );
      $( '#role option[value=Administrator]' ).hide();

      // Show fields that don't apply to admin
      $( '.adminHide' ).show();
    }

    $( '#editDialogTitle' ).text( g_sSubmitLabel );
    $( '#editDialogSubmit' ).text( g_sSubmitLabel );

    // Clear messages
    clearMessages();
  }

  function onShownEditDialog()
  {
    $( '#' + g_sFocusId ).focus();
  }

  function addUserDone( tRsp, sStatus, tJqXhr )
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

  function updateUserDone( tRsp, sStatus, tJqXhr )
  {
    if ( tRsp.user.messages.length == 0 )
    {
      location.reload();
    }
    else
    {
      // Show error messages
      showMessages( tRsp.user.messages );

      // Highlight Old Password, since (for now) that's the only thing that can produce an error in this operation
      $( '#oldPassword' ).closest( '.form-group' ).addClass( 'has-error' );
    }
  }
</script>

<script src="../users/editUser.js?version=<?=time()?>"></script>
