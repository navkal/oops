// Copyright 2017 Panel Spy.  All rights reserved.

function showSpinner()
{
  $( '#spinner' ).css( 'display', 'block' );
}

function hideSpinner()
{
  $( '#spinner' ).css( 'display', 'none' );
}

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

// Make labels for Add and Update input forms
function makeFieldLabels( aFields )
{
  for ( var iField = 0; iField < aFields.length; iField ++ )
  {
    var tField = aFields[iField];
    var sKey = tField.id;
    var tRule = g_tPropertyRules[sKey];

    // If there is a rule for this element, apply it
    if ( tRule )
    {
      var sLabel = tRule.label;
      $( 'label[for=' + sKey + ']' ).text( sLabel );

      switch( tField.tagName.toLowerCase() )
      {
        case 'input':
        case 'textarea':
          $( tField ).attr( 'placeholder', sLabel );
          break;

        case 'select':
          // $( tField ).attr( 'title', sLabel );
          break;
      }
    }
  }
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

function resetChangeHandler()
{
  $( 'input,select,textarea' ).off( 'change' );
  $( 'input,select,textarea' ).on( 'change', onChangeControl );
}

function submitEditDialogDone( tRsp, sStatus, tJqXhr )
{
  location.reload();
}
