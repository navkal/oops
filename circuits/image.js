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
  // Get main application window
  var tMain = window.opener;

  // Navigate according to current state of main window
  if ( tMain.document.title.indexOf( 'Circuits' ) == 0 )
  {
    // Currently on Circuits page.  Navigate directly to panel.
    tMain.g_sSearchTargetPath = sPath;
    tMain.navigateToSearchTarget();
  }
  else
  {
    // Not currently on Circuits page.  Navigate to page and then to panel.
    var sGotoUrl = '/?goto=' + sPath;
    tMain.location.assign( sGotoUrl );
  }
}
