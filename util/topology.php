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
    <div class="embed-responsive" style="padding-bottom:150%">
        <object id="pdf" data="../database/circuitTopology.pdf" type="application/pdf" width="100%" ></object>
    </div>
  </body>
</html>

<script>
  $( window ).on( 'unload', closeChildWindows );
  $( window ).on( 'resize', resizePdf );

  function closeChildWindows()
  {
    childWindowsClose( g_aImageWindows );
  }

  function resizePdf()
  {
    $( '#pdf' ).attr( 'data', '../database/circuitTopology.pdf' );
  }

  var g_aMainWindows = [];

  function openMainWindow( tEvent, sUrl )
  {
    return childWindowOpen( tEvent, g_aMainWindows, sUrl, 'Main', '', 0, 0, true );
  }
</script>
