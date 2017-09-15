// Copyright 2017 Panel Spy.  All rights reserved.

// Map of property rules, initialized in display order
var displayIndex = 0;
var g_tPropertyRules =
{
  error:
  {
    label: "Error",
    showInPropertiesWindow: true,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  object_type:
  {
    label: "Type",
    showInPropertiesWindow: true,
    showInSortableTable: false,
    displayIndex: displayIndex ++
  },
  index:
  {
    label: "Index",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'index',
    displayIndex: displayIndex ++
  },
  timestamp:
  {
    label: "Timestamp",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'timestamp',
    displayIndex: displayIndex ++
  },
  event_type:
  {
    label: "Event",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  event_trigger:
  {
    label: "Triggered By",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  facility_fullname:
  {
    label: "Facility",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  event_target:
  {
    label: "Target",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  event_description:
  {
    label: "Description",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  username:
  {
    label: "Username",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  user_description:
  {
    label: "Description",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  role:
  {
    label: "Role",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  auth_facilities:
  {
    label: "Facilities",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  status:
  {
    label: "Status",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  first_name:
  {
    label: "First Name",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  last_name:
  {
    label: "Last Name",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  email_address:
  {
    label: "Email Address",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  organization:
  {
    label: "Organization",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  path:
  {
    label: "Path",
    showInPropertiesWindow: true,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  source_path:
  {
    label: "Circuit",
    showInPropertiesWindow: true,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  number:
  {
    label: "Number",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  name:
  {
    label: "Name",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  source:
  {
    label: "Source",
    showInPropertiesWindow: true,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  voltage:
  {
    label: "Voltage",
    showInPropertiesWindow: true,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  loc_new:
  {
    label: "Location",
    showInPropertiesWindow: true,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  loc_old:
  {
    label: "Old Location",
    showInPropertiesWindow: true,
    showInSortableTable: true,
    columnType: 'text', // Controls text-align
    displayIndex: displayIndex ++
  },
  loc_type:
  {
    label: "Location Type",
    showInPropertiesWindow: true,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  loc_descr:
  {
    label: "Location Description",
    showInPropertiesWindow: true,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  circuit_descr:
  {
    label: "Circuit Description",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  panel_descr:
  {
    label: "Panel Description",
    showInPropertiesWindow: true,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  transformer_descr:
  {
    label: "Transformer Description",
    showInPropertiesWindow: true,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  panels:
  {
    label: "Panels",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  transformers:
  {
    label: "Transformers",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  circuits:
  {
    label: "Circuits",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  devices:
  {
    label: "Devices",
    showInPropertiesWindow: true,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  children:
  {
    label: "Children",
    showInPropertiesWindow: true,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  image_file:
  {
    label: "Image",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'control',
    controlType: 'image',
    displayIndex: displayIndex ++
  },
  update_circuit:
  {
    label: "Update",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'control',
    controlType: 'update',
    customizeButton:
      function( sDeviceId )
      {
        return ' title="Update Circuit" ';
      },
    displayIndex: displayIndex ++
  },
  update_panel:
  {
    label: "Update",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'control',
    controlType: 'update',
    customizeButton:
      function( sDeviceId )
      {
        return ' title="Update Panel" ';
      },
    displayIndex: displayIndex ++
  },
  update_transformer:
  {
    label: "Update",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'control',
    controlType: 'update',
    customizeButton:
      function( sDeviceId )
      {
        return ' title="Update Transformer" ';
      },
    displayIndex: displayIndex ++
  },
  update_device:
  {
    label: "Update",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'control',
    controlType: 'update',
    customizeButton:
      function( sDeviceId )
      {
        return ' title="Update Device" ';
      },
    displayIndex: displayIndex ++
  },
  update_location:
  {
    label: "Update",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'control',
    controlType: 'update',
    customizeButton:
      function( sLocationId )
      {
        return ' title="Update Location" ';
      },
    displayIndex: displayIndex ++
  },
  update_user:
  {
    label: "Update",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'control',
    controlType: 'update',
    customizeButton:
      function( sUsername )
      {
        return ' title="Update '+"'"+sUsername+"'"+'" ';
      },
    displayIndex: displayIndex ++
  },
  remove_user:
  {
    label: "Remove",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'control',
    controlType: 'remove',
    customizeButton:
      function( sUsername )
      {
        return ' title="Remove '+"'"+sUsername+"'"+'" ';
      },
    displayIndex: displayIndex ++
  },
  parent_path:
  {
    label: "Parent",
    showInPropertiesWindow: false,
    showInSortableTable: false,
    displayIndex: displayIndex ++
  },
  panel_photo_input_group:
  {
    label: "Panel Photo",
    showInPropertiesWindow: false,
    showInSortableTable: false,
    displayIndex: displayIndex ++
  },
  id:
  {
    label: "ID",
    showInPropertiesWindow: false,
    showInSortableTable: false,
    displayIndex: displayIndex ++
  },
  parent_id:
  {
    label: "Parent ID",
    showInPropertiesWindow: false,
    showInSortableTable: false,
    displayIndex: displayIndex ++
  },
  room_id:
  {
    label: "Room ID",
    showInPropertiesWindow: false,
    showInSortableTable: false,
    displayIndex: displayIndex ++
  }
};

function comparePropertyIndex( sLabel1, sLabel2 )
{
  var idx1 = 0;
  var idx2 = 0;

  var aKeys = Object.keys( g_tPropertyRules );
  for ( var iKey in aKeys )
  {
    var sKey = aKeys[iKey];
    var sLabel = g_tPropertyRules[sKey].label;
    if ( sLabel1 == sLabel )
    {
      idx1 = g_tPropertyRules[sKey].displayIndex;
    }
    else if ( sLabel2 == sLabel )
    {
      idx2 = g_tPropertyRules[sKey].displayIndex;
    }
  }

  return idx1 - idx2;
};

var g_aImageWindows = [];

function openImageWindow( tEvent )
{
  var tAnchor = $( tEvent.target ).closest( "a" );
  var sPath = tAnchor.attr( "path" );
  var sUrl = '../distribution/image.php?path=' + sPath;

  var nDefaultWidth = 800;
  var nDefaultAspect = 2550 / 3300;
  var nDefaultHeight = nDefaultWidth / nDefaultAspect;

  return childWindowOpen( tEvent, g_aImageWindows, sUrl, "Image", sPath, nDefaultWidth, nDefaultHeight, false );
}


// -> -> -> Manage child windows -> -> ->

// Open child window and save reference in array.
// - If opened with Click, save in element [0].
// - If opened with <key>+Click, save in new element.
function childWindowOpen( tEvent, aChildWindows, sUrl, sName, sNameSuffix, iWidth, iHeight, bAllowDefault )
{
  if ( tEvent.preventDefault )
  {
    tEvent.preventDefault();
  }
  if ( tEvent.stopPropagation )
  {
    tEvent.stopPropagation();
  }

  var iIndex, sWindowFeatures, bFocus;

  if ( bAllowDefault && ( tEvent.altKey || tEvent.shiftKey || tEvent.ctrlKey ) )
  {
    // User pressed a special key while clicking.  Allow browser default behavior.
    iIndex = aChildWindows.length;
    sName += "_" + sNameSuffix;
    sWindowFeatures = "";
    bFocus = false;
  }
  else
  {
    // User pressed no special key while clicking.  Override browser default behavior.
    iIndex = 0;
    var nLeft = parseInt( ( screen.availWidth / 2 ) - ( iWidth / 2 ) );
    var nTop = parseInt( ( screen.availHeight / 2 ) - ( iHeight / 2 ) );
    sWindowFeatures = "width=" + iWidth + ",height=" + iHeight + ",status,resizable,left=" + nLeft + ",top=" + nTop + ",screenX=" + nLeft + ",screenY=" + nTop + ",scrollbars=yes";
    bFocus = true;
  }

  // Open the new child window
  aChildWindows[iIndex] = window.open( sUrl, sName, sWindowFeatures );

  // Optionally focus on the new child window
  if ( bFocus )
  {
    aChildWindows[iIndex].focus();
  }

  return aChildWindows[iIndex];
}

// Close all child windows in given array
function childWindowsClose( aWindows )
{
  for ( var iIndex = 0; iIndex < aWindows.length; iIndex ++ )
  {
    aWindows[iIndex].close();
  }
}

// <- <- <- Manage child windows <- <- <-


// -> -> -> Edit forms -> -> ->

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

// <- <- <- Edit forms <- <- <-


// -> -> -> Error messages -> -> ->

  function clearMessages()
  {
    $( ".has-error" ).removeClass( "has-error" );
    $( "#messages" ).css( "display", "none" );
    $( "#messageList" ).html( "" );
  }

  function showMessages( aMessages )
  {
    if ( aMessages.length > 0 )
    {
      for ( var index in aMessages )
      {
        $( "#messageList" ).append( '<li>' + aMessages[index] + '</li>' );
      }
      $( "#messages" ).css( "display", "block" );
    }
  }

// <- <- <- Error messages <- <- <-



function handleAjaxError( tJqXhr, sStatus, sErrorThrown )
{
  clearWaitCursor();
  console.log( "=> ERROR=" + sStatus + " " + sErrorThrown );
  console.log( "=> HEADER=" + JSON.stringify( tJqXhr ) );
}

function setWaitCursor()
{
  $( '#view' ).css( 'cursor', 'wait' );
  $( '#spinner' ).css( 'display', 'block' );
  $( '#content' ).css( 'display', 'none' );
}

function clearWaitCursor()
{
  $( '#view' ).css( 'cursor', 'default' );
  $( '#spinner' ).css( 'display', 'none' );
  $( '#content' ).css( 'display', 'block' );
}
