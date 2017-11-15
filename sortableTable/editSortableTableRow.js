// Copyright 2017 Panel Spy.  All rights reserved.

var g_sAction = null;
function initEditDialog( nLabelColumnWidth )
{
  g_bChanged = false;

  // Label dialog and submit button
  var sSubmitLabel = g_sAction.charAt(0).toUpperCase() + g_sAction.slice(1) + ' ' + g_sSortableTableEditWhat;
  $( '#editDialogTitle' ).text( sSubmitLabel );
  $( '#editDialogFormSubmitProxy' ).text( sSubmitLabel );

  makeFieldLabels( $( '.form-control,.input-group', '#editDialogForm' ) );

  // Turn off autocomplete
  $( 'input', '#editDialogForm' ).attr( 'autocomplete', 'off' );

  // Customize responsive layout
  nLabelColumnWidth = nLabelColumnWidth || 3;
  $( '.form-group>label', '#editDialogForm' ).removeClass().addClass( 'control-label' ).addClass( 'col-sm-' + nLabelColumnWidth );
  $( '.form-group>div', '#editDialogForm' ).removeClass().addClass( 'col-sm-' + ( 12 - nLabelColumnWidth ) );
}

// Allow user to select text in select2 rendering
function allowSelect2SelectText( sId )
{
  if ( $( '#' + sId ).val() )
  {
    $( '#select2-' + sId + '-container' ).css(
      {
        '-webkit-user-select': 'text',
        '-moz-user-select': 'text',
        '-ms-user-select': 'text',
        'user-select': 'text',
      }
    );
  }
}

function setSelect2CloseHandler()
{
  $( 'select' ).on(
    'select2:close',
    function( e )
    {
      $( this ).focus();
    }
  );
}

function getSelect2Text( tControl )
{
  var sId = tControl.attr( 'id' );
  var sSelector = '#select2-' + sId + '-container';
  var sVal = tControl.val();
  var sText = ( sVal == 0 ) ? '' : $( '#' + sId + ' option[value="' + sVal + '"]' ).text();
  return sText;
}

function submitEditDialogDone( tRsp, sStatus, tJqXhr )
{
  closeChildWindows();

  hideSpinner();

  if ( tRsp.messages.length )
  {
    // Show error messages
    showMessages( tRsp.messages );

    // Highlight pertinent fields
    var aSelectors = tRsp.selectors;
    for ( var iSelector in aSelectors )
    {
      var sSelector = aSelectors[iSelector];
      $( sSelector ).closest( '.form-group' ).addClass( 'has-error' );
    }
  }
  else
  {
    $( '#editDialog' ).modal( 'hide' );

    if ( countSortableTableRows() == 0 )
    {
      // Table is empty
      addFirstSortableTableRow( tRsp );
    }
    else
    {
      // Table is not empty
      updateSortableTable( tRsp );
    }
  }
}

function addFirstSortableTableRow( tRsp )
{
  $( '#sortableTableIsEmpty' ).hide();
  g_aSortableTableRows.push( tRsp.row );
  reloadSortableTable();
}

function updateSortableTable( tRsp )
{
  // Clear list of rows to be highlighted
  g_tHighlightedRows = {};

  // Determine whether table has all columns in added/updated row
  var bTableHasAllColumns = tableHasAllColumns( tRsp.row );

  // Add or update the row
  switch( g_sAction )
  {
    case 'add':
      addRow( tRsp.row )
      break;

    case 'update':
      updateRow( tRsp.row, tRsp.descendant_rows )
      break;
  }

  // Determine how to update the display
  if ( bTableHasAllColumns && columnFiltersValid() && ! updateEmptyColumns() )
  {
    // Update the table
    $( '#sortableTable' ).trigger( 'update', [true, function(){ renumberIndex(); } ] );
  }
  else
  {
    // Reload table from internal data structures
    reloadSortableTable();
  }
}

function addRow( tRow )
{
  // Add ID to list of highlighted rows
  g_tHighlightedRows[tRow.id] = true;

  // Add row to the global list
  g_aSortableTableRows.push( tRow );
  var iRow = g_aSortableTableRows.length - 1;

  // Map ID to row number
  g_tRowMap[tRow.id] = iRow;

  // Insert artificial index cell
  tRow['index'] = 0;

  // Traverse fields
  for ( sKey in tRow )
  {
    // Map key to label
    var tRule =  g_tPropertyRules[sKey];
    var sLabel = ( tRule && tRule.showInSortableTable ) ? tRule.label : null;

    if ( sLabel != null )
    {
      // Add cell to column map
      makeTableCell( tRow[sKey], sLabel, tRule, iRow );
    }
  }

  // Create the HTML row
  $( '#sortableTableBody tr.text-primary' ).removeClass( 'text-primary' );
  var sHtml = makeHtmlRow( -1, true ).html;

  // Insert the row at the top of the table
  $( '#sortableTableBody' ).prepend( sHtml );
}

function updateRow( tRspRow, aRspDescendants )
{
  var aRows = [ tRspRow ].concat( aRspDescendants );

  $( '#sortableTableBody tr.text-primary' ).removeClass( 'text-primary' );

  for ( var iRow = 0; iRow < aRows.length; iRow ++ )
  {
    // Get next row
    var tRspRow = aRows[iRow];

    // Add ID to list of highlighted rows
    g_tHighlightedRows[tRspRow.id] = true;

    // Map row ID to row index in column map
    var iRowIndex = g_tRowMap[tRspRow.id];

    // Replace the row in the list
    g_aSortableTableRows[iRowIndex] = tRspRow;

    // Traverse fields
    for ( sKey in tRspRow )
    {
      // Map key to label
      var tRule =  g_tPropertyRules[sKey];
      var sLabel = ( tRule && tRule.showInSortableTable ) ? tRule.label : null;

      if ( sLabel != null )
      {
        // Add cell to column map
        makeTableCell( tRspRow[sKey], sLabel, tRule, iRowIndex );
      }
    }

    // Create the HTML row
    var sHtml = makeHtmlRow( iRowIndex, true ).html;

    // Replace existing row with new HTML
    $( '#sortableTableBody tr[object_id="' + tRspRow.id + '"]' ).replaceWith( sHtml );
  }
}
