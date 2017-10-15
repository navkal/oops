// Copyright 2017 Panel Spy.  All rights reserved.

var FILTER_SELECT_MAX = 2;

// Sortable table options
var g_sSortableTableTitle = null;
var g_sSortableTableSubtitle = null;
var g_sSortableTableType = null;
var g_sSortableTableEditWhat = null;
var g_sSortableTableParams = {};

// Sortable table data structures
var g_aSortableTableRows = null;
var g_tColumnMap = null;
var g_aColumns = [];
var g_tRowMap = {};
var g_tHighlightedRows = {};
var g_aSortState = [];


// Retrieve sortable table from backend
var g_iStartRetrievalTime = null;
function getSortableTable()
{
  // Set handler to close any child windows
  $( window ).on( 'unload', closeChildWindows );

  // Set wait cursor
  setWaitCursor();

  // Post request to server
  var tPostData = new FormData();
  tPostData.append( "object_type", g_sSortableTableType );

  for ( var sParamName in g_sSortableTableParams )
  {
    tPostData.append( sParamName, g_sSortableTableParams[sParamName] );
  }

  g_iStartRetrievalTime = Date.now();

  $.ajax(
    '/sortableTable/getSortableTable.php',
    {
      type: 'POST',
      processData: false,
      contentType: false,
      dataType : 'json',
      data: tPostData
    }
  )
  .done( loadSortableTable )
  .fail( handleAjaxError );
}

// Load sortable table onto page
var g_iStartRenderingTime = null;
function loadSortableTable( tRsp, sStatus, tJqXhr )
{
  if ( g_iStartRetrievalTime )
  {
    console.log( '=> Time to retrieve sortable table: ' + ( Date.now() - g_iStartRetrievalTime ) + ' ms' );
  }

  g_iStartRenderingTime = Date.now();

  g_aSortableTableRows = tRsp['rows'];

  // Initialize empty table HTML
  var sHtml =
    '<thead>' +
      '<tr id="sortableTableHead">' +
      '</tr>' +
    '</thead>' +
    '<tfoot>' +
      '<tr id="sortableTableFoot">' +
      '</tr>' +
    '</tfoot>' +
    '<tbody id="sortableTableBody" >' +
    '</tbody>';

  $( '#sortableTable' ).html( sHtml );

  // Build map of columns from list of rows
  g_tColumnMap = {};
  for ( var iRow in g_aSortableTableRows )
  {
    // Get next row
    var tRow = g_aSortableTableRows[iRow];

    // Map ID to row number
    g_tRowMap[tRow.id] = iRow;

    // Insert artificial index cell
    tRow['index'] = parseInt( iRow ) + 1;

    // Traverse fields of the current row
    for ( sKey in tRow )
    {
      // Map key to label
      var tRule =  g_tPropertyRules[sKey];
      var sLabel = ( tRule && tRule.showInSortableTable ) ? tRule.label : null;

      if ( sLabel != null )
      {
        if ( ! g_tColumnMap[sLabel] )
        {
          // Insert first column map entry for this label
          g_tColumnMap[sLabel] =
          {
            key: sKey,
            label: sLabel,
            align: ( tRule.columnType == 'text' ) ? '' : 'right',
            sorter: ( tRule.columnType == 'control' ) ? 'controlParser' : ( tRule.columnType != 'index' ),
            empty: true,
            cells: [],
            minLength: Number.MAX_SAFE_INTEGER,
            maxLength: 0,
            valMap: {}
          };
        }

        makeTableCell( tRow[sKey], sLabel, tRule, iRow );
      }
    }
  }

  // Set title
  $( '#sortableTableTitle' ).text( g_sSortableTableTitle );
  var tSession = JSON.parse( localStorage.getItem( 'panelSpy.session' ) );
  var sSubtitle = g_sSortableTableSubtitle || tSession['context']['facilityFullname'] || tSession['context']['enterpriseFullname'];
  $( '#sortableTableSubtitle' ).text( sSubtitle );

  // Format table head/foot HTML and build list of columns
  g_sPropertySortContext = g_tPropertySortContexts.sortableTable;
  var aSortedLabels = Object.keys( g_tColumnMap ).sort( comparePropertyIndex );
  var sHtml = '';
  var aPrevColumns = g_aColumns;
  g_aColumns = [];

  for ( var iHeader in aSortedLabels )
  {
    var sLabel = aSortedLabels[iHeader];
    var tColumn = g_tColumnMap[sLabel];

    if ( ! tColumn.empty )
    {
      // Append current column to list
      g_aColumns.push( tColumn );

      // Configure column header attributes
      var sColumnType = g_tPropertyRules[tColumn.key].columnType;
      var bBlankHeader = ( sColumnType == 'index' || sColumnType == 'control' );
      var sFilter = '';
      if ( bBlankHeader )
      {
        // Column should have a blank header
        sFilter = ' class="filter-false" ';
        sLabel = '';
      }
      else
      {
        // If column has few values, use dropdown for filtering
        if ( Object.keys( tColumn.valMap ).length <= FILTER_SELECT_MAX )
        {
          sFilter = ' class="filter-select filter-exact" ';
        }
      }

      // Format the header HTML
      sHtml += '<th key="' + tColumn.key + '"' + sFilter + '>' + sLabel + '</th>';
    }
  }

  // Preserve sort state from table preceding reload
  preserveSortState( aPrevColumns );

  $( '#sortableTableHead,#sortableTableFoot' ).html( sHtml );

  // Format table body HTML
  if ( countSortableTableRows() == 0 )
  {
    $( '#sortableTableIsEmpty' ).show();
  }
  else
  {
    var sHtml = '';
    var bDone = false;
    var nRow = 0;

    while ( ! bDone )
    {
      var tHtmlRow = makeHtmlRow( nRow ++ );
      sHtml += tHtmlRow.html;
      bDone = tHtmlRow.done;
    }

    $( '#sortableTableBody' ).html( sHtml );
  }

  // Track sort and filter states
  var tFilterState =
  {
    aFilterState: Array( g_aColumns.length ).fill( '' )
  };

  // Style the table
  styleTable( 'sortableTable', tFilterState );
}

// Preserve sort state in reloaded table
function preserveSortState( aPrevColumns )
{
  console.log( '=======> BF sort=' + JSON.stringify( g_aSortState ) );
  for ( var iState in g_aSortState )
  {
    var aColState = g_aSortState[iState];
    var iSortedColIndex = aColState[0];
    var sSortedColLabel = aPrevColumns[iSortedColIndex].label;
    var iNewColIndex = g_aColumns.findIndex(
      function( tColumn )
      {
        return tColumn.label == sSortedColLabel;
      }
    );
    if ( iNewColIndex != -1 )
    {
      g_aSortState[iState][0] = iNewColIndex;
    }
    else
    {
      g_aSortState.splice( iState, 1 );
    }
  }
  console.log( '=======> AF sort=' + JSON.stringify( g_aSortState ) );
}

function makeTableCell( sCell, sLabel, tRule, iRow )
{
  // If the cell is an array, replace with array length
  if ( Array.isArray( sCell ) )
  {
    sCell = sCell.length;
  }

  // Convert to trimmed string
  sCell = sCell.toString().trim();

  if ( sCell != '' )
  {
    // Add value to map
    g_tColumnMap[sLabel].valMap[sCell] = '';

    // Track min and max lengths
    g_tColumnMap[sLabel].minLength = Math.min( g_tColumnMap[sLabel].minLength, sCell.length );
    g_tColumnMap[sLabel].maxLength = Math.max( g_tColumnMap[sLabel].maxLength, sCell.length );

    // Clear column-is-empty flag
    g_tColumnMap[sLabel].empty = false;

    // If column contains non-digit character, change the default alignment
    if ( ! /^\d+$/.test( sCell ) )
    {
      g_tColumnMap[sLabel].align = '';
    }

    if ( tRule.columnType == 'control' )
    {
      // Save the original cell value
      var sCellValue = sCell;

      // Perform special cell rendering for UI controls
      switch ( tRule.controlType )
      {
        case 'image_by_path':
          sCell = '<a path="' + sCell + '">';
          sCell += '<button class="btn btn-link btn-xs" onclick="openImageWindowEtc(event)" title="Panel Image" >';
          sCell += '<span class="glyphicon glyphicon-picture" style="font-size:18px;" ></span>';
          sCell += '</button>';
          sCell += '</a>';
          break;

        case 'activity_log':
          sCell = '<a object_id="' + sCellValue + '">';
          sCell += '<button class="btn btn-link btn-xs" onclick="openActivityLogWindowEtc(event)" title="Activity Log" >';
          sCell += '<span class="glyphicon glyphicon-list" style="font-size:18px;" ></span>';
          sCell += '</button>';
          sCell += '</a>';
          break;

        case 'update':
          sCell = '<a>';
          sCell += '<button class="btn btn-link btn-xs" onclick="highlightRow(event);g_sAction=' +"'update'" + '; g_sUpdateTarget='+"'"+sCellValue+"'"+'" data-target="#editDialog" data-toggle="modal" ' + tRule.customizeButton( sCellValue ) + '>';
          sCell += '<span class="glyphicon glyphicon-pencil" style="font-size:18px;" ></span>';
          sCell += '</button>';
          sCell += '</a>';
          break;

        case 'remove':
          sCell = '<a>';
          sCell += '<button class="btn btn-link btn-xs" onclick="highlightRow(event);initRemoveDialog('+"'"+sCellValue+"'"+')" data-target="#removeDialog" data-toggle="modal" ' + tRule.customizeButton( sCellValue ) + '>';
          sCell += '<span class="glyphicon glyphicon-remove" style="font-size:18px;" ></span>';
          sCell += '</button>';
          sCell += '</a>';
          break;

        case 'restore':
          sCell = '<a>';
          sCell += '<button class="btn btn-link btn-xs" onclick="highlightRow(event);initRestoreDialog('+"'"+sCellValue+"'"+')" data-target="#restoreDialog" data-toggle="modal" ' + tRule.customizeButton( sCellValue ) + '>';
          sCell += '<span class="glyphicon glyphicon-plus" style="font-size:18px;" ></span>';
          sCell += '</button>';
          sCell += '</a>';
          break;
      }
    }
    else if ( tRule.columnType == 'timestamp' )
    {
      sCell = formatTimestamp( sCell );
    }
  }

  // Insert cell in column
  g_tColumnMap[sLabel].cells[iRow] = sCell;
}

function makeHtmlRow( nRow, bHighlight )
{
  // If supplied row number is -1, then use the last row
  if ( nRow == -1 )
  {
    nRow = g_aSortableTableRows.length - 1;
  }

  // Determine the ID of the row
  var iRowId = g_aSortableTableRows[nRow].id;

  // Determine whether to highlight the row
  if ( typeof bHighlight == 'undefined' )
  {
    bHighlight = g_tHighlightedRows[iRowId];
  }

  // Build the HTML row
  var sHtml = '';
  var bDone = false;

  for ( var iHeader in g_aColumns )
  {
    var tColumn = g_aColumns[iHeader];
    var sHeader = tColumn.label;

    var sCell = tColumn.cells[nRow];
    if ( ( tColumn.align == '' ) && ( ( tColumn.maxLength - tColumn.minLength ) < 10 ) )
    {
      tColumn.align = 'center';
    }
    var sAlign = 'text-align:' + tColumn.align;
    var sControlData = ( tColumn.sorter == 'controlParser' ) ? ( 'control-data="' + ( sCell ? 0 : 1 ) + '"' ) : '';
    sHtml += '<td style="' + sAlign + '" ' + sControlData + ' >' + sCell + '</td>';

    bDone = ( nRow == tColumn.cells.length - 1 );
  }
  sHtml += '</tr>';

  // Prepend table row tag
  var sObjectId = ' object_id="' + iRowId + '"';
  var sClass = bHighlight ? ( ' class="text-primary" ' ) : '';
  sHtml = '<tr ' + sObjectId + sClass + ' >' + sHtml;

  var tRow = { html: sHtml, done: bDone };

  return tRow;
}

// Parser for tablesorter, to sort columns containing controls
var g_tControlParser =
{
    id: 'controlParser',
    is: function(s){ return false; },
    format: function( s, table, cell, cellIndex ){ return $( cell ).attr( 'control-data' ); },
    type: 'numeric'
};


// Style table to support sort, filter, and dynamic update
function styleTable( sId, tFilterState )
{
  var tTable =  sId ? $( "#" + sId ) : $( 'table' );
  if ( tTable.length > 0 )
  {
    // Add parser to sort columns containing controls
    $.tablesorter.addParser( g_tControlParser );

    // Initialize the tablesorter
    var tSorter =
    {
      theme : "bootstrap",
      headerTemplate : '{content} {icon}',
      widgets : [ "uitheme", "filter", "columns", "zebra", "resizable" ],
      widgetOptions :
      {
        resizable: true,
        zebra : ["even", "odd"],
        columns: [ "primary", "secondary", "tertiary" ],
        filter_reset : ".reset",
        filter_cssFilter: "form-control"
      },
      headers: g_aColumns,
      sortList: g_aSortState
    };

    tTable.tablesorter( tSorter );

    $.tablesorter.setFilters( tTable, tFilterState.aFilterState, true );

    // Restore default column widths
    tTable.trigger( 'resizableReset' );

    // Set handler to finish table initialization
    tTable.on( 'tablesorter-ready', onSortableTableReady );

    // Set sort completion handler
    tTable.on( "sortEnd", function( event ){ renumberIndex(); g_aSortState = event.target.config.sortList;} );

    // Set filter completion handler
    tTable.on( "filterEnd", function( event ){ renumberIndex(); tFilterState.aFilterState = $.tablesorter.getFilters( tTable ); } );
  }

  $( '#' + sId ).addClass( 'table-condensed' );
}

function onSortableTableReady( tEvent )
{
  // Suppress further handling of this event
  $( tEvent.target ).off( 'tablesorter-ready' );

  // Initialize optional editing
  if ( g_sSortableTableEditWhat )
  {
    // Set modal event handlers
    var tEditDialog = $( '#editDialog ' );
    tEditDialog.on( 'show.bs.modal', onShowEditDialog );
    tEditDialog.on( 'shown.bs.modal', onShownEditDialog );

    // Customize and show the Add button
    $( '#sortableTableAddButtonText' ).text( 'Add ' + g_sSortableTableEditWhat );
    $( '#sortableTableAddButton' ).show();

    resetChangeHandler();
  }

  // Clear the wait cursor
  clearWaitCursor();

  // Trigger update to enable hover shading
  $( '#sortableTable' ).trigger( 'update', [true] );

  console.log( '=> Time to render sortable table: ' + ( Date.now() - g_iStartRenderingTime ) + ' ms' );
}

function renumberIndex()
{
  var aCells = $( '#sortableTableBody tr:not(.filtered) td:first-of-type' );
  for ( var i = 0; i < aCells.length; i ++ )
  {
    $( aCells[i] ).text( i + 1 );
  }
}

function openImageWindowEtc( tEvent )
{
  highlightRow( tEvent );
  openImageWindow( tEvent );
}

function openActivityLogWindowEtc( tEvent )
{
  highlightRow( tEvent );
  openActivityLogWindow( tEvent );
}

function highlightRow( tEvent )
{
  $( '#sortableTableBody tr.text-primary' ).removeClass( 'text-primary' );
  $( tEvent.target ).closest( 'tr' ).addClass( 'text-primary' );
}

function closeChildWindows()
{
  childWindowsClose( g_aImageWindows );
  childWindowsClose( g_aActivityLogWindows );
}

function findSortableTableRow( sId )
{
  var iRow = g_tRowMap[sId];
  var tRow = g_aSortableTableRows[iRow];
  return tRow;
}

function formatTimestamp( sTimestamp )
{
  return new Date( Math.floor( sTimestamp * 1000 ) ).toLocaleString();
}


// --> --> --> Dynamic update of table --> --> -->

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

// Determine whether all columns in the table have values
function updateEmptyColumns()
{
  var bFoundEmpty = false;

  for ( var sLabel in g_tColumnMap )
  {
    if ( ! g_tColumnMap[sLabel].empty )
    {
      // Get array of non-empty values
      var aValues = g_tColumnMap[sLabel].cells.filter(
        function( sCellValue )
        {
          return ( sCellValue != null ) && ( sCellValue != '' );
        }
      );

      if ( aValues.length == 0 )
      {
        bFoundEmpty = true;
        g_tColumnMap[sLabel].empty = true;
      }
    }
  }

  return bFoundEmpty;
}

function columnFiltersValid()
{
  // Check for proper column filter controls
  // - If the column has up to <max> distinct values, filter should be select control
  // - Otherwise, filter should be text input control

  var bValid = true;

  // Check each column
  for( var sLabel in g_tColumnMap )
  {
    // Determine whether the column needs a filter
    var tColumn = g_tColumnMap[sLabel];
    var bEmpty = tColumn.empty;
    var sKey = tColumn.key;
    var sColumnType = g_tPropertyRules[sKey].columnType;
    var bFilter = ! bEmpty && ( sColumnType != 'index' ) && ( sColumnType != 'control' )

    if ( bFilter )
    {
      // Determine whether filter should be a select
      var bExpectSelect = Object.keys( tColumn.valMap ).length <= FILTER_SELECT_MAX;

      // Determine whether filter is a select
      var tColumnHead = $( '#sortableTable th[key="' + sKey + '"]' );
      var iCol = tColumnHead.attr( 'data-column' );
      var tFilterSelect = $( '#sortableTable thead .tablesorter-filter-row td[data-column="' + iCol + '"] select' );
      var bIsSelect = tFilterSelect.length > 0;

      // Determine whether expectation matches reality
      bValid = bValid && ( bExpectSelect == bIsSelect );
    }
  }

  return bValid;
}

function reloadSortableTable()
{
  $( '#sortableTable ').trigger( 'destroy', [false, destroySortableTableDone]);
}

function destroySortableTableDone()
{
  // Clear some variables
  g_tRowMap = {};
  g_iStartRetrievalTime = null;

  // Get list of rows that have not been removed
  var aValidRows = g_aSortableTableRows.filter(
    function( tRow )
    {
      return tRow != null;
    }
  );

  // Sort rows on default key
  aValidRows.sort( compareSortableTableRows );

  // Load the table
  loadSortableTable( { rows: aValidRows } );
}

function compareSortableTableRows( tRow1, tRow2 )
{
  var s1 = '';
  var s2 = '';

  if ( tRow1.path )
  {
    // PTC
    s1 = tRow1.path;
    s2 = tRow2.path;
  }
  else if ( tRow1.source_path )
  {
    // Device
    s1 = tRow1.source_path;
    s2 = tRow2.source_path;
  }
  else if ( tRow1.loc_new )
  {
    // Location
    s1 = tRow1.loc_new;
    s2 = tRow2.loc_new;
  }
  else if ( tRow1.timestamp )
  {
    // Recycle Bin, Activity Log
    s2 = tRow1.timestamp.toString();
    s1 = tRow2.timestamp.toString();
  }
  else if ( tRow1.username )
  {
    // User
    s1 = tRow1.username;
    s2 = tRow2.username;
  }

  return s1.localeCompare( s2, 'kn', { numeric: true } );
}

function countSortableTableRows()
{
  var aRemovedRows = g_aSortableTableRows.filter(
    function( tRow )
    {
      return tRow == null;
    }
  );

  var nRows = g_aSortableTableRows.length - aRemovedRows.length;

  return nRows;
}

// <-- <-- <-- Dynamic update of table <-- <-- <--