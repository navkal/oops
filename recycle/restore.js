// Copyright 2017 Panel Spy.  All rights reserved.

var g_sRestoreId = null;
var g_tRow = null;

var g_tDropdowns =
{
  Panel: null,
  Transformer: null,
  Circuit: null,
  Device: null
};


function initRestoreDialog( sRestoreId )
{
  showSpinner();

  // Set dialog 'shown' handler
  $( '#restoreDialog' ).off( 'shown.bs.modal' ).on( 'shown.bs.modal', onShownRestoreDialog );

  // Initialize dialog box labels
  g_sRestoreId = sRestoreId;
  g_tRow = findSortableTableRow( g_sRestoreId );
  var sLabel = 'Restore ' + g_tRow.remove_object_type
  $( '#restoreDialogTitle,#restoreDialogFormSubmitProxy' ).text( sLabel );

  // Initialize common field values
  sTimestamp = formatTimestamp( g_tRow.timestamp );
  $( '#timestamp' ).val( sTimestamp );
  $( '#remove_comment' ).val( g_tRow.remove_comment );

  // Show fields applicable to the object type
  $( '#restoreFields' ).html( '' );
  switch( g_tRow.remove_object_type )
  {
    case 'Panel':
    case 'Transformer':
    case 'Circuit':
      initCircuitObjectFields();
      break;

    case 'Device':
      initDeviceFields();
      break;

    case 'Location':
      initLocationFields();
      break;
  }
}

function initCircuitObjectFields()
{
  var tFields = g_tRow.fields;

  console.log( JSON.stringify( tFields ) );

  var sHtml = '';
  sHtml +=
    '<div class="form-group">' +
      '<label for="parent_id"></label>' +
      '<div>' +
        '<select id="parent_id" class="form-control" style="width: 100%" ></select>' +
      '</div>' +
    '</div>';
  sHtml +=
    '<div class="form-group">' +
      '<label for="number"></label>' +
      '<div>' +
        '<input type="text" class="form-control" id="number" value="' + tFields.number + '" >' +
      '</div>' +
    '</div>';
  sHtml +=
    '<div class="form-group">' +
      '<label for="name"></label>' +
      '<div>' +
        '<input type="text" class="form-control" id="name" value="' + tFields.name + '" >' +
      '</div>' +
    '</div>';
  sHtml +=
    '<div class="form-group">' +
      '<label for="room_id"></label>' +
      '<div>' +
        '<select id="room_id" class="form-control" style="width: 100%" ></select>' +
      '</div>' +
    '</div>';

  $( '#restoreFields' ).html( sHtml );

  if ( ! g_tDropdowns[g_tRow.remove_object_type] )
  {
    getCircuitObjectDropdowns();
  }
  else
  {
    loadRestoreCircuitObjectDialog();
  }
}

function getCircuitObjectDropdowns()
{
    console.log( '===> Getting dropdowns for ' + g_tRow.remove_object_type );

    // Post request to server
    var tPostData = new FormData();
    tPostData.append( 'object_type', g_tRow.remove_object_type );

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
    .done( loadRestoreCircuitObjectDialog )
    .fail( handleAjaxError );
}

function loadRestoreCircuitObjectDialog( tRsp, sStatus, tJqXhr )
{
  if ( tRsp )
  {
    console.log( '===> Saving dropdowns for ' + g_tRow.remove_object_type );
    g_tDropdowns[g_tRow.remove_object_type] = tRsp;
  }

  finishInit();
}

function initDeviceFields()
{
  var tFields = g_tRow.fields;

  var sHtml = '';
  sHtml +=
    '<div class="form-group">' +
      '<label for="name"></label>' +
      '<div>' +
        '<input type="text" class="form-control" id="name" value="' + tFields.name + '" disabled >' +
      '</div>' +
    '</div>';
  sHtml +=
    '<div class="form-group">' +
      '<label for="source_path"></label>' +
      '<div>' +
        '<select id="source_path" class="form-control" style="width: 100%" ></select>' +
      '</div>' +
    '</div>';
  sHtml +=
    '<div class="form-group">' +
      '<label for="room_id"></label>' +
      '<div>' +
        '<select id="room_id" class="form-control" style="width: 100%" ></select>' +
      '</div>' +
    '</div>';

  $( '#restoreFields' ).html( sHtml );

  if ( ! g_tDropdowns[g_tRow.remove_object_type] )
  {
    getDeviceDropdowns();
  }
  else
  {
    loadRestoreDeviceDialog();
  }
}


function getDeviceDropdowns()
{
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
  .done( loadRestoreDeviceDialog )
  .fail( handleAjaxError );
}

function loadRestoreDeviceDialog( tRsp, sStatus, tJqXhr )
{
  if ( tRsp )
  {
    g_tDropdowns[g_tRow.remove_object_type] = tRsp;
  }

  // Generate the dropdowns
  var sHtmlSourcePath = '';
  var aSources = g_tDropdowns[g_tRow.remove_object_type].sources;
  for ( var iSource in aSources )
  {
    var tSource = aSources[iSource];
    sHtmlSourcePath += '<option value="' + tSource.id + '" >' + tSource.text + '</option>';
  }
  $( '#source_path' ).html( sHtmlSourcePath );
  $( '#source_path' ).val( g_tRow.fields.source_path );

  var sHtmlLocation = '<option value="0" >[none]</option>';
  var aLocations = g_tDropdowns[g_tRow.remove_object_type].locations;
  for ( var iLoc in aLocations )
  {
    var tLoc = aLocations[iLoc];
    sHtmlLocation += '<option value="' + tLoc.id + '" >' + tLoc.text + '</option>';
  }
  $( '#room_id' ).html( sHtmlLocation );
  $( '#room_id' ).val( g_tRow.fields.room_id );

  // Initialize select2 objects
  $.fn.select2.defaults.set( 'theme', 'bootstrap' );
  $( '#source_path' ).select2( { placeholder: 'Circuit' } );
  $( '#room_id' ).select2( { placeholder: 'Location' } );

  finishInit();
}


function initLocationFields()
{
  var tFields = g_tRow.fields;

  var sHtml = '';
  sHtml +=
     '<div class="form-group">' +
        '<label for="loc_new"></label>' +
        '<div>' +
          '<input type="text" class="form-control" id="loc_new" value="' + tFields.loc_new + '" disabled >' +
        '</div>' +
      '</div>';
  sHtml +=
     '<div class="form-group">' +
        '<label for="loc_old"></label>' +
        '<div>' +
          '<input type="text" class="form-control" id="loc_old" value="' + tFields.loc_old + '" disabled >' +
        '</div>' +
      '</div>';
  sHtml +=
     '<div class="form-group">' +
        '<label for="loc_descr"></label>' +
        '<div>' +
          '<input type="text" class="form-control" id="loc_descr" value="' + tFields.loc_descr + '" disabled >' +
        '</div>' +
      '</div>';

  $( '#restoreFields' ).html( sHtml );

  finishInit();
}

function finishInit()
{
  // Initialize field labels
  makeFieldLabels( $( '.form-control,.input-group', '#restoreDialogForm' ) );
  $( '.form-control', '#restoreDialogForm' ).attr( 'placeholder', '' );

  // Customize responsive layout
  nLabelColumnWidth = 3;
  $( '.form-group>label', '#restoreDialogForm' ).removeClass().addClass( 'control-label' ).addClass( 'col-sm-' + nLabelColumnWidth );
  $( '.form-group>div', '#restoreDialogForm' ).removeClass().addClass( 'col-sm-' + ( 12 - nLabelColumnWidth ) );

  // Set change handler
  resetChangeHandler();

  // Clear messages
  clearMessages();
}

function onShownRestoreDialog()
{
  // Allow user to select text in select2 rendering
  allowSelect2SelectText( 'source_path' );
  allowSelect2SelectText( 'room_id' );

  // Set handler to focus on select2 object after user sets value
  setSelect2CloseHandler();

  // Focus on first editable field or restore button
  var tEditable = $( '#restoreDialogForm .form-control:not([disabled])' );

  if ( tEditable.length > 0 )
  {
    tEditable[0].focus();
  }
  else
  {
    $( '#restoreDialogFormSubmitProxy' ).focus();
  }

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

    // Special handling for select2 objects
    if ( tControl.prop( 'tagName' ).toLowerCase() == 'select' )
    {
      var tSelect2 = $( '#select2-' + sId + '-container' );
      tSelect2.text( getSelect2Text( tControl ) );

      allowSelect2SelectText( sId );
    }
  }
}

function onSubmitRestoreDialog()
{
  if ( validateInput() )
  {
    // Post request to server
    var tPostData = new FormData();
    tPostData.append( 'id', g_sRestoreId );

    switch( g_tRow.remove_object_type )
    {
      case 'Device':
        tPostData.append( 'parent_id', $( '#source_path' ).val() );
        var sLocVal = $( '#room_id' ).val();
        tPostData.append( 'room_id', ( ( sLocVal == null ) || ( sLocVal == '0' ) ) ? '' : sLocVal );
        var sLoc = getSelect2Text( $( '#room_id' ) );
        tPostData.append( 'description', $( '#name' ).val() + ( sLoc ? ( ': ' + sLoc ) : '' ) );
        break;

      case 'Location':
        // Do nothing
        break;
    }

    $.ajax(
      'recycle/restoreObject.php',
      {
        type: 'POST',
        processData: false,
        contentType: false,
        dataType : 'json',
        data: tPostData
      }
    )
    .done( restoreDone )
    .fail( handleAjaxError );
  }
}

function validateInput()
{
  clearMessages();
  var aMessages = [];

  switch( g_tRow.remove_object_type )
  {
    case 'Device':
      if ( $( '#source_path' ).val() == null )
      {
        aMessages.push( 'Circuit is required' );
        $( '#source_path' ).closest( '.form-group' ).addClass( 'has-error' );
      }
      break;

    case 'Location':
      // Do nothing
      break;
  }

  showMessages( aMessages );
  return ( aMessages.length == 0 );
}

function restoreDone( tRsp, sStatus, tJqXhr )
{
  location.reload();
}
