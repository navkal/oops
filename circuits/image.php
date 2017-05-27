<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<!DOCTYPE html>
<html lang="en">

  <?php
    $sPath = $_REQUEST['path'];
    $sImg = '../database/images/' . $sPath . '.jpg';
  ?>

  <!-- Head -->
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"]."/../common/headStart.php";
  ?>
  <title>
    Image: <?=$sPath?>
  </title>
  <style>
    .glyphicon-arrow-left
    {
      font-size: 16px;
    }
  </style>
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"]."/../common/headEnd.php";
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

<script>

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

  var g_tMainWindow = null;

  function goBack( tEvent, sPath )
  {
    var tOpener = window.opener.opener || window.opener;
    try
    {
      var sTitle = tOpener.document.title;
    }
    catch( e )
    {
      tOpener = window.opener;
      var sTitle = tOpener.document.title;
    }

    var sGotoUrl = '/?goto=' + sPath;

    if ( sTitle.indexOf( 'Topology' ) == 0 )
    {
      console.log( 'Original Circuits page was closed' );
      console.log( 'mainwindow=' + g_tMainWindow );

      if ( g_tMainWindow ) console.log( 'closed=' + g_tMainWindow.closed );

      // Image opened from Topology window
      if ( g_tMainWindow && ! g_tMainWindow.closed )
      {
        console.log( 'Reopened main window still there' );
        var sMainTitle = g_tMainWindow.document.title;
        if ( sMainTitle.indexOf( 'Circuits' ) == 0 )
        {
          console.log( 'Navigating on reopened Circuits page' );
          g_tMainWindow.g_sSearchTargetPath = sPath;
          g_tMainWindow.navigateToSearchTarget();
        }
        else
        {
          console.log( 'Returning to reopened Circuits page' );
          g_tMainWindow.location.assign( sGotoUrl );
        }
      }
      else
      {
        console.log( 'Reopening Circuits page using goto' );
        g_tMainWindow = window.open( sGotoUrl, 'Main' );
      }
    }
    else if ( sTitle.indexOf( 'Circuits' ) == 0 )
    {
      console.log( 'Navigating on original Circuits page' );
      // Image opened from main page, and Circuits view is open
      tOpener.g_sSearchTargetPath = sPath;
      tOpener.navigateToSearchTarget();
    }
    else
    {
      // Image opened from main page, but Circuits is not open
      console.log( 'Returning to original Circuits page' );
      tOpener.location.assign( sGotoUrl );
    }
  }
</script>