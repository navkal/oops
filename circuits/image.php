<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<!DOCTYPE html>
<html lang="en">

  <?php
    $sPath = $_REQUEST['path'];
    $sImg = 'images/' . $sPath . '.jpg';
  ?>

  <!-- Head -->
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"]."/../common/headStart.php";
  ?>
  <title>
    Image: <?=$sPath?>
  </title>
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"]."/../common/headEnd.php";
  ?>

  <!-- Body -->
	<body>
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
</script>