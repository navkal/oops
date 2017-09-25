// Copyright 2017 Panel Spy.  All rights reserved.

var g_sRestoreId = null;
var g_tDeviceDropdowns = null;


function initRestoreDialog( sRestoreId )
{
  showSpinner();

  // Set dialog 'shown' handler
  $( '#restoreDialog' ).off( 'shown.bs.modal' ).on( 'shown.bs.modal', onShownRestoreDialog );

  // Initialize dialog box labels
  g_sRestoreId = sRestoreId;
  var tRow = findSortableTableRow( g_sRestoreId );
  var sLabel = 'Restore ' + tRow.remove_object_type
  $( '#restoreDialogTitle,#restoreDialogFormSubmitProxy' ).text( sLabel );

  // Initialize common field values
  sTimestamp = formatTimestamp( tRow.timestamp );
  $( '#timestamp' ).val( sTimestamp );
  $( '#remove_comment' ).val( tRow.remove_comment );

  // Show fields applicable to the object type
  $( '#restoreFields' ).html( '' );
  switch( tRow.remove_object_type )
  {
    case 'Device':
      initDeviceFields( tRow.fields );
      break;

    case 'Location':
      initLocationFields( tRow.fields );
      break;
  }
}

function initDeviceFields( tFields )
{
  var sHtml = '';
  sHtml +=
    '<div class="form-group">' +
      '<label for="name"></label>' +
      '<div>' +
        '<input type="text" class="form-control" id="name" value="' + tFields.name + '" readonly >' +
      '</div>' +
    '</div>';
  sHtml +=
    '<div class="form-group">' +
      '<label for="source_path"></label>' +
      '<div>' +
        '<input type="text" class="form-control" id="source_path" value="' + tFields.source_path + '" readonly >' +
      '</div>' +
    '</div>';
  sHtml +=
    '<div class="form-group">' +
      '<label for="location_digest"></label>' +
      '<div>' +
        '<input type="text" class="form-control" id="location_digest" value="' + tFields.location_digest + '" readonly >' +
      '</div>' +
    '</div>';
  sHtml +=
    '<div class="form-group">' +
      '<label for="restore_circuit"></label>' +
      '<div>' +
        '<select id="restore_circuit" class="form-control" style="width: 100%" ></select>' +
      '</div>' +
    '</div>';
  sHtml +=
    '<div class="form-group">' +
      '<label for="restore_location">Restore Location</label>' +
      '<div>' +
        '<select id="restore_location" class="form-control" style="width: 100%" ></select>' +
      '</div>' +
    '</div>';

  $( '#restoreFields' ).html( sHtml );

  if ( ! g_tDeviceDropdowns )
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
    g_tDeviceDropdowns = tRsp;
    console.log( '==> Source count: ' + g_tDeviceDropdowns.sources.length );
    console.log( '==> Location count: ' + g_tDeviceDropdowns.locations.length );
  }

  // Generate the dropdowns
  var sHtmlSourcePath = '';
  var aSources = g_tDeviceDropdowns.sources;
  for ( var iSource in aSources )
  {
    var tSource = aSources[iSource];
    sHtmlSourcePath += '<option value="' + tSource.id + '" >' + tSource.text + '</option>';
  }
  $( '#restore_circuit' ).html( sHtmlSourcePath );

  var sHtmlLocation = '<option value="0" >[none]</option>';
  var aLocations = g_tDeviceDropdowns.locations;
  for ( var iLoc in aLocations )
  {
    var tLoc = aLocations[iLoc];
    sHtmlLocation += '<option value="' + tLoc.id + '" >' + tLoc.text + '</option>';
  }
  $( '#restore_location' ).html( sHtmlLocation );

  // Initialize select2 objects
  $.fn.select2.defaults.set( 'theme', 'bootstrap' );
  $( '#restore_circuit' ).select2( { placeholder: 'Circuit' } );
  $( '#restore_location' ).select2( { placeholder: 'Location' } );

  finishInit();
}


function initLocationFields( tFields )
{
  var sHtml = '';
  sHtml +=
     '<div class="form-group">' +
        '<label for="loc_new"></label>' +
        '<div>' +
          '<input type="text" class="form-control" id="loc_new" value="' + tFields.loc_new + '" readonly >' +
        '</div>' +
      '</div>';
  sHtml +=
     '<div class="form-group">' +
        '<label for="loc_old"></label>' +
        '<div>' +
          '<input type="text" class="form-control" id="loc_old" value="' + tFields.loc_old + '" readonly >' +
        '</div>' +
      '</div>';
  sHtml +=
     '<div class="form-group">' +
        '<label for="loc_descr"></label>' +
        '<div>' +
          '<input type="text" class="form-control" id="loc_descr" value="' + tFields.loc_descr + '" readonly >' +
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
  allowSelect2SelectText( 'restore_circuit' );
  allowSelect2SelectText( 'restore_location' );

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

    // Special handling for select2 objects
    if ( tControl.prop( 'tagName' ).toLowerCase() == 'select' )
    {
      var tSelect2 = $( '#select2-' + sId + '-container' );
      tSelect2.text( getSelect2Text( tControl ) );

      allowSelect2SelectText( sId );
    }
  }
}


function initRestoreDialogFocus()
{
  // Focus on path field or restore button
  if ( false )
  {
  }
  else
  {
    $( '#restoreFormSubmitProxy' ).focus();
  }
}

function onSubmitRestoreDialog()
{
  // Post request to server
  var tPostData = new FormData();
  tPostData.append( "id", g_sRestoreId );

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

function restoreDone( tRsp, sStatus, tJqXhr )
{
  alert( JSON.stringify( tRsp ) );
  location.reload();
}
