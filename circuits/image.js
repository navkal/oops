// Copyright 2017 Panel Spy.  All rights reserved.

function resizeWindow()
{
  var tImg = new Image();
  tImg.src = $("img").attr("src");

  // Get window aspect
  var nWinWidth = $( window ).width();
  var nWinHeight = $( window ).height();
  var nWinAspect = nWinWidth / nWinHeight;

  // Get image aspect
  var nImgWidth = tImg.naturalWidth;
  var nImgHeight = tImg.naturalHeight;
  var nImgAspect = nImgWidth / nImgHeight;

  // Measure discrepancy between the two aspects
  var nDiscrepancy = Math.abs( ( nWinAspect / nImgAspect ) - 1 );
  console.log( '=> Image aspect discrepancy=' + nDiscrepancy );

  // If default aspect does not fit image, resize the window
  if ( nDiscrepancy > 0.1 )
  {
    var nHeight = nWinWidth * tImg.naturalHeight / tImg.naturalWidth;
    window.resizeTo( nWinWidth, nHeight );
  }
}


function goBack( tEvent, sPath )
{
  var tOpener = window.opener;
  var sTitle = tOpener.document.title;

  // Establish pointer to main window
  var tMain = null;
  if ( sTitle.indexOf( 'Topology' ) == 0 )
  {
    // Opener is Topology page.  Find main window.
    try
    {
      var tOpenerOpener = tOpener.opener;

      try
      {
        // Determine whether original main window is available
        var sTestTitle = tOpenerOpener.document.title;

        // No exception: Original main window available
        tMain = tOpenerOpener;
      }
      catch( e )
      {
        // Exception: Original main window not available

        // Determine whether reopened main window is available
        if ( tOpener.g_tMainWindow )
        {
          if ( tOpener.g_tMainWindow.closed )
          {
            tOpener.g_tMainWindow = null;
          }
          else
          {
            try
            {
              sTestTitle = tOpener.g_tMainWindow.document.title;
            }
            catch( e )
            {
              // Reopened main window not available
              tOpener.g_tMainWindow = null;
            }
          }
        }

        tMain = tOpener.g_tMainWindow;
      }
    }
    catch( e )
    {
      // Exception: Could not get second-level opener
    }
  }
  else
  {
    // Opener is main window
    tMain = tOpener;
  }


  // Find panel on main window
  var sGotoUrl = '/?goto=' + sPath;
  if ( tMain )
  {
    // Main window is available: use it

    if ( tMain.document.title.indexOf( 'Circuits' ) == 0 )
    {
      // Main window is currently on Circuits page
      tMain.g_sSearchTargetPath = sPath;
      tMain.navigateToSearchTarget();
    }
    else
    {
      // Main window is not currently on Circuits page
      tMain.location.assign( sGotoUrl );
    }
  }
  else
  {
    // Main window is not available: Open a new one
    try
    {
      tOpener.g_tMainWindow = window.open( sGotoUrl, 'Main' );
    }
    catch( e )
    {
      alert( 'Error: Could not reopen main window.' );
      tOpener.close();
    }
  }
}
