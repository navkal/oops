// Copyright 2017 Panel Spy.  All rights reserved.

var g_sRestoreId = null;


function initRestoreDialog( sRestoreId )
{
  g_sRestoreId = sRestoreId;
  makeFieldLabels( $( '.form-control,.input-group', '#restoreForm' ) );

  // Find the selected row
  var tRow = findSortableTableRow( g_sRestoreId );

  // Set labels
  var sLabel = 'Restore ' + tRow.remove_object_type
  $( '#restoreObjectLabel,#restoreFormSubmitProxy' ).text( sLabel );

  // Show what would be restored

  // Set dialog 'shown' handler
  $( '#restoreDialog' ).off( 'shown.bs.modal' ).on( 'shown.bs.modal', initRestoreDialogFocus );
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
