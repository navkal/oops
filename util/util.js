// Copyright 2017 Panel Spy.  All rights reserved.

// Map of property rules, initialized in display order
var displayIndex = 0;
var g_tPropertySortContexts =
{
  propertiesWindow: 'propertiesWindow',
  sortableTable: 'sortableTable'
};
var g_sPropertySortContext = null;
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
  facility_fullname:
  {
    label: "Facility",
    showInPropertiesWindow: false,
    showInSortableTable: true,
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
  event_target:
  {
    label: "Target",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  event_result:
  {
    label: "Result",
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
  three_phase:
  {
    label: "Circuits Grouped as Three-Phase",
    showInPropertiesWindow: true,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  phase_b_tail:
  {
    label: "Phase B Connection",
    showInPropertiesWindow: true,
    showInSortableTable: false,
    displayIndex: displayIndex ++
  },
  phase_c_tail:
  {
    label: "Phase C Connection",
    showInPropertiesWindow: true,
    showInSortableTable: false,
    displayIndex: displayIndex ++
  },
  loc_new:
  {
    label: "Current Location",
    showInPropertiesWindow: true,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  loc_old:
  {
    label: "Previous Location",
    showInPropertiesWindow: true,
    showInSortableTable: true,
    columnType: 'text', // Controls text-align
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
    showInPropertiesWindow: true,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  transformers:
  {
    label: "Transformers",
    showInPropertiesWindow: true,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  circuits:
  {
    label: "Circuits",
    showInPropertiesWindow: true,
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
  remove_object_type:
  {
    label: "Type",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  remove_object_origin:
  {
    label: "Origin",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  remove_comment:
  {
    label: "Remove Comment",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  restore_comment:
  {
    label: "Restore Comment",
    showInPropertiesWindow: false,
    showInSortableTable: false,
    displayIndex: displayIndex ++
  },
  restore_object:
  {
    label: "Restore",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'control',
    controlType: 'restore',
    customizeButton:
      function( sId )
      {
        var tRow = findSortableTableRow( sId );
        return ' title="Restore ' + tRow.remove_object_type + '" ';
      },
    displayIndex: displayIndex ++
  },
  panel_image:
  {
    label: "Panel Image",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'control',
    controlType: 'image_by_path',
    displayIndex: displayIndex ++
  },
  activity_log:
  {
    label: "Activity Log",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'control',
    controlType: 'activity_log',
    displayIndex: displayIndex ++
  },
  update_circuit:
  {
    label: "Update Circuit",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'control',
    controlType: 'update',
    customizeButton:
      function( sId )
      {
        var tRow = findSortableTableRow( sId );
        var aPath = tRow.path.split( '.' );
        return ' title="Update [' + aPath[aPath.length-2] + '.' + aPath[aPath.length-1] + ']" ';
      },
    displayIndex: displayIndex ++
  },
  update_panel:
  {
    label: "Update Panel",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'control',
    controlType: 'update',
    customizeButton:
      function( sId )
      {
        var tRow = findSortableTableRow( sId );
        return ' title="Update [' + tRow.name + ']" ';
      },
    displayIndex: displayIndex ++
  },
  update_transformer:
  {
    label: "Update Transformer",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'control',
    controlType: 'update',
    customizeButton:
      function( sId )
      {
        var tRow = findSortableTableRow( sId );
        return ' title="Update [' + tRow.name + ']" ';
      },
    displayIndex: displayIndex ++
  },
  update_device:
  {
    label: "Update Device",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'control',
    controlType: 'update',
    customizeButton:
      function( sId )
      {
        var tRow = findSortableTableRow( sId );
        return ' title="Update [' + tRow.name + ']" ';
      },
    displayIndex: displayIndex ++
  },
  update_location:
  {
    label: "Update Location",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'control',
    controlType: 'update',
    customizeButton:
      function( sId )
      {
        var tRow = findSortableTableRow( sId );
        return ' title="Update [' + tRow.formatted_location + ']" ';
      },
    displayIndex: displayIndex ++
  },
  remove_circuit:
  {
    label: "Remove Circuit",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'control',
    controlType: 'remove',
    customizeButton:
      function( sId )
      {
        var tRow = findSortableTableRow( sId );
        var aPath = tRow.path.split( '.' );
        return ' title="Remove [' + aPath[aPath.length-2] + '.' + aPath[aPath.length-1] + ']" ';
      },
    displayIndex: displayIndex ++
  },
  remove_panel:
  {
    label: "Remove Panel",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'control',
    controlType: 'remove',
    customizeButton:
      function( sId )
      {
        var tRow = findSortableTableRow( sId );
        return ' title="Remove [' + tRow.name + ']" ';
      },
    displayIndex: displayIndex ++
  },
  remove_transformer:
  {
    label: "Remove Transformer",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'control',
    controlType: 'remove',
    customizeButton:
      function( sId )
      {
        var tRow = findSortableTableRow( sId );
        return ' title="Remove [' + tRow.name + ']" ';
      },
    displayIndex: displayIndex ++
  },
  remove_device:
  {
    label: "Remove Device",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'control',
    controlType: 'remove',
    customizeButton:
      function( sId )
      {
        var tRow = findSortableTableRow( sId );
        return ' title="Remove [' + tRow.description + ']" ';
      },
    displayIndex: displayIndex ++
  },
  remove_location:
  {
    label: "Remove Location",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'control',
    controlType: 'remove',
    customizeButton:
      function( sId )
      {
        var tRow = findSortableTableRow( sId );
        return ' title="Remove [' + tRow.formatted_location + ']" ';
      },
    displayIndex: displayIndex ++
  },
  update_user:
  {
    label: "Update User",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'control',
    controlType: 'update',
    customizeButton:
      function( sId )
      {
        var tRow = findSortableTableRow( sId );
        return ' title="Update [' + tRow.username + ']" ';
      },
    displayIndex: displayIndex ++
  },
  remove_user:
  {
    label: "Remove User",
    showInPropertiesWindow: false,
    showInSortableTable: true,
    columnType: 'control',
    controlType: 'remove',
    customizeButton:
      function( sId )
      {
        var tRow = findSortableTableRow( sId );
        return ' title="Remove [' + tRow.username + ']" ';
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
    label: "Location",
    showInPropertiesWindow: false,
    showInSortableTable: false,
    displayIndex: displayIndex ++
  },
  note:
  {
    label: "Note",
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

    var bShowInCurrentContext =
      ( g_tPropertyRules[sKey].showInPropertiesWindow && g_sPropertySortContext == g_tPropertySortContexts.propertiesWindow )
      ||
      ( g_tPropertyRules[sKey].showInSortableTable && g_sPropertySortContext == g_tPropertySortContexts.sortableTable );

    if ( bShowInCurrentContext  )
    {
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

var g_aActivityLogWindows = [];

function openActivityLogWindow( tEvent )
{
  var tAnchor = $( tEvent.target ).closest( "a" );
  var sObjectId = tAnchor.attr( "object_id" );

  var sSubtitle = '';
  var tRow = findSortableTableRow( sObjectId );
  switch( g_sSortableTableEditWhat )
  {
    case 'Panel':
    case 'Transformer':
    case 'Device':
      sSubtitle = tRow.name;
      break;

    case 'Circuit':
      sSubtitle = tRow.circuit_decription;
      break;
  }

  var sUrl = '/activity/activityWindow.php?subtitle=' + sSubtitle + '&type=' + g_sSortableTableEditWhat + '&id=' + sObjectId;

  return childWindowOpen( tEvent, g_aActivityLogWindows, sUrl, "Activity Log", sObjectId, 800, 900, false );
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


// -> -> -> Error messages -> -> ->

  function clearMessages( sDivSelector, sListSelector )
  {
    if ( ! sDivSelector )
    {
      sDivSelector = '#messages';
    }

    if ( ! sListSelector )
    {
      sListSelector = '#messageList';
    }

    $( ".has-error" ).removeClass( "has-error" );
    $( sDivSelector ).css( "display", "none" );
    $( sListSelector ).html( "" );
  }

  function showMessages( aMessages, sDivSelector, sListSelector )
  {
    if ( ! sDivSelector )
    {
      sDivSelector = '#messages';
    }

    if ( ! sListSelector )
    {
      sListSelector = '#messageList';
    }

    if ( aMessages.length > 0 )
    {
      for ( var index in aMessages )
      {
        $( sListSelector ).append( '<li>' + aMessages[index] + '</li>' );
      }
      $( sDivSelector ).css( "display", "block" );
    }
  }

  function highlightErrors( aSelectors )
  {
    // Highlight pertinent fields
    for ( var iSelector in aSelectors )
    {
      var sSelector = aSelectors[iSelector];
      $( sSelector ).closest( '.form-group' ).addClass( 'has-error' );
    }
  }

// <- <- <- Error messages <- <- <-


// -> -> -> Editing -> -> ->

function resetChangeHandler()
{
  $( 'input,select,textarea' ).off( 'change' );
  $( 'input,select,textarea' ).on( 'change', onChangeControl );
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

// <- <- <- Editing <- <- <-


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

function showSpinner()
{
  $( '#spinner' ).css( 'display', 'block' );
}

function hideSpinner()
{
  $( '#spinner' ).css( 'display', 'none' );
}

function htmlentities_undo( sHtmlEntitiesText )
{
  return $( '<span/>' ).html( sHtmlEntitiesText ).text()  
}
