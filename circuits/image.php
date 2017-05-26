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
        <button class="btn btn-link btn-xs" onclick="goBack('<?=$sPath?>')" title="Back to Circuits">
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

  function goBack( sPath )
  {
    var tOpener = window.opener;

    if ( tOpener.location.search.indexOf( 'page=panels' ) != -1 )
    {
      // Image window was opened from the Panels view
      tOpener.location.assign( '/?goto=' + sPath );
    }
    else
    {
      // Image window was opened from the Circuits view
      tOpener.g_sSearchTargetPath = sPath;
      tOpener.navigateToSearchTarget();
    }
  }
</script>