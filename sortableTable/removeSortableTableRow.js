// Copyright 2017 Panel Spy.  All rights reserved.

var g_sRemoveTarget = null;

function initRemoveDialog( sRemoveTarget )
{
  g_sRemoveTarget = sRemoveTarget;

  // Set labels
  $( '#removeObjectLabel' ).text( 'Remove ' + g_sSortableTableEditWhat );
  $( '#removeFormSubmitProxy' ).text( 'Remove ' + g_sSortableTableEditWhat );
  $( '#removeWhatLabel' ).text( g_sSortableTableEditWhat );

  // Find the selected row
  var iRow = 0;
  var tRow = null;
  do
  {
    tRow = g_aSortableTableRows[iRow];
    iRow ++
  }
  while( ( iRow < g_aSortableTableRows.length ) && ( tRow.id != sRemoveTarget ) );

  // Show what would be removed
  $( '#removeWhat' ).val( tRow.remove_what );

  // Set dialog 'shown' handler
  $( '#removeDialog' ).off( 'shown.bs.modal' ).on( 'shown.bs.modal', initRemoveDialogFocus );
}

function initRemoveDialogFocus()
{
  $( '#removeFormSubmitProxy' ).focus();
}

function removeDone()
{
  location.reload();
}
