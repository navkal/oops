// Copyright 2017 Panel Spy.  All rights reserved.

var g_sAction = null;
var g_sSubmitLabel = null;
function initEditDialog( nLabelColumnWidth )
{
  g_bChanged = false;
  g_sSubmitLabel = g_sAction.charAt(0).toUpperCase() + g_sAction.slice(1) + ' ' + g_sSortableTableEditWhat;

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
    if ( Object.keys( tRsp.row ).length && tableHasAllColumns( tRsp.row ) )
    {
      $( '#editDialog' ).modal( 'hide' );

      switch( g_sAction )
      {
        case 'add':
          addRow( tRsp.row )
          break;

        case 'update':
          updateRow( tRsp.row )
          break;
      }
    }
    else
    {
      location.reload();
    }
  }
}

function addRow( tRow )
{
  // Add row to the global list
  g_aSortableTableRows.push( tRow );

  // Map ID to row number
  g_tRowMap[tRow.id] = g_aSortableTableRows.length - 1;

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
      makeTableCell( tRow[sKey], sLabel, tRule );
    }
  }

  // Create the HTML row
  $( '#sortableTableBody tr' ).removeClass();
  var sHtml = makeHtmlRow( -1, 'text-add' ).html;

  // Insert the row at the top of the table
  $( '#sortableTableBody' ).prepend( sHtml );

  // Update the table
  $( '#sortableTable' ).trigger( 'update', [true] );

  // Renumber the index column
  renumberIndex();
}

function updateRow( tRspRow )
{
  var iRow = g_tRowMap[tRspRow.id];

  // Traverse fields
  for ( sKey in tRspRow )
  {
    // Map key to label
    var tRule =  g_tPropertyRules[sKey];
    var sLabel = ( tRule && tRule.showInSortableTable ) ? tRule.label : null;

    if ( sLabel != null )
    {
      // Add cell to column map
      makeTableCell( tRspRow[sKey], sLabel, tRule, iRow );
    }
  }

  // Create the HTML row
  $( '#sortableTableBody tr' ).removeClass();
  var sHtml = makeHtmlRow( iRow, 'text-update' ).html;

  // Replace existing row with new HTML
  $( '#sortableTableBody tr[object_id="' + tRspRow.id + '"]' ).replaceWith( sHtml );

  // Update the table
  $( '#sortableTable' ).trigger( 'update', [true] );
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