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
  var tRow = findSortableTableRow( g_sRemoveId );

  // Show what would be removed
  $( '#removeWhatLabel' ).text( g_tPropertyRules[tRow.remove_what] ? g_tPropertyRules[tRow.remove_what].label : g_sSortableTableEditWhat );
  $( '#removeWhat' ).val( tRow[tRow.remove_what] );

  // Set dialog 'shown' handler
  $( '#removeDialog' ).off( 'shown.bs.modal' ).on( 'shown.bs.modal', initRemoveDialogFocus );

  // Clear and optionally show comment field
  $( '#remove_comment' ).val( '' );
  if ( g_bShowRemoveComment )
  {
    $( '#removeCommentDiv label' ).text( g_tPropertyRules['remove_comment'].label )
    $( '#removeCommentDiv' ).show();
  }

  // Customize the dialog for the specific object type
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
    $( '#remove_comment' ).focus();
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
  tPostData.append( 'comment', $( '#remove_comment' ).val() );

  showSpinner();

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

function removeDone( tRsp, sStatus, tJqXhr )
{
  hideSpinner();

  $( '#removeDialog' ).modal( 'hide' );

  var aIds = tRsp.removed_object_ids;
  for ( var iIndex in aIds )
  {
    sId = aIds[iIndex];
    removeRow( sId );
  }
}

function removeRow( sId )
{
  var iRow = g_tRowMap[sId];

  // Purge row from data structures
  g_aSortableTableRows[iRow] = { id: null };

  for ( sLabel in g_tColumnMap )
  {
    g_tColumnMap[sLabel].cells[iRow] = '';
  }

  delete g_tRowMap[sId];

  // Remove row from display
  $( '#sortableTableBody tr[object_id="' + sId + '"]' ).remove();

  // Update the table
  $( '#sortableTable' ).trigger( 'update', [true] );

  // If table is empty, show empty message
  if ( $( '#sortableTableBody tr' ).length == 0 )
  {
    $( '#sortableTable' ).hide();
    $( '#sortableTableIsEmpty' ).show();
  }
}
