// Copyright 2017 Panel Spy.  All rights reserved.

var g_tMainWindow = null;

$( document ).ready( init );

function init()
{
  // Set handlers
  $( window ).on( 'unload', closeChildWindows );
  tContainer = $( 'body>div' );
  tContainer.on( 'wheel', zoomByWheel );
  tContainer.on( 'mousedown', startPan );
  tContainer.on( 'mousemove', panDiagram );
  tContainer.on( 'mouseup', endPan );
  tContainer.on( 'mouseleave', endPan );

  // Generate hyperlinks to open image window
  $( 'a' ).each( makeHyperlink );
}

function makeHyperlink( i, tEl )
{
  var tAnchor = $( tEl );

  // Get original hyperlink
  var sLink = tAnchor.attr( 'xlink:href' );

  // Reconfigure hyperlink
  if ( sLink )
  {
    if ( sLink.toLowerCase().indexOf( 'http://' ) == 0 )
    {
      // Link is full URL
      tAnchor.attr( 'href', sLink );
      tAnchor.attr( 'target', '_blank' );
    }
    else
    {
      // Link is internal
      tAnchor.attr( 'href', 'javascript:void(null)' );

      if ( sLink == 'PANELSPY' )
      {
        // Link is to main window
        tAnchor.click( openMainWindow );
      }
      else
      {
        // Link is path
        tAnchor.attr( 'path', sLink );
        tAnchor.click( openImageWindow );
      }
    }

    // Remove original link
    tAnchor.removeAttr( 'xlink:href' );

    // Style clickable rectangle
    var tRect = tAnchor.find( 'rect' );
    tRect.css( 'fill', 'white' );
    tRect.css( 'stroke', 'black' );
    tRect.css( 'stroke-width', '5' );
    tRect.css( 'fill-opacity', '0.001' );
    tRect.css( 'stroke-opacity', '0.9' );
  }
}


function openMainWindow( tEvent )
{
  var tMain = window.opener;

  try
  {
    // Determine whether original main window is available
    var sTestTitle = tMain.document.title;
  }
  catch( e )
  {
    // Exception: Original main window not available

    // Determine whether reopened main window is available
    if ( g_tMainWindow )
    {
      if ( g_tMainWindow.closed )
      {
        g_tMainWindow = null;
      }
      else
      {
        try
        {
          sTestTitle = g_tMainWindow.document.title;
        }
        catch( e )
        {
          // Reopened main window not available
          g_tMainWindow = null;
        }
      }
    }

    tMain = g_tMainWindow;
  }

  // Open main window
  if ( tMain )
  {
    // Main window is available: use it

    if ( tMain.document.title.indexOf( 'Circuits' ) != 0 )
    {
      // Main window is not currently on Circuits page
      tMain.location.assign( '/' );
    }
  }
  else
  {
    // Main window is not available: Open a new one
    try
    {
      g_tMainWindow = window.open( '/', 'Main' );
    }
    catch( e )
    {
      alert( 'Error: Could not reopen main window.' );
      window.close();
    }
  }
}

function zoomByWheel( tEvent )
{
  var tOriginalEvent = tEvent.originalEvent;
  var bAlt = tOriginalEvent.altKey

  // If user pressed Alt key while turning wheel, zoom the diagram
  if ( bAlt )
  {
    // Suppress vertical scrolling
    tEvent.preventDefault();

    // Determine direction of zoom
    var iDelta = tOriginalEvent.wheelDelta /* most browsers */ || ( - tOriginalEvent.deltaY )/* Firefox */;
    zoomDiagram( iDelta > 0 );
  }
}

function zoomDiagram( bIn )
{
  var tDiagram = $( 'svg' );
  var nPercent = bIn ? 7/6 : 6/7;
  tDiagram.width( tDiagram.width() * nPercent );
  tDiagram.height( tDiagram.height() * nPercent );
}


// --> --> --> Pan image --> --> -->
var g_bPan = null;
var g_iPanX = null;
var g_iPanY = null;

function startPan( tEvent )
{
  g_bPan = true;
  g_iPanX = tEvent.clientX;
  g_iPanY = tEvent.clientY;
}

function panDiagram( tEvent )
{
  if ( g_bPan )
  {
    tEvent.preventDefault();

    // Set cursor for moving
    $( 'body>div' ).css( 'cursor', 'move' );

    // Pan the display
    $( window ).scrollTop( $( window ).scrollTop() - ( tEvent.clientY - g_iPanY ) );
    $( window ).scrollLeft( $( window ).scrollLeft() - ( tEvent.clientX - g_iPanX ) );

    // Save current mouse position
    g_iPanX = tEvent.clientX;
    g_iPanY = tEvent.clientY;
  }
}

function endPan( tEvent )
{
  // Restore normal cursors
  $( 'body>div' ).css( 'cursor', 'default' );
  $( 'a' ).css( 'cursor', 'pointer' );

  // Clear pan data
  g_bPan = null;
  g_iPanX = null;
  g_iPanY = null;
}

// <-- <-- <-- Pan image <-- <-- <--


function closeChildWindows()
{
  childWindowsClose( g_aImageWindows );
}
