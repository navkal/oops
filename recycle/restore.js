// Copyright 2017 Panel Spy.  All rights reserved.

var g_sRestoreId = null;
var g_tRestoreRow = null;
var g_sParentIdId = null;
var g_aParents = null;


function initRestoreDialog( sRestoreId )
{
  g_sRestoreId = sRestoreId;

  g_tRestoreRow = findSortableTableRow( g_sRestoreId );
  $( '#restoreDialogTitle,#restoreDialogFormSubmitProxy' ).text( 'Restore ' + g_tRestoreRow.remove_object_type );
  $( '#timestamp' ).val( formatTimestamp( g_tRestoreRow.timestamp ) );
  $( '#remove_comment' ).val( g_tRestoreRow.remove_comment );
  $( '#restoreFields' ).html( '' );
  $( '#restore_comment' ).val( '' );

  showSpinner();

  getRestoreDropdowns();
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
  .done( loadCustomFields )
  .fail( handleAjaxError );
}

function loadCustomFields( tRsp, sStatus, tJqXhr )
{
  // Show fields applicable to the object type
  switch( g_tRestoreRow.remove_object_type )
  {
    case 'Panel':
    case 'Transformer':
    case 'Circuit':
      g_sParentIdId = 'parent_path';
      initDistributionObjectFields();
      makeDropdowns( tRsp );
      break;

    case 'Device':
      g_sParentIdId = 'source_path';
      initDeviceFields();
      makeDropdowns( tRsp );
      break;

    case 'Location':
      initLocationFields();
      break;
  }

  finishInit();
}

function initDistributionObjectFields()
{
  var tFields = g_tRestoreRow.fields;

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
      '<label for="phase_b_tail"></label>' +
      '<div id="phase_b_tail_container" >' +
        '<select id="phase_b_tail" class="form-control" style="width: 100%" ></select>' +
      '</div>' +
    '</div>';
  sHtml +=
    '<div class="form-group">' +
      '<label for="phase_c_tail"></label>' +
      '<div id="phase_c_tail_container" >' +
        '<select id="phase_c_tail" class="form-control" style="width: 100%" ></select>' +
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
  var tFields = g_tRestoreRow.fields;

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

function makeDropdowns( tRsp )
{
  // Generate parent dropdown
  var sHtmlParentPath = '';

  switch( g_tRestoreRow.remove_object_type )
  {
    case 'Panel':
      g_aParents = tRsp.panel_parents;
      break;
    case 'Transformer':
      g_aParents = tRsp.transformer_parents;
      break;
    case 'Circuit':
      g_aParents = tRsp.circuit_parents;
      break;
    case 'Device':
      g_aParents = tRsp.device_parents;
      break;
  }

  for ( var iParent in g_aParents )
  {
    var tParent = g_aParents[iParent];
    var bParentAllowed = null;

    switch( g_tRestoreRow.remove_object_type )
    {
      case 'Panel':
        // --> KLUDGE: Assume that there are only two voltage levels and the higher voltage has the lower ID -->
        bParentAllowed = ( tParent.object_type == 'Transformer' ) ? ( tParent.voltage_id < g_tRestoreRow.fields.voltage_id ) : ( tParent.voltage_id == g_tRestoreRow.fields.voltage_id  );
        // <-- KLUDGE: Assume that there are only two voltage levels and the higher voltage has the lower ID <--
        break;
      case 'Transformer':
        bParentAllowed = ( tParent.voltage_id == g_tRestoreRow.fields.voltage_id );
        break;
      case 'Circuit':
        bParentAllowed = ( tParent.voltage_id == g_tRestoreRow.fields.voltage_id );
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
  $( '#' + g_sParentIdId ).val( g_tRestoreRow.fields.parent_id );

  makePhaseDropdowns( g_aParents, g_tRestoreRow.fields.parent_id, g_tRestoreRow.fields.phase_b_parent_id )
  $( '#phase_b_tail' ).val( g_tRestoreRow.fields.phase_b_parent_id );
  $( '#phase_c_tail' ).val( g_tRestoreRow.fields.phase_c_parent_id );

  // Generate location dropdown
  var sHtmlLocation = ( g_tRestoreRow.remove_object_type == 'Device' ) ? '<option value="0" >[none]</option>' : '';
  var aLocations = tRsp.locations;
  for ( var iLoc in aLocations )
  {
    var tLoc = aLocations[iLoc];
    sHtmlLocation += '<option value="' + tLoc.id + '" >' + tLoc.text + '</option>';
  }
  $( '#room_id' ).html( sHtmlLocation );
  $( '#room_id' ).val( g_tRestoreRow.fields.room_id );

  // Initialize select2 objects
  $( '#phase_b_tail_container, #phase_c_tail_container' ).closest( '.form-group' ).css( 'display', ( g_tRestoreRow.remove_object_type == 'Circuit' ) ? 'none' : 'block' );
  $.fn.select2.defaults.set( 'theme', 'bootstrap' );
  $( '#' + g_sParentIdId ).select2( { placeholder: ( g_tRestoreRow.remove_object_type == 'Device' ) ? 'Circuit' : 'Parent' } );
  $( '#phase_b_tail' ).select2( { placeholder: g_tPropertyRules['phase_b_tail'].label } );
  $( '#phase_c_tail' ).select2( { placeholder: g_tPropertyRules['phase_c_tail'].label } );
  $( '#room_id' ).select2( { placeholder: 'Location' } );
}

function initLocationFields()
{
  var tFields = g_tRestoreRow.fields;

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

  // Turn off autocomplete
  $( 'input', '#restoreDialogForm' ).attr( 'autocomplete', 'off' );

  // Set change handler
  resetChangeHandler();

  // Clear messages
  clearMessages();

  // Allow user to select text in select2 rendering
  allowSelect2SelectText( g_sParentIdId );
  allowSelect2SelectText( 'phase_b_tail' );
  allowSelect2SelectText( 'phase_c_tail' );
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

    // Special handling for PTC Name field
    switch( g_tRestoreRow.remove_object_type )
    {
      case 'Panel':
      case 'Transformer':
      case 'Circuit':

        switch( sId )
        {
          case 'parent_path':
            makePhaseDropdowns( g_aParents, sVal, $( '#phase_b_tail' ).val() );
            break;

          case 'phase_b_tail':
            updatePhaseCDropdown( $( '#phase_b_tail' ).val() );
            break;

          case 'name':
            tControl.val( sVal.toUpperCase() );
            break;
        }

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
}

function onSubmitRestoreDialog()
{
  if ( validateInput() )
  {
    // Post request to server
    var tPostData = new FormData();
    tPostData.append( 'id', g_sRestoreId );

    switch( g_tRestoreRow.remove_object_type )
    {
      case 'Panel':
      case 'Transformer':
      case 'Circuit':

        var sNumber = $( '#number' ).val();
        var sName = $( '#name' ).val();
        var sHyphen = ( sNumber && sName ) ? '-' : '';
        tPostData.append( 'tail', sNumber + sHyphen + sName );

        var sPhaseVal = $( '#phase_b_tail' ).val();
        tPostData.append( 'phase_b_parent_id', ( ( sPhaseVal == null ) || ( sPhaseVal == '0' ) ) ? '' : sPhaseVal );

        var sPhaseVal = $( '#phase_c_tail' ).val();
        tPostData.append( 'phase_c_parent_id', ( ( sPhaseVal == null ) || ( sPhaseVal == '0' ) ) ? '' : sPhaseVal );

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

    var sComment = $( '#restore_comment' ).val();
    tPostData.append( 'comment', sComment );

    showSpinner();

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

  switch( g_tRestoreRow.remove_object_type )
  {
    case 'Panel':
    case 'Transformer':
    case 'Circuit':
      aMessages = validateDistributionObjectInput( g_tRestoreRow.remove_object_type );
      break;

    case 'Device':
      aMessages = validateDeviceInput();
      break;

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
  hideSpinner();

  if ( tRsp.messages.length )
  {
    // Show error messages
    showMessages( tRsp.messages );
    highlightErrors( tRsp.selectors );
  }
  else
  {
    // Hide the dialog
    $( '#restoreDialog' ).modal( 'hide' );

    // Remove the row from the table
    removeRow( tRsp.id, true );
  }
}
