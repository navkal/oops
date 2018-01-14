<!-- Copyright 2018 Panel Spy.  All rights reserved. -->

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
          <button type="button" id="editUserFormSubmitProxy" class="btn btn-primary" onclick="$('#editUserFormSubmitButton').click()" ></button>
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

  function onShowEditDialog()
  {
    g_fnSubmitUserDone = submitEditDialogDone;

    switch( g_sAction )
    {
      case 'add':
        initAddDialog();
        break;

      case 'update':
        initUpdateDialog();
        break;
    }

    // Initialize input fields
    $( '#username' ).val( g_sUsername );
    $( '#username' ).prop( 'disabled', g_bUsernameDisabled );
    $( '#oldPassword' ).val( '' );
    $( '#password' ).val( '' );
    $( '#confirm' ).val( '' );
    $( '#role' ).val( g_sRole );
    $( '#status' ).val( g_sStatus );
    $( '#first_name' ).val( htmlentities_undo( g_sFirstName ) );
    $( '#last_name' ).val( htmlentities_undo( g_sLastName ) );
    $( '#email_address' ).val( htmlentities_undo( g_sEmailAddress ) );
    $( '#organization' ).val( htmlentities_undo( g_sOrganization ) );
    $( '#user_description' ).val( htmlentities_undo( g_sDescription ) );

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
    $( '#editUserFormSubmitProxy' ).text( g_sSubmitLabel );

    // Clear messages
    clearMessages();
  }

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

    getAllFacilities();
  }

  function initUpdateDialog()
  {
    g_sAction = 'update';
    formatLabels( 4 );

    g_sSubmitLabel = 'Update User';
    g_bUsernameDisabled = true;

    var tRow = findSortableTableRow( g_sUpdateTarget );

    // Load fields from the row
    g_sUsername = tRow.username;
    g_sRole = tRow.role;
    g_sStatus = tRow.status;
    g_sFirstName = tRow.first_name;
    g_sLastName = tRow.last_name;
    g_sEmailAddress = tRow.email_address;
    g_sOrganization = tRow.organization;
    g_sDescription = tRow.user_description;
    g_tAuthFacilities = tRow.facilities_maps;

    g_bDoValidation = false;

    if ( g_sRole != 'Administrator' )
    {
      getAllFacilities();
    }
    else
    {
      clearAllFacilities()
    }
  }

  function onShownEditDialog()
  {
    $( '#' + g_sFocusId ).focus();
  }
</script>

<script src="../users/editUser.js?version=<?=time()?>"></script>
