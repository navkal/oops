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
    <a href="javascript:void(null)" path="MSWB" onclick="openImageWindow(event)">MSWB</a>
    <a href="javascript:void(null)" path="MSWB.6-DE" onclick="openImageWindow(event)">MSWB.6-DE</a>
    <a href="javascript:void(null)" path="MSWB.9-AMDP" onclick="openImageWindow(event)">MSWB.9-AMDP</a>
    <?php
      require_once $_SERVER["DOCUMENT_ROOT"]."/topology/circuitTopology.htm";
    ?>
  </body>
</html>

<script>
  $( document ).ready( init );

  function init()
  {
    $( window ).on( 'unload', closeChildWindows );

    console.log( 'BF body meta length=' + $( 'body meta' ).length );
    console.log( 'BF head meta length=' + $( 'head meta' ).length );
    $( 'body meta' ).insertAfter( $( 'head meta' ).last() );
    console.log( 'AF body meta length=' + $( 'body meta' ).length );
    console.log( 'AF head meta length=' + $( 'head meta' ).length );
    console.log( 'BF body title length=' + $( 'body title' ).length );
    $( 'body title' ).remove();
    console.log( 'AF body title length=' + $( 'body title' ).length );
    

    $( 'AREA' ).each(
      function( i, el )
      {
        $(el).attr( "path", $(el).attr("HREF").split('=')[1] );
        $(el).attr( "href", "javascript:void(null)" );
        $(el).click( openImageWindow );
      }
    );
  }

  function closeChildWindows()
  {
    childWindowsClose( g_aImageWindows );
  }

  function openMainWindow( tEvent, sUrl )
  {
    return childWindowOpen( tEvent, [], sUrl, 'Main', '', 0, 0, true );
  }
</script>
