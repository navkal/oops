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
          require_once $_SERVER["DOCUMENT_ROOT"]."/topology/topology.htm";
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

    // Remove head tags from body
    console.log( 'BF body meta length=' + $( 'body meta' ).length );
    console.log( 'BF head meta length=' + $( 'head meta' ).length );
    $( 'body meta' ).insertAfter( $( 'head meta' ).last() );
    console.log( 'AF body meta length=' + $( 'body meta' ).length );
    console.log( 'AF head meta length=' + $( 'head meta' ).length );
    console.log( 'BF body title length=' + $( 'body title' ).length );
    $( 'body title' ).remove();
    console.log( 'AF body title length=' + $( 'body title' ).length );

    // Generate hyperlinks to open image window
    $( 'AREA' ).each(
      function( i, tArea )
      {
        $( tArea ).attr( 'path', $( tArea ).attr( 'HREF' ) );
        $( tArea ).attr( 'href', 'javascript:void(null)' );
        $( tArea ).click( openImageWindow );
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
