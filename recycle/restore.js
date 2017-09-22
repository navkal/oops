// Copyright 2017 Panel Spy.  All rights reserved.

var g_sRestoreId = null;


function initRestoreDialog( sRestoreId )
{
  // Initialize field labels
  makeFieldLabels( $( '.form-control,.input-group', '#restoreDialogForm' ) );
  $( '.form-control', '#restoreDialogForm' ).attr( 'placeholder', '' );

  // Initialize operation labels
  g_sRestoreId = sRestoreId;
  var tRow = findSortableTableRow( g_sRestoreId );
  var sLabel = 'Restore ' + tRow.remove_object_type
  $( '#restoreDialogTitle,#restoreDialogFormSubmitProxy' ).text( sLabel );

  // Initialize field values
  sTimestamp = formatTimestamp( tRow.timestamp );
  $( '#timestamp' ).val( sTimestamp );
  $( '#remove_object_origin' ).val( tRow.remove_object_origin );
  $( '#remove_comment' ).val( tRow.remove_comment );

  // Set dialog 'shown' handler
  $( '#restoreDialog' ).off( 'shown.bs.modal' ).on( 'shown.bs.modal', initRestoreDialogFocus );

  // Customize responsive layout
  nLabelColumnWidth = 3;
  $( '.form-group>label', '#restoreDialogForm' ).removeClass().addClass( 'control-label' ).addClass( 'col-sm-' + nLabelColumnWidth );
  $( '.form-group>div', '#restoreDialogForm' ).removeClass().addClass( 'col-sm-' + ( 12 - nLabelColumnWidth ) );


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

function restoreObject()
{
  // Post request to server
  var tPostData = new FormData();
  tPostData.append( "id", g_sRestoreId );

  $.ajax(
    'sortableTable/restoreSortableTableRow.php',
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

function restoreDone()
{
  location.reload();
}
