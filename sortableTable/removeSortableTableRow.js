// Copyright 2018 Panel Spy.  All rights reserved.

var g_sRemoveId = null;
var g_sRemoveCodeFolder = null;
var g_bShowRemoveComment = null;

function initRemoveDialog( sRemoveId )
{
  // Clear messages
  clearMessages( '#removeMessages', '#removeMessageList' );

  showSpinner();

  g_sRemoveId = sRemoveId;

  // Set labels
  $( '#removeObjectLabel' ).text( 'Remove ' + g_sSortableTableEditWhat );
  $( '#removeFormSubmitProxy' ).text( 'Remove ' + g_sSortableTableEditWhat );

  // Find the selected row
  var tRow = findSortableTableRow( g_sRemoveId );

  // Show what would be removed
  $( '#removeWhatLabel' ).text( g_tPropertyRules[tRow.remove_what] ? g_tPropertyRules[tRow.remove_what].label : g_sSortableTableEditWhat );
  $( '#removeWhat' ).val( tRow[tRow.remove_what] );

  // Clear and optionally show comment field
  $( '#remove_comment' ).val( '' );
  if ( g_bShowRemoveComment )
  {
    $( '#removeCommentDiv label' ).text( htmlentities_undo( g_tPropertyRules['remove_comment'].label ) );
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

  hideSpinner();
}

function removeObject()
{
  clearMessages( '#removeMessages', '#removeMessageList' );

  // Post request to server
  var tPostData = new FormData();
  tPostData.append( "id", g_sRemoveId );
  tPostData.append( 'comment', htmlentities( $( '#remove_comment' ).val() ) );

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

  if ( tRsp.messages.length )
  {
    // Show error messages
    showMessages( tRsp.messages, '#removeMessages', '#removeMessageList' );
    highlightErrors( tRsp.selectors );
  }
  else
  {
    closeChildWindows();

    $( '#removeDialog' ).modal( 'hide' );

    var aIds = tRsp.removed_object_ids;
    for ( var iIndex in aIds )
    {
      var sId = aIds[iIndex];
      removeRow( sId, iIndex == ( aIds.length - 1 ) );
    }
  }
}

function removeRow( sId, bUpdate )
{
  var iRow = g_tRowMap[sId];

  //
  // Update internal data structures
  //

  // Set row as removed
  g_aSortableTableRows[iRow] = null;

  // Update maps of distinct column values
  for ( var sLabel in g_tColumnMap )
  {
    // Get cell value for this column
    var sCell = g_tColumnMap[sLabel].cells[iRow];

    // Get instances of this value in the column
    var aInstances = g_tColumnMap[sLabel].cells.filter(
      function( sCellValue )
      {
        return sCellValue == sCell
      }
    );

    if ( aInstances.length <= 1 )
    {
      // Remove from map of distinct values
      delete g_tColumnMap[sLabel].valMap[sCell];
    }

    // Clear cell value
    g_tColumnMap[sLabel].cells[iRow] = null;
  }

  delete g_tRowMap[sId];

  //
  // Update display
  //

  // Determine how to update the display
  if ( columnFiltersValid() && ! updateEmptyColumns() )
  {
    // Remove row from display
    $( '#sortableTableBody tr[object_id="' + sId + '"]' ).remove();

    if ( bUpdate )
    {
      // Update the table
      $( '#sortableTable' ).trigger( 'update', [true, function(){ renumberIndex(); } ] );
    }
  }
  else
  {
    // Reload table from internal data structures
    reloadSortableTable();
  }
}
