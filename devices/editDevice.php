<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";
?>

<!-- Edit Location dialog -->
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
                <label for="source_path"></label>
                <div id="source_path_container" >
                </div>
              </div>
              <div class="form-group">
                <label for="name"></label>
                <div>
                  <input type="text" class="form-control" id="name" maxlength="50" required>
                </div>
              </div>
              <div class="form-group">
                <label for="loc_new">Location</label>
                <div id="loc_new_container" >
                </div>
              </div>
              <button id="editDialogSubmitProxy" type="submit" style="display:none" ></button>
            </form>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <div style="text-align:center;" >
          <button id="editDialogSubmit" class="btn btn-primary" onclick="$('#editDialogSubmitProxy').click()" ></button>
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

  var g_sDeviceId = null;
  var g_sSourceId = null;
  var g_sName = null;
  var g_sLocationId = null;

  var g_bGotDropdowns = false;
  var g_tRsp = null;

  function onShowEditDialog()
  {
    showSpinner();

    $( '#source_path_container' ).html( '<select id="source_path" class="form-control" style="width: 100%" ></select>' );
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
    tPostData.append( "postSecurity", "" );

    $.ajax(
      "devices/getDeviceDropdowns.php",
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
      console.log( '==> Circuit count: ' + tRsp.sources.length );
      console.log( '==> Location count: ' + tRsp.locations.length );
    }
    else
    {
      tRsp = g_tRsp;
    }

    var sHtmlSourcePath = '';
    var aSources = tRsp.sources;
    for ( var iSource in aSources )
    {
      var tSource = aSources[iSource];
      sHtmlSourcePath += '<option value="' + tSource.id + '" >' + tSource.text + '</option>';
    }

    var sHtmlLocation = '<option value="0" >[none]</option>';
    var aLocations = tRsp.locations;
    for ( var iLoc in aLocations )
    {
      var tLoc = aLocations[iLoc];
      sHtmlLocation += '<option value="' + tLoc.id + '" >' + tLoc.text + '</option>';
    }

    $( '#source_path' ).html( sHtmlSourcePath );
    $( '#loc_new' ).html( sHtmlLocation );

    loadEditDialog()
  }

  function loadEditDialog()
  {
    initEditDialog( 'Device' );

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
    $( '#source_path' ).val( g_sSourceId );
    $( '#name' ).val( g_sName );
    $( '#loc_new' ).val( g_sLocationId );

    // Initialize select2 objects
    $.fn.select2.defaults.set( 'theme', 'bootstrap' );
    $( '#source_path' ).select2( { placeholder: 'Select a Circuit' } );
    $( '#loc_new' ).select2( { placeholder: 'Select a Location' } );

    // Label dialog and submit button
    $( '#editDialogTitle' ).text( g_sSubmitLabel );
    $( '#editDialogSubmit' ).text( g_sSubmitLabel );

    // Set change handler
    resetChangeHandler();

    // Clear messages
    clearMessages();
  }

  function initAddDialog()
  {
    g_sSourceId = '';
    g_sName = '';
    g_sLocationId = '';
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
    g_sDeviceId = tRow.id;
    g_sSourceId = tRow.parent_id;
    g_sName = tRow.name;
    g_sLocationId = tRow.room_id;
  }

  function onShownEditDialog()
  {
    $( '#source_path' ).focus();
    hideSpinner();
  }

  function onChangeControl( tEvent )
  {
    var tControl = $( tEvent.target );
    tControl.val( tControl.val().trim() );

    // Special handling for select2 objects
    if ( tControl.prop( 'tagName' ).toLowerCase() == 'select' )
    {
      $( '#select2-' + tControl.attr( 'id' ) + '-container' ).text( getSelect2Text( tControl ) );
    }

    // Set flag
    g_bChanged = true;
  }

  function getSelect2Text( tControl )
  {
    var sId = tControl.attr( 'id' );
    var sSelector = '#select2-' + sId + '-container';
    var sVal = tControl.val();
    var sText = ( sVal == 0 ) ? '' : $( '#' + sId + ' option[value="' + sVal + '"]' ).text();
    return sText;
  }

  function onSubmitEditDialog()
  {
    if ( g_bChanged && validateInput() )
    {
      var tPostData = new FormData();

      if ( g_sDeviceId )
      {
        tPostData.append( "id", g_sDeviceId );
      }

      tPostData.append( 'parent_id', $( '#source_path' ).val() );
      tPostData.append( 'name', $( '#name' ).val() );

      var sLocVal = $( '#loc_new' ).val();
      tPostData.append( 'room_id', ( ( sLocVal == null ) || ( sLocVal == '0' ) ) ? '' : sLocVal );

      var sLoc = getSelect2Text( $( '#loc_new' ) );
      tPostData.append( 'description', $( '#name' ).val() + ( sLoc ? ( ': ' + sLoc ) : '' ) );

      // Post request to server
      $.ajax(
        'devices/' + g_sAction + 'Device.php',
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
    var aMessages = [];

    if ( $( '#source_path' ).val() == null )
    {
      aMessages.push( 'Circuit is required' );
      $( '#source_path_container .selection' ).closest( '.form-group' ).addClass( 'has-error' );
    }

    showMessages( aMessages );
    return ( aMessages.length == 0 );
  }

  function submitEditDialogDone( tRsp, sStatus, tJqXhr )
  {
    location.reload();
  }

</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" />
<link rel="stylesheet" href="https://select2.github.io/select2-bootstrap-theme/css/select2-bootstrap.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
