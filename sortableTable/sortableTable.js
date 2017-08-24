// Copyright 2017 Panel Spy.  All rights reserved.

var g_aSortableTableRows = null;
var g_sSortableTableTitle = null;
var g_sSortableTableType = null;
var g_bSortDescending = false;
var g_sSortableTableObjectName = null;

// Retrieve sortable table from backend
function getSortableTable()
{
  // Set handler to close any child windows
  $( window ).on( 'unload', closeChildWindows );

  if ( g_sSortableTableObjectName )
  {
    // Set modal event handlers
    var sSelector = '#edit' + g_sSortableTableObjectName + 'Dialog'
    $( sSelector ).on( 'show.bs.modal', eval( 'onShow' + g_sSortableTableObjectName + 'Dialog' ) );
    $( sSelector ).on( 'shown.bs.modal', eval( 'onShown' + g_sSortableTableObjectName + 'Dialog' ) );

    // Customize the button
    var tAddButton = $( '#sortableTableAddButton' );
    tAddButton.html( '<span class="glyphicon glyphicon-plus"></span> Add ' + g_sSortableTableObjectName );
    tAddButton.click( eval( 'initAdd' + g_sSortableTableObjectName + 'Dialog' ) );
    tAddButton.attr( 'data-target', sSelector );
    tAddButton.show();
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
  g_aSortableTableRows = tRsp['rows'];

  // Build map of columns from list of rows
  var tColumnMap = {};
  for ( var iRow in g_aSortableTableRows )
  {
    // Get next row
    var tRow = g_aSortableTableRows[iRow];

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
            sortable: ! ( ( tRule.columnType == 'control' ) || ( tRule.columnType == 'index' ) ),
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

              case 'update':
                sCell = '<a username="' + sCell + '">';
                sCell += '<button class="btn btn-link btn-xs"' + tRule.formatButtonAttributes( sCellValue ) + ' data-toggle="modal" data-backdrop="static">';
                sCell += '<span class="glyphicon glyphicon-pencil" style="font-size:18px;" ></span>';
                sCell += '</button>';
                sCell += '</a>';
                break;

              case 'remove':
                sCell = '<a username="' + sCell + '">';
                sCell += '<button class="btn btn-link btn-xs" ' + tRule.formatButtonAttributes( sCellValue ) + ' data-toggle="modal" data-backdrop="static" >';
                sCell += '<span class="glyphicon glyphicon-remove" style="font-size:18px;" ></span>';
                sCell += '</button>';
                sCell += '</a>';
                break;
            }
          }
          else if ( tRule.columnType == 'timestamp' )
          {
            sCell = new Date( Math.floor( sCell * 1000 ) ).toLocaleString();
          }
        }

        // Append current cell to the column
        tColumnMap[sLabel].cells.push( sCell );
      }
    }
  }

  // Set title
  $( '#sortableTableTitle' ).text( g_sSortableTableTitle );
  var tSession = JSON.parse( localStorage.getItem( 'panelSpy.session' ) );
  var sSubtitle = tSession['context']['facilityFullname'] ? tSession['context']['facilityFullname'] : tSession['context']['enterpriseFullname'];
  $( '#sortableTableSubtitle' ).text( sSubtitle );

  // Format table head/foot HTML, and construct sorter array
  var aSortedHeaders = Object.keys( tColumnMap ).sort( comparePropertyIndex );
  var sHtml = '';
  var aHeaders = [];
  var iColumn = 0;

  for ( var iHeader in aSortedHeaders )
  {
    var sLabel = aSortedHeaders[iHeader];
    var tColumn = tColumnMap[sLabel];

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
  if ( g_bSortDescending )
  {
    $( $( '#sortableTableHead th' )[1] ).click();
  }

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
