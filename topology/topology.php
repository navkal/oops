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
    <div id="outer" style="visibility:hidden">
      <div id="wrap">
        <?php
          require_once $_SERVER["DOCUMENT_ROOT"]."/topology/topology.svg";
        ?>
      </div>
    </div>
  </body>
</html>

<style>
  #wrap
  {
    position: relative;
  }
  #outer
  {
    position: relative;
    -webkit-transform-origin: top left;
  }
</style>

<script>
  var g_tMainWindow = null;

  $( document ).ready( init );

  function init()
  {
    // Set handlers
    $( window ).on( 'unload', closeChildWindows );
    $( window ).resize( resizeDiagram );


    // Generate hyperlinks to open image window
    $( 'a' ).each(
      function( i, tA )
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
    );

    // Set initial size of circuit diagram
    resizeDiagram();
    $( '#outer' ).css( 'visibility', 'visible' );
  }

  function closeChildWindows()
  {
    childWindowsClose( g_aImageWindows );
  }

  function resizeDiagram()
  {
    var tWin = $( window );
    var iWidth = tWin.width();
    var iHeight = tWin.height();
    var iMaxWidth = $( 'IMG' ).width() + 100;
    var iMaxHeight = $( 'IMG' ).height() + 50;

    if ( ( iWidth >= iMaxWidth ) && ( iHeight >= iMaxHeight ) )
    {
      $( '#outer' ).css( { '-webkit-transform': '' } );
      $( '#wrap' ).css( { width: '', height: '' } );
    }
    else
    {
      var scale = Math.min( iWidth / iMaxWidth, iHeight / iMaxHeight );
      $( '#outer' ).css( { '-webkit-transform': 'scale(' + scale + ')' } );
      $( '#wrap' ).css( { width: iMaxWidth * scale, height: iMaxHeight * scale } );
    }
  }
</script>
