// Copyright 2017 Panel Spy.  All rights reserved.

var g_aPropertiesWindows = [];
var g_tTreeMap = {};
var g_sImageButton = '<button class="btn btn-link btn-xs" onclick="openImageWindowEtc(event)" title="Panel Image" ><span class="glyphicon glyphicon-picture" style="font-size:18px;" ></span></button>';
var g_sPropertiesButton = '<button class="btn btn-link btn-xs propertiesButton" onclick="openPropertiesWindow(event)" title="Properties" ><span class="glyphicon glyphicon-info-sign" style="font-size:18px;" ></span></button>';
var g_sSearchTargetPath = '';
var g_aPropertiesTrail = null;
var g_iPropertiesTrailIndex = null;

$( document ).ready( initView );

function initView()
{
  $( window ).on( 'unload', closeChildWindows );
  $( window ).resize( resizeTree );

  // Capture goto parameter, if any
  g_sSearchTargetPath = $( '#goto' ).val();

  // Initialize tree
  resizeTree();
  getTreeNode( "" );
}

function getTreeNode( sPath )
{
  // Post request to server
  var tPostData = new FormData();
  tPostData.append( "objectType", "GenericDistributionObject" );
  tPostData.append( "objectSelector", sPath );

  $.ajax(
    "distribution/getObject.php",
    {
      type: 'POST',
      processData: false,
      contentType: false,
      dataType : 'json',
      data: tPostData
    }
  )
  .done( insertTreeNode )
  .fail( handleAjaxError );
}

function insertTreeNode( tRsp, sStatus, tJqXhr )
{
  // Get path and related values
  var sPath = tRsp.path;
  var sEncode = sPath.replace( /\./g, g_sReservedDelimiter );
  var aPath = sPath.split( "." );
  var nDepth = aPath.length;
  var sLabel = ( tRsp.error ? tRsp.error : tRsp.label );
  var sPadNode = "" + ( nDepth * 15 ) + "px";
  var sPadCollapse = "" + ( ( nDepth + 1 ) * 15 ) + "px";
  var sType = tRsp.object_type;
  var sOid = tRsp.id;
  var sErrorStyle =  tRsp.error ? 'color:red;' : '';

  // Display tree node
  var sNode = "";
  sNode += '<a href="#' + sEncode + '" class="list-group-item clearfix" data-toggle="collapse" onclick="toggleFolder(event);" ondblclick="toggleFolder(event);" path="' + sPath + '" type="' + sType + '" oid="' + sOid + '" title="' + sType + ' ' + sPath + '" style="padding-left:' + sPadNode + ';' + sErrorStyle + '" >';
  sNode += '<i class="glyphicon glyphicon-chevron-down toggle"></i>';
  sNode += sLabel;
  sNode += '<span class="pull-right">';
  sNode += tRsp.panel_image ? g_sImageButton : '';
  sNode += g_sPropertiesButton;
  sNode += '</span>';
  sNode += '</a>';

  // Open block of collapsed content
  var sCollapse = "";
  sCollapse += '<div class="list-group collapse in" id="' + sEncode + '" >';

  // Sort children and load into collapsed content
  var aChildren = tRsp.children;
  var aChildInfo = [];
  for ( var iChild = 0; iChild < aChildren.length; iChild ++ )
  {
    var sChildOid = aChildren[iChild][0];
    var sChildPath = aChildren[iChild][1];
    var sChildLabel = aChildren[iChild][2];
    var sChildType = aChildren[iChild][3];
    var sChildImage = aChildren[iChild][4];
    aChildInfo.push( { oid: sChildOid, path: sChildPath, label: sChildLabel, type: sChildType, panel_image: sChildImage } );
  }
  aChildInfo.sort( compareNodes );
  for ( var iChild = 0; iChild < aChildInfo.length; iChild ++ )
  {
    sCollapse += '<a href="javascript:void(null)" class="list-group-item clearfix collapsed" data-toggle="collapse" onclick="toggleFolder(event);"  ondblclick="toggleFolder(event);"path="' + aChildInfo[iChild].path + '" type="' + aChildInfo[iChild].type + '" oid="' + aChildInfo[iChild].oid + '" title="' + aChildInfo[iChild].type + ' ' + aChildInfo[iChild].path + '" style="padding-left:' + sPadCollapse + '" >';
    sCollapse += '<i class="glyphicon glyphicon-chevron-right toggle"></i>';
    sCollapse += aChildInfo[iChild].label;
    sCollapse += '<span class="pull-right">';
    sCollapse += aChildInfo[iChild].panel_image ? g_sImageButton : '';
    sCollapse += g_sPropertiesButton;
    sCollapse += '</span>';
    sCollapse += '</a>';
  }

  // Sort devices and load into collapsed content
  var aDevices = tRsp.devices;
  var aDeviceInfo = [];
  for ( var iDevice = 0; iDevice < aDevices.length; iDevice ++ )
  {
    var sDeviceOid = aDevices[iDevice][0];
    var sDevicePath = sPath + "." + sDeviceOid;
    var sDeviceLabel = aDevices[iDevice][5];
    aDeviceInfo.push( { oid: sDeviceOid, path: sDevicePath, label: sDeviceLabel } );
  }

  aDeviceInfo.sort( compareNodes );
  for ( var iDevice = 0; iDevice < aDeviceInfo.length; iDevice ++ )
  {
    sCollapse += '<a href="javascript:void(null)" class="list-group-item clearfix" path="' + aDeviceInfo[iDevice].path + '" type="Device" oid="' + aDeviceInfo[iDevice].oid + '" title="Device on ' + sPath + '" style="padding-left:' + sPadCollapse + '" >';
    sCollapse += aDeviceInfo[iDevice].label;
    sCollapse += '<span class="pull-right">';
    sCollapse += g_sPropertiesButton;
    sCollapse += '</span>';
    sCollapse += '</a>';
  }

  // Close collapsed content block
  sCollapse += '</div>';

  // Load collapsed content
  var sSubtree = sNode + sCollapse;

  if ( Object.keys( g_tTreeMap ).length == 0 )
  {
    $( "#circuitTree" ).append( sSubtree );
  }
  else
  {
    var tReplace = $( '#circuitTree a[path="' + sPath + '"]' );
    if ( $( '#' + sEncode ).length == 0 )
    {
      tReplace.replaceWith( sSubtree );
    }
  }

  var nCollapseElements = aChildren.length + aDevices.length;
  if ( ( aChildren.length + aDevices.length ) == 0 )
  {
    $( '#circuitTree a[path="' + sPath + '"] .toggle' ).addClass( "no-children" );
  }

  // Set toggle completion handlers
  $( '#' + sEncode ).on( 'shown.bs.collapse', collapseShown );
  $( '#' + sEncode ).on( 'hidden.bs.collapse', collapseHidden );

  // Set tooltips on tree toggles
  setToggleTooltips();

  // Insert node in tree map
  g_tTreeMap[sPath] = tRsp;

  // Handle continuation of navigation to search result
  if ( g_sSearchTargetPath )
  {
    navigateToSearchTarget();
  }
}

function compareNodes( d1, d2 )
{
  // Extract labels
  var sLabel1 = d1.label;
  var sLabel2 = d2.label;

  // Extract prefixes
  var sPrefix1 = sLabel1.split( ":" )[0];
  var sPrefix2 = sLabel2.split( ":" )[0];

  // Determine whether prefixes are numeric
  var iNum1 = parseInt( sPrefix1 );
  var iNum2 = parseInt( sPrefix2 );

  var iResult = 0;
  if ( ! isNaN( iNum1 ) && ! isNaN( iNum2 ) )
  {
    // Compare numerically
    iResult = iNum1 - iNum2;
  }

  // If no difference found, compare full text
  if ( iResult == 0 )
  {
    // Compare alphabetically
    iResult = sLabel1.localeCompare( sLabel2 );
  }

  return iResult;
}

Element.prototype.documentOffsetTop = function()
{
  return this.offsetTop + ( this.offsetParent ? this.offsetParent.documentOffsetTop() : 0 );
};

function navigateToSearchTarget()
{
  // Find first collapsed node hiding search target
  var aPath = g_sSearchTargetPath.split( '.' );
  var bExpanded = true;
  var sNavPath = '';
  var tNavNode = null;
  for ( var iLen = 0; ( iLen < aPath.length ) && bExpanded; iLen ++ )
  {
    sNavPath = aPath.slice( 0, iLen + 1 ).join( '.' );
    tNavNode = $( '#circuitTree a[path="' + sNavPath + '"]' );
    if ( tNavNode.length == 0 )
    {
      console.log( '=> ERROR navigating to path: <' + g_sSearchTargetPath + '>' );
      console.log( '=> Ancestor does not exist: <' + sNavPath + '>' );
    }
    bExpanded = tNavNode.find( ".toggle.glyphicon-chevron-down" ).length > 0;
  }

  // Terminate or continue navigation to search target
  if ( sNavPath == g_sSearchTargetPath )
  {
    // Navigation done: Update display and clean up

    // Highlight search target in tree
    var tSearchTarget = $( '#circuitTree a[path="' + g_sSearchTargetPath + '"]' );
    $( '.searchTarget' ).removeClass( 'searchTarget' );
    tSearchTarget.addClass( 'searchTarget' );

    // Auto-scroll tree to search target
    scrollToCenter( $( '#circuitTree' ), tSearchTarget );

    // Set tooltips on toggle buttons
    setToggleTooltips();

    // Clear search target path
    g_sSearchTargetPath = '';

    // Clear the goto parameter
    $( '#goto' ).val( '' );

    // Mark search target so 'shown' event handler can center search target
    $( '.searchTargetToCenter' ).removeClass( 'searchTargetToCenter' );
    tSearchTarget.addClass( 'searchTargetToCenter' );
  }
  else
  {
    // Navigation continues: Expand node hiding search result
    tNavNode.trigger( 'click' );
  }
}

function toggleFolder( tEvent )
{
  var tItem = $( tEvent.target ).closest( '.list-group-item' );

  // If we haven't already determined that it's a leaf, toggle it
  if ( ! tItem.find( '.toggle' ).hasClass( "no-children" ) )
  {
    var sPath = $( tItem ).attr( "path" );
    if ( ! g_tTreeMap[sPath] )
    {
      // Expand for the first time
      getTreeNode( sPath );
    }
    else
    {
      switch( tEvent.type )
      {
        case 'click':
          if ( g_sSearchTargetPath )
          {
            $( tItem.attr( 'href' ) ).collapse( 'show' );
            tItem.find( '.toggle' ).removeClass( 'glyphicon-chevron-right' ).addClass( 'glyphicon-chevron-down' );
            navigateToSearchTarget();
          }
          break;

        case 'dblclick':
          if ( tItem.find( ".toggle.glyphicon-chevron-down" ).length > 0 )
          {
            // Collapse all descendants of this target
            $( '.collapse', $( tItem.attr( 'href' ) ) ).collapse( 'hide' );
          }
          break;
      }
    }
  }
}

function collapseShown( tEvent )
{
  collapseComplete( tEvent, true );

  // For special case, when navigating to a search target that is present but hidden
  var tSearchTarget = $( '.searchTargetToCenter' );
  if ( tSearchTarget.length )
  {
    // Determine whether search target is showing
    var tDiv = tSearchTarget.closest( 'div' );
    var sId = tDiv.attr( 'id' );
    var aSplit = sId.split( g_sReservedDelimiter );
    var bShowing = tDiv.hasClass( 'in' );
    while( bShowing && aSplit.length )
    {
      sId = aSplit.join( g_sReservedDelimiter );
      tDiv = $( '#' + sId );
      bShowing = tDiv.hasClass( 'in' );
      aSplit.pop();
    }

    if ( bShowing )
    {
      tSearchTarget.removeClass( 'searchTargetToCenter' );
      scrollToCenter( $( '#circuitTree' ), tSearchTarget );
    }
  }
}

function collapseHidden( tEvent )
{
  $( '.searchTargetToCenter' ).removeClass( 'searchTargetToCenter' );
  collapseComplete( tEvent, false );
}

function collapseComplete( tEvent, bShown )
{
  var sId = $( tEvent.target ).attr( "id" );
  var tItem = $( '.list-group-item[href="#' + sId + '"]' );
  var tToggle = tItem.find( '.toggle' );

  if ( ! tToggle.hasClass( 'no-children' ) )
  {
    if ( bShown )
    {
      tToggle.removeClass( 'glyphicon-chevron-right' ).addClass( 'glyphicon-chevron-down' );
    }
    else
    {
      tToggle.removeClass( 'glyphicon-chevron-down' ).addClass( 'glyphicon-chevron-right' );
    }

    setToggleTooltips();
  }
}

function setToggleTooltips()
{
  // Right-arrow shows tooltip of parent
  $( '.toggle.glyphicon-chevron-right' ).each(
    function()
    {
      $( this ).attr( 'title', $( this ).parent().attr( 'title' ) );
    }
  );

  // Down-arrow shows collapse-all instruction
  $( '.toggle.glyphicon-chevron-down:not(.no-children)' ).attr( 'title', 'Double click to collapse all' );
}

function resizeTree()
{
  var tFooter = $( '.navbar-fixed-bottom' );
  var nHeightMinus = tFooter.length ? ( tFooter.height() + 80 ) : 40;
  $( '#circuitTree' ).css( 'max-height', $( window ).height() - $( '#circuitTree' ).position().top - nHeightMinus );
}

function scrollToCenter( tContainer, tItem )
{
  tContainer.scrollTop( tContainer.scrollTop() + ( tItem.position().top - tContainer.position().top ) - ( tContainer.height() / 2 ) + ( tItem.height() / 2 ) );
}

function openPropertiesWindow( tEvent )
{
  var tTarget = $( tEvent.target );
  var tAnchor = tTarget.closest( 'a' );
  var sPath = tAnchor.attr( "path" );
  var sType = tAnchor.attr( "type" );
  var sOid = tAnchor.attr( "oid" );

  // Update tree
  var bFromPropertiesWindow = tTarget.hasClass( 'btnUp' ) || tTarget.hasClass( 'btnDown' );
  if ( bFromPropertiesWindow )
  {
    // User clicked arrow button in Properties window
    g_iPropertiesTrailIndex += ( tTarget.hasClass( 'btnUp' ) ? -1 : 1 );
    tTarget.removeClass( 'btnUp' ).removeClass( 'btnDown' );
    g_sSearchTargetPath = sPath;
    navigateToSearchTarget();
  }
  else
  {
    // User clicked Properties button on tree node
    g_aPropertiesTrail = sPath.split( '.' );
    g_iPropertiesTrailIndex = g_aPropertiesTrail.length - 1;
    $( '.searchTarget' ).removeClass( 'searchTarget' );
    tAnchor.addClass( 'searchTarget' );
  }

  // Open the window
  var sDirectory = bFromPropertiesWindow ? '' : 'distribution/';
  var sUrl = sDirectory + 'properties.php?path=' + sPath + '&type=' + sType + '&oid=' + sOid;
  childWindowOpen( tEvent, g_aPropertiesWindows, sUrl, "Properties", sPath, 450, 500, false );
}

function openImageWindowEtc( tEvent )
{
  var tTarget = $( tEvent.target );
  var tAnchor = tTarget.closest( 'a' );
  $( '.searchTarget' ).removeClass( 'searchTarget' );
  tAnchor.addClass( 'searchTarget' );

  openImageWindow( tEvent );
}

function closeChildWindows()
{
  childWindowsClose( g_aImageWindows );
  childWindowsClose( g_aPropertiesWindows );
}
