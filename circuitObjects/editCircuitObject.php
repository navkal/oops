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
              <div class="form-group">
                <label for="voltage"></label>
                <div>
                  <div id="voltage_container" >
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label for="parent_path"></label>
                <div id="parent_path_container" >
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
  var g_sNumber = null;
  var g_sName = null;
  var g_sVoltageId = null;
  var g_sLocationId = null;
  var g_sPath = null;

  var g_tDropdowns = null;

  function onShowEditDialog()
  {
    showSpinner();

    $( '#parent_path_container' ).html( '<select id="parent_path" class="form-control" style="width: 100%" ></select>' );
    $( '#voltage_container' ).html( '<select id="voltage" class="form-control" style="width: 100%" ></select>' );
    $( '#room_id_container' ).html( '<select id="room_id" class="form-control" style="width: 100%" ></select>' );

    if ( ! g_tDropdowns )
    {
      getDropdowns();
    }
    else
    {
      loadEditDialog();
    }
  }

  function getDropdowns()
  {
    // Post request to server
    var tPostData = new FormData();
    tPostData.append( 'object_type', g_sSortableTableType );

    $.ajax(
      "circuitObjects/getCircuitObjectDropdowns.php",
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
    if ( tRsp )
    {
      g_tDropdowns = tRsp;
    }

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
    $( '#number' ).val( g_sNumber );
    $( '#name' ).val( g_sName );
    $( '#voltage' ).val( g_sVoltageId );
    $( '#room_id' ).val( g_sLocationId );
    $( '#' + sDescrId ).val( g_sDescription );
    $( '#panel_photo_file,#panel_photo_filename' ).val( '' );

    // Initialize select2 objects
    $.fn.select2.defaults.set( 'theme', 'bootstrap' );
    $( '#parent_path' ).select2( { placeholder: 'Parent' } );
    $( '#voltage' ).select2( { placeholder: 'Voltage' } );
    $( '#room_id' ).select2( { placeholder: 'Location' } );

    // Optionally show control for uploading Panel picture
    $( "#panel_photo_upload_block" ).css( 'display', ( g_sSortableTableEditWhat == 'Panel' ) ? 'block' : 'none' );

    // Label dialog and submit button
    $( '#editDialogTitle' ).text( g_sSubmitLabel );
    $( '#editDialogFormSubmitProxy' ).text( g_sSubmitLabel );

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
    g_sVoltageId = '';
    g_sLocationId = '';
    g_sDescription = '';
    g_sPath = '';

    // Allow user to select a voltage
    $( '#voltage' ).prop( 'disabled', false );
  }

  function initUpdateDialog()
  {
    // Find the selected row
    var tRow = findSortableTableRow( g_sUpdateTarget );

    // Save values of selected row
    g_sObjectId = tRow.id;
    g_sParentId = tRow.parent_id;
    g_sNumber = tRow.number;
    g_sName = tRow.name;
    g_sVoltageId = tRow.voltage_id;
    g_sLocationId = tRow.room_id;
    g_sDescription = tRow.circuit_descr || tRow.panel_descr || tRow.transformer_descr;
    g_sPath = tRow.path;

    // Don't let the user change the voltage
    $( '#voltage' ).prop( 'disabled', true );
  }

  function makeDropdowns()
  {
    makeParentDropdown( g_sVoltageId )

    var sHtmlVoltage = '';
    var aVoltages = g_tDropdowns.voltages;
    for ( var iVoltage in aVoltages )
    {
      var tVoltage = aVoltages[iVoltage];
      sHtmlVoltage += '<option value="' + tVoltage.id + '" >' + tVoltage.text + '</option>';
    }

    $( '#voltage' ).html( sHtmlVoltage );

    var sHtmlLocation = '';
    var aLocations = g_tDropdowns.locations;
    for ( var iLoc in aLocations )
    {
      var tLoc = aLocations[iLoc];
      sHtmlLocation += '<option value="' + tLoc.id + '" >' + tLoc.text + '</option>';
    }

    $( '#room_id' ).html( sHtmlLocation );
  }

  function makeParentDropdown( sVoltageId )
  {
    // Save parent selection, if any
    var sParentVal = $( '#parent_path' ).val() || '';

    // Generate dropdown
    var sHtmlParentPath = '';
    var aParents = g_tDropdowns.parents;

    for ( var iParent in aParents )
    {
      var tParent = aParents[iParent];

      var bPathAllowed = ( tParent.text != g_sPath ) && ! tParent.text.startsWith( g_sPath + '.' );
      // --> KLUDGE: Assume that there are only two voltage levels and the higher voltage has the lower ID -->
      var bVoltageAllowed = sVoltageId ? ( ( tParent.object_type == 'Transformer' ) ? ( tParent.voltage_id < sVoltageId  ) : ( tParent.voltage_id == sVoltageId  ) ) : true;
      // <-- KLUDGE: Assume that there are only two voltage levels and the higher voltage has the lower ID <--
      if ( bPathAllowed && bVoltageAllowed )
      {
        sHtmlParentPath += '<option value="' + tParent.id + '" object_type="' + tParent.object_type + '" voltage_id="' + tParent.voltage_id + '" >' + tParent.text + '</option>';
      }
    }

    $( '#parent_path' ).html( sHtmlParentPath );

    // Restore parent selection, if possible
    $( '#parent_path' ).val( sParentVal );
  }

  function onShownEditDialog()
  {
    if ( $( '#voltage' ).prop( 'disabled' ) )
    {
      $( '#parent_path' ).focus();
    }
    else
    {
      $( '#voltage' ).focus();
    }

    // Allow user to select text in select2 rendering
    allowSelect2SelectText( 'parent_path' );
    allowSelect2SelectText( 'voltage' );
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

      // Special handling for Name field
      if ( sId == 'name' )
      {
        // Convert to uppercase
        tControl.val( sVal.toUpperCase() );
      }

      // If parent changed, correct voltage
      if ( sId == 'parent_path' )
      {
        var sParentType = tControl.find( 'option[value="' + sVal + '"]' ).attr( 'object_type' );
        var sParentVoltageId = tControl.find( 'option[value="' + sVal + '"]' ).attr( 'voltage_id' );
        var sCurrentVoltageId = $( '#voltage' ).val();

        // --> KLUDGE: Assume that there are only two voltage levels and the higher voltage has the lower ID -->
        var sLowVoltageId = Math.max( g_tDropdowns.voltages[0].id, g_tDropdowns.voltages[1].id ).toString();
        sAllowedVoltageId = ( sParentType == 'Transformer' ) ? sLowVoltageId : sParentVoltageId;
        // <-- KLUDGE: Assume that there are only two voltage levels and the higher voltage has the lower ID <--

        if ( sCurrentVoltageId != sAllowedVoltageId )
        {
          $( '#voltage' ).val( sAllowedVoltageId ).trigger( 'change' );
        }
      }

      // If voltage changed, re-populate the parent dropdown with compatible objects
      if ( sId == 'voltage' )
      {
        makeParentDropdown( sVal );
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

      var sNumber = $( '#number' ).val();
      var sName = $( '#name' ).val();
      var sHyphen = ( sNumber && sName ) ? '-' : '';
      tPostData.append( 'tail', sNumber + sHyphen + sName );

      tPostData.append( 'voltage_id', $( '#voltage' ).val() );
      tPostData.append( 'room_id', $( '#room_id' ).val() );
      tPostData.append( 'description', $( '#description textarea' ).val() );

      if ( $( '#panel_photo_filename' ).val() )
      {
        tPostData.append( 'panel_photo_file', $( '#panel_photo_file' ).prop( 'files' )[0] );
      }

      // Post request to server
      $.ajax(
        'circuitObjects/' + g_sAction + 'CircuitObject.php',
        {
          type: 'POST',
          processData: false,
          contentType: false,
          dataType : 'json',
          data: tPostData
        }
      )
      .done( editCircuitObjectDone )
      .fail( handleAjaxError );
    }
  }

  function validateInput()
  {
    clearMessages();
    var aMessages = [];

    if ( ( $( '#parent_path' ).val() == null ) && ( $( '#parent_path option' ).length > 0 ) )
    {
      aMessages.push( 'Parent is required' );
      $( '#parent_path_container .selection' ).closest( '.form-group' ).addClass( 'has-error' );
    }

    var sNumber = $( '#number' ).val();
    var sName = $( '#name' ).val();
    switch( g_sSortableTableEditWhat )
    {
      case 'Circuit':
        if ( ! sNumber && ! sName )
        {
          aMessages.push( 'Number or Name is required' );
          $( '#number' ).closest( '.form-group' ).addClass( 'has-error' );
          $( '#name' ).closest( '.form-group' ).addClass( 'has-error' );
        }
        break;

      case 'Panel':
      case 'Transformer':
        if ( ! sName )
        {
          aMessages.push( 'Name is required' );
          $( '#name' ).closest( '.form-group' ).addClass( 'has-error' );
        }
        break;

      default:
        aMessages.push( "Unrecognized Circuit Object type '" + g_sSortableTableEditWhat + "'" );
        break;
    }

    if ( sNumber.length > 0 )
    {
      if ( ! sNumber.match( /^\d+$/ ) )
      {
        aMessages.push( 'Number can contain only digits.' );
        $( '#number' ).closest( '.form-group' ).addClass( 'has-error' );
      }

      if ( parseInt( sNumber ) == 0 )
      {
        aMessages.push( 'Number must be an integer value between 1 and 9999.' );
        $( '#number' ).closest( '.form-group' ).addClass( 'has-error' );
      }
    }

    if ( ( sName.length > 0 ) && ! sName.match( /^[a-zA-Z0-9\-_]+$/ ) )
    {
      aMessages.push( 'Name can contain only alphanumeric, hyphen, and underscore characters.' );
      $( '#name' ).closest( '.form-group' ).addClass( 'has-error' );
    }

    if ( $( '#voltage' ).val() == null )
    {
      aMessages.push( 'Voltage is required' );
      $( '#voltage_container .selection' ).closest( '.form-group' ).addClass( 'has-error' );
    }

    if ( $( '#room_id' ).val() == null )
    {
      aMessages.push( 'Location is required' );
      $( '#room_id_container .selection' ).closest( '.form-group' ).addClass( 'has-error' );
    }

    showMessages( aMessages );
    return ( aMessages.length == 0 );
  }

  function editCircuitObjectDone( tRsp, sStatus, tJqXhr )
  {
    if ( tRsp.messages.length )
    {
      // Show error messages
      showMessages( tRsp.messages );

      // Highlight pertinent fields
      var aSelectors = tRsp.selectors;
      for ( var iSelector in aSelectors )
      {
        var sSelector = aSelectors[iSelector];
        $( sSelector ).closest( '.form-group' ).addClass( 'has-error' );
      }
    }
    else
    {
      switch( g_sAction )
      {
        case 'add':
          showAddedRow( tRsp.row )
          break;

        case 'update':
          location.reload();
          break;
      }
    }
  }

  function showAddedRow( tRow )
  {
    $('#editDialog').modal('hide');

    // Determine whether current table has all the columns needed to render the new row
    var bTableHasAllColumns = true;
    var aKeys = Object.keys( tRow );
    var iCol = 0;
    for ( var iCol = 0; ( iCol < aKeys.length ) && bTableHasAllColumns; iCol ++ )
    {
      var sKey = aKeys[iCol];
      var tRule =  g_tPropertyRules[sKey];
      var sLabel = ( tRule && tRule.showInSortableTable ) ? tRule.label : null;

      if ( sLabel != null )
      {
        var sCell = tRow[sKey];
        var bColumnEmpty = g_tColumnMap[sLabel].empty;

        // If we got a cell that does not have a corresponding column in the table, then reload the table
        if ( sCell && bColumnEmpty )
        {
          bTableHasAllColumns = false;
        }
      }
    }

    if ( bTableHasAllColumns )
    {
      // Render new row in existing table
      var sHtml = '<tr class="text-success"><td>1</td><td><b>MOOOOOOOOOO</b></td><td></td><td>MOOOOOOOOOO</td><td></td><td>277/480</td><td></td><td>1069</td><td>Delivery Area/ Corridor</td><td>0</td><td>9</td><td></td></tr>';
      $( '#sortableTableBody' ).prepend( sHtml );
      $( '#sortableTable' ).trigger( 'update', [true] );
      renumberIndex();
    }
    else
    {
      // Reload page
      location.reload();
    }
  }
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" />
<link rel="stylesheet" href="https://select2.github.io/select2-bootstrap-theme/css/select2-bootstrap.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
