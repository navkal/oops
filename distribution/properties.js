// Copyright 2017 Panel Spy.  All rights reserved.

var g_tProperties = window.opener.g_tTreeMap[g_sPath];

$( document ).ready( loadProperties );

function loadProperties()
{
  if ( g_tProperties )
  {
    showProperties();
  }
  else
  {
    getProperties();
  }
}

function getProperties()
{
  // Post request to server
  var tPostData = new FormData();
  tPostData.append( "objectTable", ( ( g_sType == 'device' ) ? "device" : "cirobj" ) );
  tPostData.append( "objectSelector", ( ( g_sType == 'device' ) ? g_sOid : g_sPath ) );

  $.ajax(
    "getObject.php",
    {
      type: 'POST',
      processData: false,
      contentType: false,
      dataType : 'json',
      data: tPostData
    }
  )
  .done( saveProperties )
  .fail( handleAjaxError );
}

function saveProperties( tRsp, sStatus, tJqXhr )
{
  g_tProperties = tRsp;

  // If main window already has this element, update it
  if ( window.opener.g_tTreeMap[g_tProperties["path"]] )
  {
    window.opener.g_tTreeMap[g_tProperties["path"]] = tRsp;
  }

  showProperties();
}

function showProperties()
{
  // Display title
  var sTitle = '';
  var sType = g_tProperties['object_type'] ? g_tProperties['object_type'].toLowerCase() : 'device';
  switch( sType )
  {
    case 'panel':
    case 'transformer':
      var aPath = g_tProperties['path'].split( '.' );
      sTitle = aPath[ aPath.length - 1 ];
      aSplit = sTitle.split( '-' );
      if ( ( aSplit.length > 1 ) && /^\d+$/.test( aSplit[0] ) )
      {
        sTitle = aSplit.splice( 1 ).join( '-' );
      }
      break;

    case 'circuit':
      sTitle = g_tProperties['description'];
      break;

    case 'device':
    default:
      sTitle = g_tProperties['name'];
      break;
  }
  $( "#propertiesTitle" ).html( sTitle );

  // Enable/disable buttons
  $( '#btnUp' ).prop( 'disabled', ! g_tProperties.parent_path );
  $( '#btnDown' ).prop( 'disabled', ( window.opener.g_iPropertiesTrailIndex >= ( window.opener.g_aPropertiesTrail.length - 1 ) ) );

  // Build map of labels and values for display
  var tDisplayProps = {};
  var aKeys = Object.keys( g_tProperties );
  for ( var i = 0; i < aKeys.length; i++ )
  {
    // Get display label
    var sKey = aKeys[i];
    var tRule =  g_tPropertyRules[sKey];
    var sLabel = ( tRule && tRule.showInPropertiesWindow ) ? tRule.label : null;

    if ( sLabel )
    {
      // Get display value
      var sVal = g_tProperties[sKey];
      if ( Array.isArray( sVal ) )
      {
        sVal = sVal.length;
      }

      // Save pair in map
      tDisplayProps[sLabel] = sVal;
    }
    else console.log( "=> Omitted field: " + sKey );
  }

  // Build layout of property display
  g_sPropertySortContext = g_tPropertySortContexts.propertiesWindow;
  aKeys = Object.keys( tDisplayProps ).sort( comparePropertyIndex );
  var sTbody = "";
  for ( var i = 0; i < aKeys.length; i++ )
  {
    var sKey = aKeys[i];
    var sVal = tDisplayProps[sKey];
    if ( sVal != '' )
    {
      var sColor = ( sKey == "Error" ) ? "color:red;" : '';
      sTbody += '<tr><td style="text-align:right;' + sColor + '"><b>' + sKey + '</b></td><td style="' + sColor + '">' + sVal + '</td></tr>';
    }
  }

  // Display properties
  $( "#objectLayout" ).html( sTbody );
}

function goUp()
{
  var tButton = window.opener.$( '#circuitTree a[path="' +  g_tProperties.parent_path + '"] .propertiesButton' );
  tButton.addClass( 'btnUp' );
  tButton.click();
}

function goDown()
{
  var aPath = window.opener.g_aPropertiesTrail.slice( 0, window.opener.g_iPropertiesTrailIndex + 2 );
  var sPath = aPath.join( '.' );
  var tButton = window.opener.$( '#circuitTree a[path="' +  sPath + '"] .propertiesButton' );
  tButton.addClass( 'btnDown' );
  tButton.click();
}
