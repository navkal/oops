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
                <label for="voltage"></label>
                <div>
                  <div id="voltage_container" >
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label for="loc_new">Location</label>
                <div id="loc_new_container" >
                </div>
              </div>
              <div id="description" class="form-group">
                <label></label>
                <div>
                  <textarea class="form-control" maxlength="512" ></textarea>
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
  var g_sVoltage = null;
  var g_sLocationId = null;

  var g_bGotDropdowns = false;
  var g_tRsp = null;

  function onShowEditDialog()
  {
    showSpinner();

    $( '#parent_path_container' ).html( '<select id="parent_path" class="form-control" style="width: 100%" ></select>' );
    $( '#voltage_container' ).html( '<select id="voltage" class="form-control" style="width: 100%" ></select>' );
    $( '#loc_new_container' ).html( '<select id="loc_new" class="form-control" style="width: 100%" ></select>' );

    if ( ! g_bGotDropdowns )
    {
      g_bGotDropdowns = true;
      getDropdowns();
    }
    else
    {
      makeDropdowns();
    }
  }

  var g_iBfDebug = null;
  function getDropdowns()
  {
    g_iBfDebug = Date.now();

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
    .done( makeDropdowns )
    .fail( handleAjaxError );
  }

  function makeDropdowns( tRsp, sStatus, tJqXhr )
  {
    if ( tRsp )
    {
      g_tRsp = tRsp;
      console.log( '==> Parent count: ' + tRsp.parents.length );
      console.log( '==> Location count: ' + tRsp.locations.length );
    }
    else
    {
      tRsp = g_tRsp;
    }

    var sHtmlParentPath = '';
    var aParents = tRsp.parents;
    for ( var iParent in aParents )
    {
      var tParent = aParents[iParent];
      sHtmlParentPath += '<option value="' + tParent.id + '" >' + tParent.text + '</option>';
    }

    var sHtmlVoltage = '';
    var aVoltages = tRsp.voltages;
    for ( var iVoltage in aVoltages )
    {
      var tVoltage = aVoltages[iVoltage];
      sHtmlVoltage += '<option value="' + tVoltage.id + '" >' + tVoltage.text + '</option>';
    }

    var sHtmlLocation = '';
    var aLocations = tRsp.locations;
    for ( var iLoc in aLocations )
    {
      var tLoc = aLocations[iLoc];
      sHtmlLocation += '<option value="' + tLoc.id + '" >' + tLoc.text + '</option>';
    }

    $( '#parent_path' ).html( sHtmlParentPath );
    $( '#voltage' ).html( sHtmlVoltage );
    $( '#loc_new' ).html( sHtmlLocation );

    loadEditDialog()
  }

  function loadEditDialog()
  {
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

    // Initialize input fields
    $( '#parent_path' ).val( g_sParentId );
    $( '#number' ).val( g_sNumber );
    $( '#name' ).val( g_sName );
    $( '#voltage' ).val( g_sVoltage );
    $( '#loc_new' ).val( g_sLocationId );
    $( '#' + sDescrId ).val( g_sDescription );

    // Initialize select2 objects
    $.fn.select2.defaults.set( 'theme', 'bootstrap' );
    $( '#parent_path' ).select2( { placeholder: 'Parent' } );
    $( '#voltage' ).select2( { placeholder: 'Voltage' } );
    $( '#loc_new' ).select2( { placeholder: 'Location' } );

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
    g_sVoltage = '';
    g_sLocationId = '';
    g_sDescription = '';
  }

  function initUpdateDialog()
  {
    // Find the selected row
    var iRow = 0;
    var tRow = null;
    do
    {
      tRow = g_aSortableTableRows[iRow];
      iRow ++
    }
    while( ( iRow < g_aSortableTableRows.length ) && ( tRow.id != g_sUpdateTarget ) );

    // Save values of selected row
    g_sObjectId = tRow.id;
    g_sParentId = tRow.parent_id;
    g_sNumber = tRow.number;
    g_sName = tRow.name;
    g_sVoltage = tRow.voltage_id;
    g_sLocationId = tRow.room_id;
    g_sDescription = tRow.circuit_descr || tRow.panel_descr || tRow.transformer_descr;
  }

  function onShownEditDialog()
  {
    $( '#parent_path' ).focus();

    // Allow user to select text in setting display
    if ( $( '#parent_path' ).val() )
    {
      $( '#select2-parent_path-container' ).css(
        {
          '-webkit-user-select': 'text',
          '-moz-user-select': 'text',
          '-ms-user-select': 'text',
          'user-select': 'text',
        }
      );
    }
    if ( $( '#voltage' ).val() )
    {
      $( '#select2-voltage-container' ).css(
        {
          '-webkit-user-select': 'text',
          '-moz-user-select': 'text',
          '-ms-user-select': 'text',
          'user-select': 'text',
        }
      );
    }
    if ( $( '#loc_new' ).val() )
    {
      $( '#select2-loc_new-container' ).css(
        {
          '-webkit-user-select': 'text',
          '-moz-user-select': 'text',
          '-ms-user-select': 'text',
          'user-select': 'text',
        }
      );
    }

    var tSelect2 = $( '#select2-parent_path-container' );

    hideSpinner();
  }

  function onChangeControl( tEvent )
  {
    var tControl = $( tEvent.target );
    tControl.val( tControl.val().trim() );

    // Special handling for select2 objects
    if ( tControl.prop( 'tagName' ).toLowerCase() == 'select' )
    {
      var tSelect2 = $( '#select2-' + tControl.attr( 'id' ) + '-container' );
      tSelect2.text( getSelect2Text( tControl ) );

      // Allow user to select text in setting display
      tSelect2.css(
        {
          '-webkit-user-select': 'text',
          '-moz-user-select': 'text',
          '-ms-user-select': 'text',
          'user-select': 'text',
        }
      );
    }

    // Set flag
    g_bChanged = true;
  }

  function onSubmitEditDialog()
  {
    if ( g_bChanged && validateInput() )
    {
      var tPostData = new FormData();

      if ( g_sObjectId )
      {
        tPostData.append( "id", g_sObjectId );
      }

      tPostData.append( 'parent_id', $( '#parent_path' ).val() );

      var sParentPath = getSelect2Text( $( '#parent_path' ) );
      var sNumber = $( '#number' ).val();
      var sName = $( '#name' ).val();
      var sHyphen = ( sNumber && sName ) ? '-' : '';
      tPostData.append( 'path', sParentPath + '.' + sNumber + sHyphen + sName );

      tPostData.append( 'voltage_id', $( '#voltage' ).val() );
      tPostData.append( 'room_id', $( '#loc_new' ).val() );
      tPostData.append( 'description', $( '#description textarea' ).val() );

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

    if ( $( '#parent_path' ).val() == null )
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

    if ( $( '#loc_new' ).val() == null )
    {
      aMessages.push( 'Location is required' );
      $( '#loc_new_container .selection' ).closest( '.form-group' ).addClass( 'has-error' );
    }

    showMessages( aMessages );
    return ( aMessages.length == 0 );
  }

  function editCircuitObjectDone( tRsp, sStatus, tJqXhr )
  {
    alert( JSON.stringify( tRsp ) );

    if ( true /* tRsp.messages.length */ )
    {
      // Show error messages
      showMessages( ['fake backend error: path "xxxxxx" not available'] );

      // Highlight the fields that make up the path, since (for now) those are the elements that can produce an error in this operation
      $( '#parent_path_container .selection' ).closest( '.form-group' ).addClass( 'has-error' );
      $( '#number' ).closest( '.form-group' ).addClass( 'has-error' );
      $( '#name' ).closest( '.form-group' ).addClass( 'has-error' );
    }
    else
    {
      location.reload();
    }
  }
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" />
<link rel="stylesheet" href="https://select2.github.io/select2-bootstrap-theme/css/select2-bootstrap.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
