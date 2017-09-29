// Copyright 2017 Panel Spy.  All rights reserved.

var g_sRestoreId = null;
var g_tRow = null;
var g_tDropdowns = null;
var g_sParentIdId = null;


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
      g_sParentIdId = 'parent_path';
      initCircuitObjectFields();
      makeDropdowns();
      break;

    case 'Device':
      g_sParentIdId = 'source_path';
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

  var sHtml = '';
  sHtml +=
    '<div class="form-group">' +
      '<label for="' + g_sParentIdId + '"></label>' +
      '<div>' +
        '<select id="' + g_sParentIdId + '" class="form-control" style="width: 100%" ></select>' +
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
      '<label for="' + g_sParentIdId + '"></label>' +
      '<div>' +
        '<select id="' + g_sParentIdId + '" class="form-control" style="width: 100%" ></select>' +
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
    var bParentAllowed = null;

    switch( g_tRow.remove_object_type )
    {
      case 'Panel':
        // --> KLUDGE: Assume that there are only two voltage levels and the higher voltage has the lower ID -->
        bParentAllowed = ( tParent.object_type == 'Transformer' ) ? ( tParent.voltage_id < g_tRow.fields.voltage_id ) : ( tParent.voltage_id == g_tRow.fields.voltage_id  );
        // <-- KLUDGE: Assume that there are only two voltage levels and the higher voltage has the lower ID <--
        break;
      case 'Transformer':
        bParentAllowed = ( tParent.voltage_id == g_tRow.fields.voltage_id );
        break;
      case 'Circuit':
        bParentAllowed = ( tParent.voltage_id == g_tRow.fields.voltage_id );
        break;
      case 'Device':
        bParentAllowed = true;
        break;
    }

    if ( bParentAllowed )
    {
      sHtmlParentPath += '<option value="' + tParent.id + '" >' + tParent.text + '</option>';
    }
  }
  $( '#' + g_sParentIdId ).html( sHtmlParentPath );
  $( '#' + g_sParentIdId ).val( g_tRow.fields.parent_id );


  // Generate location dropdown
  var sHtmlLocation = ( g_tRow.remove_object_type == 'Device' ) ? '<option value="0" >[none]</option>' : '';
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
  $( '#' + g_sParentIdId ).select2( { placeholder: ( g_tRow.remove_object_type == 'Device' ) ? 'Circuit' : 'Parent' } );
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
  allowSelect2SelectText( g_sParentIdId );
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
      case 'Panel':
      case 'Transformer':
      case 'Circuit':

        var sNumber = $( '#number' ).val();
        var sName = $( '#name' ).val();
        var sHyphen = ( sNumber && sName ) ? '-' : '';
        tPostData.append( 'tail', sNumber + sHyphen + sName );

        // NO BREAK !!!  Continue into 'Device' case...

      case 'Device':
        tPostData.append( 'parent_id', $( '#' + g_sParentIdId ).val() );
        var sLocVal = $( '#room_id' ).val();
        tPostData.append( 'room_id', ( ( sLocVal == null ) || ( sLocVal == '0' ) ) ? '' : sLocVal );
        break;

      case 'Location':
        // Do nothing
        break;
    }

    $.ajax(
      'recycle/restoreRemovedObject.php',
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

  // Require parent
  switch( g_tRow.remove_object_type )
  {
    case 'Panel':
    case 'Transformer':
    case 'Circuit':
      if ( $( '#' + g_sParentIdId ).val() == null )
      {
        aMessages.push( 'Parent is required' );
        $( '#' + g_sParentIdId ).closest( '.form-group' ).addClass( 'has-error' );
      }
      break;

    case 'Device':
      if ( $( '#' + g_sParentIdId ).val() == null )
      {
        aMessages.push( 'Circuit is required' );
        $( '#' + g_sParentIdId ).closest( '.form-group' ).addClass( 'has-error' );
      }
      break;

    case 'Location':
    default:
      // Do nothing
      break;
  }

  // Require elements of tail
  var sNumber = $( '#number' ).val();
  var sName = $( '#name' ).val();
  switch( g_tRow.remove_object_type )
  {
    case 'Panel':
    case 'Transformer':
        if ( ! sName )
        {
          aMessages.push( 'Name is required' );
          $( '#name' ).closest( '.form-group' ).addClass( 'has-error' );
        }
      break;

    case 'Circuit':
        if ( ! sNumber && ! sName )
        {
          aMessages.push( 'Number or Name is required' );
          $( '#number' ).closest( '.form-group' ).addClass( 'has-error' );
          $( '#name' ).closest( '.form-group' ).addClass( 'has-error' );
        }
      break;

    case 'Device':
    case 'Location':
    default:
      // Do nothing
      break;
  }

  // Check tail syntax and require location
  switch( g_tRow.remove_object_type )
  {
    case 'Panel':
    case 'Transformer':
    case 'Circuit':

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

      if ( $( '#room_id' ).val() == null )
      {
        aMessages.push( 'Location is required' );
        $( '#room_id' ).closest( '.form-group' ).addClass( 'has-error' );
      }

      break;

    case 'Device':
    case 'Location':
    default:
      // Do nothing
      break;
  }

  showMessages( aMessages );
  return ( aMessages.length == 0 );
}

function restoreDone( tRsp, sStatus, tJqXhr )
{
  alert( JSON.stringify( tRsp ) );
  location.reload();
}
