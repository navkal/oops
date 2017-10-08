// Copyright 2017 Panel Spy.  All rights reserved.

var g_aSortableTableRows = null;
var g_sSortableTableTitle = null;
var g_sSortableTableType = null;
var g_sSortableTableEditWhat = null;
var g_tColumnMap = null;
var g_aSortedHeaders = null;
var g_tRowMap = {};

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
  console.log( '=> Time to retrieve sortable table: ' + ( Date.now() - g_iStartRetrievalTime ) + ' ms' );
  g_iStartRenderingTime = Date.now();
  g_aSortableTableRows = tRsp['rows'];

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
            sortable: ! ( ( tRule.columnType == 'control' ) || ( tRule.columnType == 'index' ) ),
            empty: true,
            cells: [],
            minLength: Number.MAX_SAFE_INTEGER,
            maxLength: 0,
            valMap: {}
          };
        }

        makeTableCell( tRow[sKey], sLabel, tRule );
      }
    }
  }

  // Set title
  $( '#sortableTableTitle' ).text( g_sSortableTableTitle );
  var tSession = JSON.parse( localStorage.getItem( 'panelSpy.session' ) );
  var sSubtitle = tSession['context']['facilityFullname'] ? tSession['context']['facilityFullname'] : tSession['context']['enterpriseFullname'];
  $( '#sortableTableSubtitle' ).text( sSubtitle );

  // Format table head/foot HTML, and construct sorter array
  g_sPropertySortContext = g_tPropertySortContexts.sortableTable;
  g_aSortedHeaders = Object.keys( g_tColumnMap ).sort( comparePropertyIndex );
  var sHtml = '';
  var aHeaders = [];
  var iColumn = 0;

  for ( var iHeader in g_aSortedHeaders )
  {
    var sLabel = g_aSortedHeaders[iHeader];
    var tColumn = g_tColumnMap[sLabel];

    if ( ! tColumn.empty )
    {
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
        if ( Object.keys( tColumn.valMap ).length <= 2 )
        {
          sFilter = ' class="filter-select filter-exact" ';
        }
      }

      // Format the header HTML
      sHtml += '<th key="' + tColumn.key + '"' + sFilter + '>' + sLabel + '</th>';
      aHeaders[iColumn++] = tColumn.sortable ? {} : { sorter: false };
    }
  }

  $( '#sortableTableHead,#sortableTableFoot' ).html( sHtml );

  // Format table body HTML
  if ( g_aSortableTableRows.length == 0 )
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
  var tSortState =
  {
    aSortState: []
  };
  var tFilterState =
  {
    aFilterState: Array( aHeaders.length ).fill( '' )
  };

  // Style the table
  styleTable( 'sortableTable', aHeaders, tSortState, tFilterState );
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
        case 'image':
          sCell = '<a path="' + sCell + '">';
          sCell += '<button class="btn btn-link btn-xs" onclick="openImageWindowEtc(event)" title="Image" >';
          sCell += '<span class="glyphicon glyphicon-picture" style="font-size:18px;" ></span>';
          sCell += '</button>';
          sCell += '</a>';
          break;

        case 'activity_log':
          sCell = '<a activity_log_id="' + sCellValue + '">';
          sCell += '<button class="btn btn-link btn-xs" onclick="openActivityLogWindowEtc(event)" title="Activity Log" >';
          sCell += '<span class="glyphicon glyphicon-book" style="font-size:18px;" ></span>';
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

  // Append cell to the column
  if ( typeof iRow == 'undefined' )
  {
    g_tColumnMap[sLabel].cells.push( sCell );
  }
  else
  {
    if ( g_tColumnMap[sLabel].cells[iRow] != sCell )
    {
      g_tColumnMap[sLabel].cells[iRow] = sCell;
    }
  }
}

function makeHtmlRow( nRow, sCssClass )
{
  var bDone = false;

  var sObjectId = ( nRow == -1 ) ? '' : ' object_id="' + g_aSortableTableRows[nRow].id + '"';

  var sClass = sCssClass ? ( ' class="' + sCssClass + '" ' ) : '';

  var sHtml = '';

  for ( var iHeader in g_aSortedHeaders )
  {
    var sHeader = g_aSortedHeaders[iHeader];
    var tColumn = g_tColumnMap[sHeader];

    if ( nRow == -1 )
    {
      nRow = tColumn.cells.length - 1;
      sObjectId = ' object_id="' + g_aSortableTableRows[nRow].id + '"';
    }

    if ( ! tColumn.empty )
    {
      var sCell = tColumn.cells[nRow];
      if ( ( tColumn.align == '' ) && ( ( tColumn.maxLength - tColumn.minLength ) < 10 ) )
      {
        tColumn.align = 'center';
      }
      var sAlign = 'text-align:' + tColumn.align;
      sHtml += '<td style="' + sAlign + '" >' + sCell + '</td>';
    }

    bDone = ( nRow == tColumn.cells.length - 1 );
  }
  sHtml += '</tr>';

  // Prepend table row tag
  sHtml = '<tr ' + sObjectId + sClass + ' >' + sHtml;

  var tRow = { html: sHtml, done: bDone };

  return tRow;
}

// Parser for tablesorter, to sort hexadecimal unit IDs
var g_tHexParser =
{
    id: 'unitId',
    is: function(s){ return false; },
    format: function(s) { return parseInt( s, 16 ); },
    type: 'numeric'
};

// Style table to support sort, filter, and dynamic update
function styleTable( sId, tHeaders, tSortState, tFilterState )
{
  var tTable =  sId ? $( "#" + sId ) : $( 'table' );
  if ( tTable.length > 0 )
  {
    // Add parser to sort hex unit IDs
    $.tablesorter.addParser( g_tHexParser );

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
      headers: tHeaders,
      sortList: tSortState.aSortState
    };

    tTable.tablesorter( tSorter );

    $.tablesorter.setFilters( tTable, tFilterState.aFilterState, true );

    // Restore default column widths
    tTable.trigger( 'resizableReset' );

    // Set handler to finish table initialization
    tTable.on( 'tablesorter-ready', onSortableTableReady );

    // Set sort completion handler
    tTable.on( "sortEnd", function( event ){ renumberIndex(); tSortState.aSortState = event.target.config.sortList;} );

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
    var tAddButton = $( '#sortableTableAddButton' );
    tAddButton.append( g_sSortableTableEditWhat );
    tAddButton.show();

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