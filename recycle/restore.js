// Copyright 2017 Panel Spy.  All rights reserved.

var g_sRestoreId = null;
var g_tRow = null;

var g_tDropdowns = null;


function initRestoreDialog( sRestoreId )
{
  g_sRestoreId = sRestoreId;

  showSpinner();

  if ( ! g_tDropdowns )
  {
    getRestoreDropdowns();
  }
  else
  {
    loadRestoreDialog();
  }
}

function getRestoreDropdowns()
{
  console.log( '==========> Requesting dropdowns' );

  // Post request to server
  var tPostData = new FormData();
  tPostData.append( "postSecurity", "" );

  $.ajax(
    "recycle/getRestoreDropdowns.php",
    {
      type: 'POST',
      processData: false,
      contentType: false,
      dataType : 'json',
      data: tPostData
    }
  )
  .done( loadRestoreDialog )
  .fail( handleAjaxError );
}

function loadRestoreDialog( tRsp, sStatus, tJqXhr )
{
  if ( tRsp )
  {
    console.log( '==========> Saving dropdowns' );
    g_tDropdowns = tRsp;
  }

  // Set dialog 'shown' handler
  $( '#restoreDialog' ).off( 'shown.bs.modal' ).on( 'shown.bs.modal', onShownRestoreDialog );

  // Set operation labels
  g_tRow = findSortableTableRow( g_sRestoreId );
  var sLabel = 'Restore ' + g_tRow.remove_object_type;
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
      makeDropdowns();
      break;

    case 'Device':
      initDeviceFields();
      makeDropdowns();
      break;

    case 'Location':
      initLocationFields();
      break;
  }

  finishInit();
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
      '<label for="parent_id"></label>' +
      '<div>' +
        '<select id="parent_id" class="form-control" style="width: 100%" ></select>' +
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
}

function makeDropdowns()
{
  // Generate parent dropdown
  var sHtmlParentPath = '';
  var aParents = null;
  switch( g_tRow.remove_object_type )
  {
    case 'Panel':
      aParents = g_tDropdowns.panel_parents;
      break;
    case 'Transformer':
      aParents = g_tDropdowns.transformer_parents;
      break;
    case 'Circuit':
      aParents = g_tDropdowns.circuit_parents;
      break;
    case 'Device':
      aParents = g_tDropdowns.device_parents;
      break;
  }

  for ( var iParent in aParents )
  {
    var tParent = aParents[iParent];
    sHtmlParentPath += '<option value="' + tParent.id + '" >' + tParent.text + '</option>';
  }
  $( '#parent_id' ).html( sHtmlParentPath );
  $( '#parent_id' ).val( g_tRow.fields.parent_id );


  // Generate location dropdown
  var sHtmlLocation = '<option value="0" >[none]</option>';
  var aLocations = g_tDropdowns.locations;
  for ( var iLoc in aLocations )
  {
    var tLoc = aLocations[iLoc];
    sHtmlLocation += '<option value="' + tLoc.id + '" >' + tLoc.text + '</option>';
  }
  $( '#room_id' ).html( sHtmlLocation );
  $( '#room_id' ).val( g_tRow.fields.room_id );

  // Initialize select2 objects
  $.fn.select2.defaults.set( 'theme', 'bootstrap' );
  $( '#parent_id' ).select2( { placeholder: 'Circuit' } );
  $( '#room_id' ).select2( { placeholder: 'Location' } );
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
  allowSelect2SelectText( 'parent_id' );
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
  if( ['Panel','Transformer','Circuit'].includes( g_tRow.remove_object_type ) ){alert( 'Restore operation not available.' );  return;}





  if ( validateInput() )
  {
    // Post request to server
    var tPostData = new FormData();
    tPostData.append( 'id', g_sRestoreId );

    switch( g_tRow.remove_object_type )
    {
      case 'Device':
        tPostData.append( 'parent_id', $( '#parent_id' ).val() );
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
      if ( $( '#parent_id' ).val() == null )
      {
        aMessages.push( 'Circuit is required' );
        $( '#parent_id' ).closest( '.form-group' ).addClass( 'has-error' );
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
