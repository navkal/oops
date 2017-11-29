<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";
?>

<!-- Edit dialog for Circuit, Panel, Transformer -->
<div class="modal fade" id="editDialog" role="dialog" aria-labelledby="editDialogTitle">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><span class="glyphicon glyphicon-remove"></span></span></button>
        <h4 class="modal-title" id="editDialogTitle"></h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <form id="editDialogForm" class="form-horizontal" onsubmit="onSubmitEditDialog(event); return false;" >
              <div class="form-group" id="three_phase_block" >
                <label for="three_phase"></label>
                <div>
                  <input type="hidden" class="form-control" id="three_phase">
                  <div class="radio-inline">
                    <label style="font-weight: normal;" >
                      <input type="radio" name="three_phase" >
                      Yes
                    </label>
                  </div>
                  <div class="radio-inline">
                    <label style="font-weight: normal;" >
                      <input type="radio" name="three_phase" >
                      No
                    </label>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label for="parent_path"></label>
                <div id="parent_path_container" >
                </div>
              </div>
              <div class="form-group">
                <label for="phase_b_tail"></label>
                <div id="phase_b_tail_container" >
                </div>
              </div>
              <div class="form-group">
                <label for="phase_c_tail"></label>
                <div id="phase_c_tail_container" >
                </div>
              </div>
              <div class="form-group">
                <label for="number"></label>
                <div>
                  <input type="text" class="form-control" id="number" maxlength="4">
                </div>
              </div>
              <div class="form-group">
                <label for="name"></label>
                <div>
                  <input type="text" class="form-control" id="name" maxlength="40">
                </div>
              </div>
              <div class="form-group">
                <label for="room_id"></label>
                <div id="room_id_container" >
                </div>
              </div>
              <div id="description" class="form-group">
                <label></label>
                <div>
                  <textarea class="form-control" maxlength="150" ></textarea>
                </div>
              </div>
              <div class="form-group" id="panel_photo_upload_block" >
                <label for="panel_photo_input_group"></label>
                <div >
                  <div class="input-group" id="panel_photo_input_group" >
                    <label class="input-group-btn" >
                      <span class="btn btn-default" >
                        Browse…
                        <input type="file" id="panel_photo_file" style="display:none" onchange="showUploadFilename( 'panel_photo_file', 'panel_photo_filename'  )" >
                      </span>
                    </label>
                    <input id="panel_photo_filename" type="text" class="form-control" onclick="$('#panel_photo_file').click();" readonly >
                  </div>
                </div>
              </div>
              <button id="editDialogFormSubmitButton" type="submit" style="display:none" ></button>
            </form>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <div style="text-align:center;" >
          <button type="button" id="editDialogFormSubmitProxy" class="btn btn-primary" onclick="$('#editDialogFormSubmitButton').click()" ></button>
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
  var g_sObjectId = null;
  var g_sParentId = null;
  var g_sPhaseBParentId = null;
  var g_sPhaseCParentId = null;
  var g_sNumber = null;
  var g_sName = null;
  var g_sParentVoltageId = null;
  var g_sLocationId = null;
  var g_sPath = null;

  var g_tDropdowns = null;

  function onShowEditDialog()
  {
    showSpinner();

    $( '#parent_path_container' ).html( '<select id="parent_path" class="form-control" style="width: 100%" ></select>' );
    $( '#phase_b_tail_container' ).html( '<select id="phase_b_tail" class="form-control" style="width: 100%" ></select>' );
    $( '#phase_c_tail_container' ).html( '<select id="phase_c_tail" class="form-control" style="width: 100%" ></select>' );
    $( '#room_id_container' ).html( '<select id="room_id" class="form-control" style="width: 100%" ></select>' );

    getDropdowns();
  }

  function getDropdowns()
  {
    // Post request to server
    var tPostData = new FormData();
    tPostData.append( 'object_type', g_sSortableTableType );

    if ( g_sUpdateTarget )
    {
      var sId = findSortableTableRow( g_sUpdateTarget ).id;
    }
    else
    {
      var sId = '';
    }

    tPostData.append( 'id', sId );

    $.ajax(
      "distributionObjects/getDistributionObjectDropdowns.php",
      {
        type: 'POST',
        processData: false,
        contentType: false,
        dataType : 'json',
        data: tPostData
      }
    )
    .done( loadEditDialog )
    .fail( handleAjaxError );
  }

  function loadEditDialog( tRsp, sStatus, tJqXhr )
  {
    g_tDropdowns = tRsp;

    // Customize ID of description field
    var sDescrId = g_sSortableTableEditWhat.toLowerCase() + '_descr';
    $( '#description textarea' ).attr( 'id', sDescrId );
    $( '#description label' ).attr( 'for', sDescrId );

    initEditDialog();

    switch( g_sAction )
    {
      case 'add':
        initAddDialog();
        break;

      case 'update':
        initUpdateDialog();
        break;
    }

    makeDropdowns();

    // Initialize input fields
    $( '#parent_path' ).val( g_sParentId );
    $( '#phase_b_tail' ).val( g_sPhaseBParentId );
    $( '#phase_c_tail' ).val( g_sPhaseCParentId );
    $( '#number' ).val( g_sNumber );
    $( '#name' ).val( g_sName );
    $( '#three_phase_block label:contains(' + g_sPhases + ') input' ).prop( 'checked', true );
    $( '#room_id' ).val( g_sLocationId );
    $( '#' + sDescrId ).val( g_sDescription );
    $( '#panel_photo_file,#panel_photo_filename' ).val( '' );

    // Initialize select2 objects
    $.fn.select2.defaults.set( 'theme', 'bootstrap' );
    $( '#parent_path' ).select2( { placeholder: g_tPropertyRules['parent_path'].label } );
    $( '#phase_b_tail' ).select2( { placeholder: g_tPropertyRules['phase_b_tail'].label } );
    $( '#phase_c_tail' ).select2( { placeholder: g_tPropertyRules['phase_c_tail'].label } );
    $( '#room_id' ).select2( { placeholder: g_tPropertyRules['room_id'].label } );

    // Customize type-specific fields
    $( "#three_phase_block" ).css( 'display', ( g_sSortableTableEditWhat == 'Panel' ) ? 'block' : 'none' );
    $( '#three_phase_block label:contains(Yes) input' ).val( ( g_sSortableTableEditWhat == 'Panel' ) ? '1' : '' );
    $( '#three_phase_block label:contains(No) input' ).val( ( g_sSortableTableEditWhat == 'Panel' ) ? '0' : '' );
    $( "#panel_photo_upload_block" ).css( 'display', ( g_sSortableTableEditWhat == 'Panel' ) ? 'block' : 'none' );
    $( '#phase_b_tail_container, #phase_c_tail_container' ).closest( '.form-group' ).css( 'display', ( g_sSortableTableEditWhat == 'Circuit' ) ? 'none' : 'block' );

    // Set change handler
    resetChangeHandler();

    // Clear messages
    clearMessages();
  }

  function initAddDialog()
  {
    g_sParentId = '';
    g_sNumber = '';
    g_sName = '';
    g_sParentVoltageId = ( g_sSortableTableEditWhat == 'Transformer' ) ? '1' : '';
    g_sLocationId = '';
    g_sDescription = '';
    g_sPath = '';
    g_sPhases = 'Yes';

    // Allow user to set creation-time attributes
    $( '#three_phase_block input' ).prop( 'disabled', false );
  }

  function initUpdateDialog()
  {
    // Find the selected row
    var tRow = findSortableTableRow( g_sUpdateTarget );

    // Save values of selected row
    g_sObjectId = tRow.id;
    g_sParentId = tRow.parent_id;
    g_sPhaseBParentId = tRow.phase_b_parent_id;
    g_sPhaseCParentId = tRow.phase_c_parent_id;
    g_sNumber = tRow.number;
    g_sName = tRow.name;
    g_sParentVoltageId = tRow.parent_voltage_id;
    g_sLocationId = tRow.room_id;
    g_sDescription = htmlentities_undo( tRow.circuit_descr || tRow.panel_descr || tRow.transformer_descr );
    g_sPath = tRow.path;
    g_sPhases = tRow.three_phase;

    // Don't let the user change creation-time settings
    $( '#three_phase_block input' ).prop( 'disabled', true );

    // Don't let user change parent of bound circuit
    $( '#parent_path' ).prop( 'disabled', ( tRow.object_type == 'Circuit' ) && tRow.is_bound_circuit );
  }

  function makeDropdowns()
  {
    makeParentDropdown()
    makePhaseDropdowns( g_tDropdowns.parents, g_sParentId, g_sPhaseBParentId );

    var sHtmlLocation = '';
    var aLocations = g_tDropdowns.locations;
    for ( var iLoc in aLocations )
    {
      var tLoc = aLocations[iLoc];
      sHtmlLocation += '<option value="' + tLoc.id + '" >' + tLoc.text + '</option>';
    }

    $( '#room_id' ).html( sHtmlLocation );
  }

  function makeParentDropdown()
  {
    // Generate dropdown
    var sHtmlParentPath = '';
    var aParents = g_tDropdowns.parents;

    for ( var iParent in aParents )
    {
      var tParent = aParents[iParent];
      sHtmlParentPath += '<option value="' + tParent.id + '" object_type="' + tParent.object_type + '" voltage_id="' + tParent.voltage_id + '" >' + tParent.text + '</option>';
    }

    $( '#parent_path' ).html( sHtmlParentPath );
  }

  function onShownEditDialog()
  {
    if ( $( '#parent_path' ).prop( 'disabled' ) )
    {
      $( '#number' ).focus();
    }
    else
    {
      $( '#parent_path' ).focus();
    }

    // Allow user to select text in select2 rendering
    allowSelect2SelectText( 'parent_path' );
    allowSelect2SelectText( 'phase_b_tail' );
    allowSelect2SelectText( 'phase_c_tail' );
    allowSelect2SelectText( 'room_id' );

    // Set handler to focus on select2 object after user sets value
    setSelect2CloseHandler();

    hideSpinner();
  }

  function onChangeControl( tEvent )
  {
    var tControl = $( tEvent.target );

    if ( tControl.val() != null )
    {
      if ( ( tControl.attr( 'type' ) == 'text' ) || ( tControl.prop( 'tagName' ).toLowerCase() == 'textarea' ) )
      {
        tControl.val( tControl.val().trim() );
      }

      var sId = tControl.attr( 'id' );
      var sVal = tControl.val();

      switch( sId )
      {
        case 'parent_path':
          makePhaseDropdowns( g_tDropdowns.parents, sVal, $( '#phase_b_tail' ).val() );
          break;

        case 'phase_b_tail':
          updatePhaseCDropdown( $( '#phase_b_tail' ).val() );
          break;

        case 'name':
          // Convert to uppercase
          tControl.val( sVal.toUpperCase() );
          break;
      }

      // Special handling for select2 objects
      if ( tControl.prop( 'tagName' ).toLowerCase() == 'select' )
      {
        var tSelect2 = $( '#select2-' + sId + '-container' );
        tSelect2.text( getSelect2Text( tControl ) );

        allowSelect2SelectText( sId );
      }
    }

    // Set flag
    g_bChanged = true;
  }

  // Show selected filename in input field
  function showUploadFilename( sFileId, sFilenameId )
  {
    var sFilename = $( '#' + sFileId ).val().split('\\').pop().split('/').pop();
    $( '#' + sFilenameId ).val( sFilename );
  }

  function onSubmitEditDialog()
  {
    if ( g_bChanged && validateInput() )
    {
      var tPostData = new FormData();

      if ( g_sObjectId )
      {
        tPostData.append( 'id', g_sObjectId );
      }

      tPostData.append( 'object_type', g_sSortableTableEditWhat );
      tPostData.append( 'parent_id', $( '#parent_path' ).val() ? $( '#parent_path' ).val() : '' );
      tPostData.append( 'phase_b_parent_id', $( '#phase_b_tail' ).val() ? $( '#phase_b_tail' ).val() : '' );
      tPostData.append( 'phase_c_parent_id', $( '#phase_c_tail' ).val() ? $( '#phase_c_tail' ).val() : '' );

      var sNumber = $( '#number' ).val();
      var sName = $( '#name' ).val();
      var sHyphen = ( sNumber && sName ) ? '-' : '';
      tPostData.append( 'tail', sNumber + sHyphen + sName );

      tPostData.append( 'three_phase', $( '#three_phase_block input[name=three_phase]:checked' ).val() );

      tPostData.append( 'room_id', $( '#room_id' ).val() );
      tPostData.append( 'description', htmlentities( $( '#description textarea' ).val() ) );

      if ( $( '#panel_photo_filename' ).val() )
      {
        tPostData.append( 'panel_photo_file', $( '#panel_photo_file' ).prop( 'files' )[0] );
      }

      showSpinner();

      // Post request to server
      $.ajax(
        'distributionObjects/' + g_sAction + 'DistributionObject.php',
        {
          type: 'POST',
          processData: false,
          contentType: false,
          dataType : 'json',
          data: tPostData
        }
      )
      .done( submitEditDialogDone )
      .fail( handleAjaxError );
    }
  }

  function validateInput()
  {
    clearMessages();

    var aMessages = validateDistributionObjectInput( g_sSortableTableEditWhat );
    showMessages( aMessages );

    return ( aMessages.length == 0 );
  }
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" />
<link rel="stylesheet" href="https://select2.github.io/select2-bootstrap-theme/css/select2-bootstrap.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
