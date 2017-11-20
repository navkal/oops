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
    highlightErrors( tRsp.selectors );
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

function makePhaseDropdowns( aParents, sParentId, sPhaseBParentId )
{
  var tParent = null;

  if ( sParentId )
  {
    tParent = aParents.find(
      function( tParent )
      {
        return tParent.id == sParentId
      }
    );
  }

  if ( tParent && tParent.make_phase_dropdowns )
  {
    var sParentPath = tParent.text;
    var sGrannyPath = getGrannyPath( sParentPath );

    var aSiblings = aParents.filter(
      function ( tParent )
      {
        return ( tParent.text != sParentPath ) && ( getGrannyPath( tParent.text ) == sGrannyPath );
      }
    );

    if ( aSiblings.length )
    {
      // Load siblings into dropdowns
      var sHtmlPhase = '<option value="0" >[none]</option>';
      for ( var iSibling in aSiblings )
      {
        var tSibling = aSiblings[iSibling];
        sHtmlPhase += '<option value="' + tSibling.id + '" >' + tSibling.text.split( '.' ).pop() + '</option>';
      }
      $( '#phase_b_tail, #phase_c_tail' ).html( sHtmlPhase );

      // Enable/disable phase dropdowns
      $( '#phase_b_tail' ).prop( 'disabled', false );
      updatePhaseCDropdown( sPhaseBParentId );
    }
    else
    {
      // No siblings
      $( '#phase_b_tail, #phase_c_tail' ).html( '' );
      $( '#phase_b_tail, #phase_c_tail' ).prop( 'disabled', true );
    }
  }
  else
  {
    // Disable phase dropdowns
    $( '#phase_b_tail, #phase_c_tail' ).prop( 'disabled', true );
  }

  // Clear phase selections
  $( '#phase_b_tail' ).val( 0 ).trigger( 'change' );
}

function getGrannyPath( sParentPath )
{
  var aParentPath = sParentPath.split( '.' );
  aParentPath.pop();
  return aParentPath.join( '.' );
}

// Update state of Phase C dropdown
function updatePhaseCDropdown( sPhaseBParentId )
{
  if ( Number( sPhaseBParentId ) )
  {
    $( '#phase_c_tail' ).prop( 'disabled', false );
  }
  else
  {
    $( '#phase_c_tail' ).prop( 'disabled', true );
    $( '#phase_c_tail' ).val( 0 ).trigger( 'change' );
  }
}

function validateDistributionObjectInput( sType )
{
  var aMessages = [];

  aMessages = aMessages.concat( requireVoltage() );
  aMessages = aMessages.concat( requireParent() );
  aMessages = aMessages.concat( validatePhaseParents() );
  aMessages = aMessages.concat( requireTail( sType ) );
  aMessages = aMessages.concat( validateNumber() );
  aMessages = aMessages.concat( validateName() );
  aMessages = aMessages.concat( requireLocation() );

  return aMessages;
}

function requireVoltage()
{
  var aMessages = [];

  if ( ( $( '#voltage' ).val() == null ) && ( $( '#voltage option' ).length > 0 ) )
  {
    aMessages.push( 'Voltage is required' );
    $( '#voltage' ).closest( '.form-group' ).addClass( 'has-error' );
  }

  return aMessages;
}

function requireParent()
{
  var aMessages = [];

  if ( ( $( '#parent_path' ).val() == null ) && ( $( '#parent_path option' ).length > 0 ) )
  {
    aMessages.push( 'Parent is required' );
    $( '#parent_path' ).closest( '.form-group' ).addClass( 'has-error' );
  }

  return aMessages;
}

function validatePhaseParents()
{
  var aMessages = [];

  var iPhaseBParentId = Number( $( '#phase_b_tail' ).val() );
  var iPhaseCParentId = Number( $( '#phase_c_tail' ).val() );
  if ( iPhaseBParentId && ( iPhaseBParentId == iPhaseCParentId ) )
  {
    aMessages.push( 'Phase B Connection and Phase C Connection must be unique' );
    $( '#phase_b_tail' ).closest( '.form-group' ).addClass( 'has-error' );
    $( '#phase_c_tail' ).closest( '.form-group' ).addClass( 'has-error' );
  }

  return aMessages;
}

function requireTail( sType )
{
  var aMessages = [];

  var sNumber = $( '#number' ).val();
  var sName = $( '#name' ).val();

  switch( sType )
  {
    case 'Panel':
    case 'Transformer':
        if ( ! sName )
        {
          aMessages.push( 'Name is required' );
          $( '#name' ).closest( '.form-group' ).addClass( 'has-error' );
        }
      break;

    case 'Circuit':
        if ( ! sNumber && ! sName )
        {
          aMessages.push( 'Number or Name is required' );
          $( '#number' ).closest( '.form-group' ).addClass( 'has-error' );
          $( '#name' ).closest( '.form-group' ).addClass( 'has-error' );
        }
      break;
  }

  return aMessages;
}

function validateNumber()
{
  var aMessages = [];

  var sNumber = $( '#number' ).val();
  if ( sNumber.length > 0 )
  {
    if ( ! sNumber.match( /^\d+$/ ) )
    {
      aMessages.push( 'Number can contain only digits.' );
      $( '#number' ).closest( '.form-group' ).addClass( 'has-error' );
    }

    if ( parseInt( sNumber ) == 0 )
    {
      aMessages.push( 'Number must be an integer value between 1 and 9999.' );
      $( '#number' ).closest( '.form-group' ).addClass( 'has-error' );
    }
  }

  return aMessages;
}

function validateName( sName )
{
  var aMessages = [];

  var sName = $( '#name' ).val();

  if ( sName.length > 0 )
  {
    if ( sName.match( /^\d+$/ ) )
    {
      aMessages.push( 'Name must contain at least one non-digit character.' );
      $( '#name' ).closest( '.form-group' ).addClass( 'has-error' );
    }

    if ( ! sName.match( /^[a-zA-Z0-9\-_]+$/ ) )
    {
      aMessages.push( 'Name can contain only alphanumeric, hyphen, and underscore characters.' );
      $( '#name' ).closest( '.form-group' ).addClass( 'has-error' );
    }
  }

  return aMessages;
}

function requireLocation()
{
  var aMessages = [];

  if ( $( '#room_id' ).val() == null )
  {
    aMessages.push( 'Location is required' );
    $( '#room_id' ).closest( '.form-group' ).addClass( 'has-error' );
  }

  return aMessages;
}

function validateDeviceInput()
{
  var aMessages = [];

  if ( $( '#source_path' ).val() == null )
  {
    aMessages.push( 'Circuit is required' );
    $( '#source_path' ).closest( '.form-group' ).addClass( 'has-error' );
  }

  return aMessages;
}