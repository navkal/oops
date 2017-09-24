// Copyright 2017 Panel Spy.  All rights reserved.

var g_sRestoreId = null;


function initRestoreDialog( sRestoreId )
{
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
      initDeviceFields( tRow );
      break;

    case 'Location':
      initLocationFields( tRow );
      break;
  }

  // Initialize field labels
  makeFieldLabels( $( '.form-control,.input-group', '#restoreDialogForm' ) );
  $( '.form-control', '#restoreDialogForm' ).attr( 'placeholder', '' );

  // Set dialog 'shown' handler
  $( '#restoreDialog' ).off( 'shown.bs.modal' ).on( 'shown.bs.modal', initRestoreDialogFocus );

  // Customize responsive layout
  nLabelColumnWidth = 3;
  $( '.form-group>label', '#restoreDialogForm' ).removeClass().addClass( 'control-label' ).addClass( 'col-sm-' + nLabelColumnWidth );
  $( '.form-group>div', '#restoreDialogForm' ).removeClass().addClass( 'col-sm-' + ( 12 - nLabelColumnWidth ) );
}

function initDeviceFields( tRow )
{
  $( '#restoreFields' ).html( 'Device' );
}

function initLocationFields( tRow )
{
  tRow.loc_new = 'foooooooooooo foooooooooooo foooooooooooo foooooooooooo foooooooooooo foooooooooooo foooooooooooo ';
  tRow.loc_old = 'moooooooooooo';
  tRow.loc_descr = 'goooooooooooo';

  var sHtml = '';
  sHtml +=
     '<div class="form-group">' +
        '<label for="loc_new"></label>' +
        '<div>' +
          '<input type="text" class="form-control" id="loc_new" value="' + tRow.loc_new + '" readonly >' +
        '</div>' +
      '</div>';
  sHtml +=
     '<div class="form-group">' +
        '<label for="loc_old"></label>' +
        '<div>' +
          '<input type="text" class="form-control" id="loc_old" value="' + tRow.loc_old + '" readonly >' +
        '</div>' +
      '</div>';
  sHtml +=
     '<div class="form-group">' +
        '<label for="loc_descr"></label>' +
        '<div>' +
          '<input type="text" class="form-control" id="loc_descr" value="' + tRow.loc_descr + '" readonly >' +
        '</div>' +
      '</div>';

  $( '#restoreFields' ).html( sHtml );
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
