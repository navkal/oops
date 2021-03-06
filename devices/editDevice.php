<!-- Copyright 2018 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";
?>

<!-- Edit Device dialog -->
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
                  <input type="text" class="form-control" id="name" maxlength="40" required>
                </div>
              </div>
              <div class="form-group">
                <label for="room_id"></label>
                <div id="room_id_container" >
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
  var g_sDeviceId = null;
  var g_sSourceId = null;
  var g_sName = null;
  var g_sLocationId = null;

  var g_sSpinnerHideShow = '#editDialog .modal-header, #editDialog .modal-body, #editDialog .modal-footer'

  function onShowEditDialog()
  {
    showSpinner( g_sSpinnerHideShow );

    $( '#source_path_container' ).html( '<select id="source_path" class="form-control" style="width: 100%" ></select>' );
    $( '#room_id_container' ).html( '<select id="room_id" class="form-control" style="width: 100%" ></select>' );

    getDropdowns();
  }

  var g_iStartGetTime = null;
  function getDropdowns()
  {
    // Post request to server
    var tPostData = new FormData();
    tPostData.append( "postSecurity", "" );

    g_iStartGetTime = Date.now();

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
    console.log( '=> Time to retrieve dropdowns: ' + ( Date.now() - g_iStartGetTime ) + ' ms' );
    var iStartLoadTime = Date.now();

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
    $( '#room_id' ).html( sHtmlLocation );

    loadEditDialog()

    console.log( '=> Time to load dialog: ' + ( Date.now() - iStartLoadTime ) + ' ms' );
  }

  function loadEditDialog()
  {
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
    $( '#source_path' ).val( g_sSourceId );
    $( '#name' ).val( g_sName );
    $( '#room_id' ).val( g_sLocationId );

    // Initialize select2 objects
    $.fn.select2.defaults.set( 'theme', 'bootstrap' );
    $( '#source_path' ).select2( { placeholder: 'Circuit' } );
    $( '#room_id' ).select2( { placeholder: 'Location' } );

    // Allow user to select text in select2 rendering
    allowSelect2SelectText( 'source_path' );
    allowSelect2SelectText( 'room_id' );

    // Set handler to focus on select2 object after user sets value
    setSelect2CloseHandler();

    // Set change handler
    resetChangeHandler();

    // Clear messages
    clearMessages();

    hideSpinner( g_sSpinnerHideShow );
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
    var tRow = findSortableTableRow( g_sUpdateTarget );

    // Save values of selected row
    g_sDeviceId = tRow.id;
    g_sSourceId = tRow.parent_id;
    g_sName = htmlentities_undo( tRow.name );
    g_sLocationId = tRow.room_id;
  }

  function onShownEditDialog()
  {
    $( '#source_path' ).focus();
  }

  function onChangeControl( tEvent )
  {
    var tControl = $( tEvent.target );
    tControl.val( tControl.val().trim() );

    // Special handling for select2 objects
    if ( tControl.prop( 'tagName' ).toLowerCase() == 'select' )
    {
      var sId = tControl.attr( 'id' );
      var tSelect2 = $( '#select2-' + sId + '-container' );
      tSelect2.text( getSelect2Text( tControl ) );

      // Allow user to select text in select2 rendering
      allowSelect2SelectText( sId );
    }

    // Set flag
    g_bChanged = true;
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
      tPostData.append( 'name', htmlentities( $( '#name' ).val() ) );

      var sLocVal = $( '#room_id' ).val();
      tPostData.append( 'room_id', ( ( sLocVal == null ) || ( sLocVal == '0' ) ) ? '' : sLocVal );

      showSpinner();

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
    var aMessages = validateDeviceInput();
    showMessages( aMessages );
    return ( aMessages.length == 0 );
  }
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" />
<link rel="stylesheet" href="https://select2.github.io/select2-bootstrap-theme/css/select2-bootstrap.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
