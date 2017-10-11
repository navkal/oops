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
    if ( g_aSortableTableRows.length == 0 )
    {
      // Table is empty; reload page to initialize
      location.reload();
    }
    else
    {
      // Table is not empty; update on existing page
      $( '#editDialog' ).modal( 'hide' );
      updateSortableTable( tRsp );
    }
  }
}

function updateSortableTable( tRsp )
{
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

  // Determine whether column filters are valid
  var bColumnFiltersValid = columnFiltersValid();

  // Determine how to update the display
  if ( bTableHasAllColumns && bColumnFiltersValid )
  {
    // Update the table
    $( '#sortableTable' ).trigger( 'update', [true] );

    // Renumber the index column
    renumberIndex();
  }
  else
  {
    // Reload table from internal data structures
    reloadSortableTable();
  }
}

function addRow( tRow )
{
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
  var sHtml = makeHtmlRow( -1, 'text-primary' ).html;

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
    var sHtml = makeHtmlRow( iRowIndex, 'text-primary' ).html;

    // Replace existing row with new HTML
    $( '#sortableTableBody tr[object_id="' + tRspRow.id + '"]' ).replaceWith( sHtml );
  }
}

// Determine whether current table has all the columns needed to render the new row
function tableHasAllColumns( tRow )
{
  var bTableHasAllColumns = true;
  var aKeys = Object.keys( tRow );
  var iCol = 0;

  for ( var iCol = 0; ( iCol < aKeys.length ) && bTableHasAllColumns; iCol ++ )
  {
    var sKey = aKeys[iCol];
    var tRule =  g_tPropertyRules[sKey];
    var sLabel = ( tRule && tRule.showInSortableTable ) ? tRule.label : null;

    if ( sLabel != null )
    {
      var sCell = tRow[sKey];
      var bColumnEmpty = g_tColumnMap[sLabel].empty;

      if ( sCell && bColumnEmpty )
      {
        // This cell does not have a corresponding column in the table
        bTableHasAllColumns = false;
      }
    }
  }

  return bTableHasAllColumns;
}

function columnFiltersValid()
{
  // Ensure proper column filter controls
  // - If the column has up to <max> distinct values, filter should be select control
  // - Otherwise, filter should be text input control

  var bValid = true;

  // If the table contains more than <max> rows...
  if ( g_aSortableTableRows.length > FILTER_SELECT_MAX )
  {
    // Check each column
    for( var sLabel in g_tColumnMap )
    {
      // Determine whether the column needs a filter
      var tColumn = g_tColumnMap[sLabel];
      var bEmpty = tColumn.empty;
      var sKey = tColumn.key;
      var sColumnType = g_tPropertyRules[sKey].columnType;
      var bFilter = ! bEmpty && ( sColumnType != 'index' ) && ( sColumnType != 'control' )

      // If the column needs a filter...
      if ( bFilter )
      {
        // If the column has more than <max> distinct values...
        if ( Object.keys( tColumn.valMap ).length > FILTER_SELECT_MAX )
        {
          // This column should be filtered by a text input control

          // Find the column head
          var tColumnHead = $( '#sortableTable th[key="' + sKey + '"]' );
          var iCol = tColumnHead.attr( 'data-column' );

          // If this column is filtered by a select control, reload the page
          var tFilterSelect = $( '#sortableTable thead .tablesorter-filter-row td[data-column="' + iCol + '"] select' );
          if ( tFilterSelect.length )
          {
            bValid = false;
          }
        }
      }
    }
  }

  return bValid;
}

function reloadSortableTable()
{
  g_tRowMap = {};
  g_iStartRetrievalTime = null;
  $( '#sortableTable ').trigger( 'destroy', [false, destroySortableTableDone]);
}

function destroySortableTableDone()
{
  g_aSortableTableRows.sort( compareSortableTableRows );
  loadSortableTable( { rows: g_aSortableTableRows } );
}

function compareSortableTableRows( tRow1, tRow2 )
{
  var s1 = '';
  var s2 = '';

  if ( tRow1.path )             // PTC
  {
    s1 = tRow1.path;
    s2 = tRow2.path;
  }
  else if ( tRow1.source_path )  // Device
  {
    s1 = tRow1.source_path;
    s2 = tRow2.source_path;
  }
  else if ( tRow1.loc_new )      // Location
  {
    s1 = tRow1.loc_new;
    s2 = tRow2.loc_new;
  }
  else if ( tRow1.timestamp )    // Recycle Bin, Activity Log
  {
    s2 = tRow1.timestamp.toString();
    s1 = tRow2.timestamp.toString();
  }
  else if ( tRow1.username )     // User
  {
    s1 = tRow1.username;
    s2 = tRow2.username;
  }

  return s1.localeCompare( s2, 'kn', { numeric: true } );
}