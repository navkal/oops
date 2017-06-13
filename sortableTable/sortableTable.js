// Copyright 2017 Panel Spy.  All rights reserved.


var g_sSortableTableTitle = null;
var g_sSortableTableType = null;
var g_bShowTopologyLink = true;
var g_bShowAddUserButton = false;

// Retrieve sortable table from backend
function getSortableTable()
{
  // Set handler to close any child windows
  $( window ).on( 'unload', closeChildWindows );
  
  // Optionally hide topology link
  if ( g_bShowTopologyLink )
  {
    $( '#topologyLink' ).show();
  }

  if ( g_bShowAddUserButton )
  {
    $( '#addUserButton' ).show();
    $( '#tableTop' ).css( 'padding-bottom', '25px' );
  }

  // Set wait cursor
  setWaitCursor();

  // Post request to server
  var tPostData = new FormData();
  tPostData.append( "object_type", g_sSortableTableType );

  $.ajax(
    'sortableTable/getSortableTable.php',
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
function loadSortableTable( tRsp, sStatus, tJqXhr )
{
  var aRows = tRsp['rows'];

  // Build map of columns from list of rows
  var tColumnMap = {};
  for ( var iRow in aRows )
  {
    // Get next row
    var tRow = aRows[iRow];

    // Insert artificial index cell
    tRow['index'] = iRow;

    // Traverse fields of the current row
    for ( sKey in tRow )
    {
      // Map key to label
      var tRule =  g_tPropertyRules[sKey];
      var sLabel = ( tRule && tRule.showInSortableTable ) ? tRule.label : null;

      if ( sLabel != null )
      {
        if ( ! tColumnMap[sLabel] )
        {
          // Insert first column map entry for this label
          tColumnMap[sLabel] =
          {
            key: sKey,
            label: sLabel,
            align: ( tRule.columnType == 'text' ) ? '' : 'right',
            sortable: ! ( ( tRule.columnType == 'image' ) || ( tRule.columnType == 'index' ) ),
            empty: true,
            cells: [],
            minLength: Number.MAX_SAFE_INTEGER,
            maxLength: 0,
            valMap: {}
          };
        }

        // If the cell is an array, replace with array length
        var sCell = tRow[sKey];
        if ( Array.isArray( sCell ) )
        {
          sCell = sCell.length;
        }

        // Convert to trimmed string
        sCell = sCell.toString().trim();

        if ( sCell != '' )
        {
          // Add value to map
          tColumnMap[sLabel].valMap[sCell] = '';

          // Track min and max lengths
          tColumnMap[sLabel].minLength = Math.min( tColumnMap[sLabel].minLength, sCell.length );
          tColumnMap[sLabel].maxLength = Math.max( tColumnMap[sLabel].maxLength, sCell.length );

          // Clear column-is-empty flag
          tColumnMap[sLabel].empty = false;

          // If column contains non-digit character, change the default alignment
          if ( ! /^\d+$/.test( sCell ) )
          {
            tColumnMap[sLabel].align = '';
          }

          // Perform special rendering for images
          if ( tRule.columnType == 'image' )
          {
            sCell = '<a path="' + sCell + '">';
            sCell += '<button class="btn btn-link btn-xs" onclick="openImageWindowEtc(event)" title="Image" >';
            sCell += '<span class="glyphicon glyphicon-picture" style="font-size:18px;" ></span>';
            sCell += '</button>';
            sCell += '</a>';
          }
        }

        // Append current cell to the column
        tColumnMap[sLabel].cells.push( sCell );
      }
    }
  }

  // Set title
  $( '#sortableTableTitle' ).text( g_sSortableTableTitle );

  // Format table head/foot HTML, and construct sorter array
  var aSortedHeaders = Object.keys( tColumnMap ).sort( comparePropertyIndex );
  var sHtml = '';
  var aHeaders = [];
  var iColumn = 0;
  for ( var iHeader in aSortedHeaders )
  {
    var sHeader = aSortedHeaders[iHeader];
    var tColumn = tColumnMap[sHeader];
    if ( ! tColumn.empty )
    {
      var nVals = Object.keys( tColumn.valMap ).length;
      sFilter = ( nVals <= 2 ) ? ' class="filter-select filter-exact" ' : ( sHeader == '' ? ' class="filter-false" ' : '' );
      sHtml += '<th key="' + tColumn.key + '"' + sFilter + '>' + sHeader + '</th>';
      aHeaders[iColumn++] = tColumn.sortable ? {} : { sorter: false };
    }
  }
  $( '#sortableTableHead,#sortableTableFoot' ).html( sHtml );

  // Format table body HTML
  sHtml = '';
  var bDone = false;
  var nRow = 0;
  while ( ! bDone )
  {
    sHtml += '<tr>';
    for ( var iHeader in aSortedHeaders )
    {
      var sHeader = aSortedHeaders[iHeader];
      var tColumn = tColumnMap[sHeader];
      if ( ! tColumn.empty )
      {
        var sCell = tColumn.cells[nRow];
        if ( ( tColumn.align == '' ) && ( ( tColumn.maxLength - tColumn.minLength ) < 10 ) )
        {
          tColumn.align = 'center';
        }
        var sAlign = 'text-align:' + tColumn.align;
        sHtml += '<td style="' + sAlign + '" >' + sCell + '</td>';
        bDone = ( nRow == tColumn.cells.length - 1 );
      }
    }
    sHtml += '</tr>';

    nRow ++;
  }
  $( '#sortableTableBody' ).html( sHtml );

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

  // Trigger initial column sort
  $( $( '#sortableTableHead th' )[1] ).click();

  // Clear the wait cursor
  clearWaitCursor();
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
  $( '#sortableTableBody' ).find( '.text-primary' ).removeClass( 'text-primary' );
  $( tEvent.target ).closest( 'tr' ).addClass( 'text-primary' );
  openImageWindow( tEvent );
}

function closeChildWindows()
{
  childWindowsClose( g_aImageWindows );
}
