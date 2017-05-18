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
  path:
  {
    label: "Path",
    showInPropertiesWindow: true,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  source_path:
  {
    label: "Source Path",
    showInPropertiesWindow: true,
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
    displayIndex: displayIndex ++
  },
  loc_descr:
  {
    label: "Location Description",
    showInPropertiesWindow: true,
    showInSortableTable: true,
    displayIndex: displayIndex ++
  },
  loc_type:
  {
    label: "Location Type",
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
  description:
  {
    label: "Description",
    showInPropertiesWindow: false,
    showInSortableTable: false,
    displayIndex: displayIndex ++
  },
  parent_path:
  {
    label: "Parent",
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


// -> -> -> Manage child windows -> -> ->

// Open child window and save reference in array.
// - If opened with Click, save in element [0].
// - If opened with <key>+Click, save in new element.
function childWindowOpen( tEvent, aChildWindows, sUrl, sName, sNameSuffix, iWidth, iHeight, bAllowDefault )
{
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

