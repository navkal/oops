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
    // Get original hyperlink
    var sLink = $( tA ).attr( 'xlink:href' );
    console.log( 'link=' + $( tA ).attr( 'sLink' ) );

    // Reconfigure hyperlink
    if ( sLink )
    {
      if ( sLink.toLowerCase().indexOf( 'http://' ) == 0 )
      {
        // Link is full URL
        $( tA ).attr( 'href', sLink );
        $( tA ).attr( 'target', '_blank' );
      }
      else
      {
        // Link is path
        $( tA ).attr( 'path', sLink );
        $( tA ).attr( 'href', 'javascript:void(null)' );
        $( tA ).click( openImageWindow );
      }

      // Remove original link
      $( tA ).removeAttr( 'xlink:href' );

      // Make linked object clickable
      var tRect = $( tA ).find( 'rect' );
      tRect.css( 'fill', 'blue' );
      tRect.css( 'stroke', 'pink' );
      tRect.css( 'stroke-width', '5' );
      tRect.css( 'fill-opacity', '0.001' );
      tRect.css( 'stroke-opacity', '0.9' );
    }
  }

  function closeChildWindows()
  {
    childWindowsClose( g_aImageWindows );
  }
</script>
