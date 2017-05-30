<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<!DOCTYPE html>
<html lang="en">

  <!-- Head -->
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"]."/../common/headStart.php";
  ?>
  <title>
    Topology
  </title>
  <script src="../util/util.js?version=<?=time()?>"></script>
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"]."/../common/headEnd.php";
  ?>

  <!-- Body -->
	<body>
    <?php
      require_once $_SERVER["DOCUMENT_ROOT"]."/topology/topology.svg";
    ?>
  </body>
</html>

<style>
  svg
  {
    display: block;
    position: absolute;
    width:100%;
    height:100%;
  }
</style>

<script>
  var g_tMainWindow = null;

  $( document ).ready( init );

  function init()
  {
    // Set handlers
    $( window ).on( 'unload', closeChildWindows );

    // Generate hyperlinks to open image window
    $( 'a' ).each( makeHyperlink );
  }

  function makeHyperlink( i, tA )
  {
    // Configure hyperlink
    $( tA ).attr( 'path', $( tA ).attr( 'xlink:href' ) );
    console.log( 'path=' + $( tA ).attr( 'path' ) );
    $( tA ).attr( 'href', 'javascript:void(null)' );
    $( tA ).removeAttr( 'xlink:href' );
    $( tA ).click( openImageWindow );

    // Make it clickable
    $( tA ).find( 'rect' ).css( 'fill', 'blue' );
    $( tA ).find( 'rect' ).css( 'stroke', 'pink' );
    $( tA ).find( 'rect' ).css( 'stroke-width', '5' );
    $( tA ).find( 'rect' ).css( 'fill-opacity', '0.001' );
    $( tA ).find( 'rect' ).css( 'stroke-opacity', '0.9' );
  }

  function closeChildWindows()
  {
    childWindowsClose( g_aImageWindows );
  }
</script>
