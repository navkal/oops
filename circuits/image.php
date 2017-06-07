<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<!DOCTYPE html>
<html lang="en">

  <?php
    $sPath = $_REQUEST['path'];
    $sImg = '../database/images/' . $sPath . '.jpg';
  ?>

  <!-- Head -->
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"]."/../common/head.php";
  ?>

  <!-- Body -->
	<body>

    <div class="container">
      <div class="clearfix" style="padding-top: 5px">
        <button class="btn btn-link btn-xs" onclick="goBack(event,'<?=$sPath?>')" title="Back to Circuits">
          <span class="glyphicon glyphicon-arrow-left" ></span>
        </button>
      </div>
    </div>

    <div class="container-fluid">
      <img class="img-responsive" src="<?=$sImg?>" alt="<?=$sPath?>">
    </div>

  </body>
</html>

<style>
  .glyphicon-arrow-left
  {
    font-size: 16px;
  }
</style>

<script>
  document.title = 'Image: <?=$sPath?>';

  $( document ).ready( resizeWindow );

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
      console.log( 'Opener is Topology page' );
      try
      {
        console.log( 'Trying to get second-level opener' );
        var tOpenerOpener = tOpener.opener;

        try
        {
          // Determine whether original main window is available
          var sTestTitle = tOpenerOpener.document.title;

          // No exception: Original main window available
          console.log( 'Second-level opener available, title=' + sTestTitle );
          tMain = tOpenerOpener;
        }
        catch( e )
        {
          // Exception: Original main window not available
          console.log( 'Second-level opener NOT available' );

          // Determine whether reopened main window is available
          if ( tOpener.g_tMainWindow )
          {
            if ( tOpener.g_tMainWindow.closed )
            {
              console.log( 'Reopened main window CLOSED' );
              tOpener.g_tMainWindow = null;
            }
            else
            {
              try
              {
                sTestTitle = tOpener.g_tMainWindow.document.title;
                console.log( 'Reopened main window available, title=' + sTestTitle );
              }
              catch( e )
              {
                // Reopened main window not available
                tOpener.g_tMainWindow = null;
                console.log( 'Reopened main window NOT available' );
              }
            }
          }

          tMain = tOpener.g_tMainWindow;
        }
      }
      catch( e )
      {
        // Exception: Could not get second-level opener
        console.log( 'Could not get second-level opener' );
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
        console.log( 'Navigating on Circuits page' );
        tMain.g_sSearchTargetPath = sPath;
        tMain.navigateToSearchTarget();
      }
      else
      {
        // Main window is not currently on Circuits page
        console.log( 'Returning to Circuits page using goto' );
        tMain.location.assign( sGotoUrl );
      }
    }
    else
    {
      // Main window is not available: Open a new one
      console.log( 'Reopening main window using goto' );
      try
      {
        tOpener.g_tMainWindow = window.open( sGotoUrl, 'Main' );
      }
      catch( e )
      {
        alert( 'Error: Could not reopen main window.' );
      }
    }
  }
</script>