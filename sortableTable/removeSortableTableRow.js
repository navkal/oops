// Copyright 2017 Panel Spy.  All rights reserved.

var g_sRemoveId = null;
var g_sRemoveCodeFolder = null;
var g_bShowRemoveComment = null;

function initRemoveDialog( sRemoveId )
{
  g_sRemoveId = sRemoveId;

  // Set labels
  $( '#removeObjectLabel' ).text( 'Remove ' + g_sSortableTableEditWhat );
  $( '#removeFormSubmitProxy' ).text( 'Remove ' + g_sSortableTableEditWhat );

  // Find the selected row
  var iRow = 0;
  var tRow = null;
  do
  {
    tRow = g_aSortableTableRows[iRow];
    iRow ++
  }
  while( ( iRow < g_aSortableTableRows.length ) && ( tRow.id != g_sRemoveId ) );

  // Show what would be removed
  $( '#removeWhatLabel' ).text( g_tPropertyRules[tRow.remove_what] ? g_tPropertyRules[tRow.remove_what].label : g_sSortableTableEditWhat );
  $( '#removeWhat' ).val( tRow[tRow.remove_what] );

  // Set dialog 'shown' handler
  $( '#removeDialog' ).off( 'shown.bs.modal' ).on( 'shown.bs.modal', initRemoveDialogFocus );

  // Optionally show comment field
  if ( g_bShowRemoveComment )
  {
    $( '#removeCommentDiv' ).show();
  }

  if ( typeof customizeRemoveDialog === "function" )
  {
    customizeRemoveDialog( tRow );
  }
}

function initRemoveDialogFocus()
{
  // Focus on comment or remove button
  if ( g_bShowRemoveComment )
  {
    $( '#removeComment' ).focus();
  }
  else
  {
    $( '#removeFormSubmitProxy' ).focus();
  }
}

function removeObject()
{
  // Post request to server
  var tPostData = new FormData();
  tPostData.append( "id", g_sRemoveId );
  tPostData.append( 'comment', $( '#removeComment' ).val() );

  $.ajax(
    g_sRemoveCodeFolder + '/remove' + g_sSortableTableEditWhat + '.php',
    {
      type: 'POST',
      processData: false,
      contentType: false,
      dataType : 'json',
      data: tPostData
    }
  )
  .done( removeDone )
  .fail( handleAjaxError );
}

function removeDone()
{
  location.reload();
}
