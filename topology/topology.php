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
    width: 100%;
    height: 100%;
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
      var sColor = 'white';

      if ( sLink.toLowerCase().indexOf( 'http://' ) == 0 )
      {
        // Link is full URL
        $( tA ).attr( 'href', sLink );
        $( tA ).attr( 'target', '_blank' );
        sColor = 'green';
      }
      else
      {
        // Link is internal
        $( tA ).attr( 'href', 'javascript:void(null)' );

        if ( sLink == 'PANELSPY' )
        {
          // Link is to main window
          $( tA ).click( openMainWindow );
        }
        else
        {
          // Link is path
          $( tA ).attr( 'path', sLink );
          $( tA ).click( openImageWindow );
          sColor = 'black';
        }
      }

      // Remove original link
      $( tA ).removeAttr( 'xlink:href' );

      // Style clickable rectangle
      var tRect = $( tA ).find( 'rect' );
      tRect.css( 'fill', sColor );
      tRect.css( 'stroke', sColor );
      tRect.css( 'stroke-width', '8' );
      tRect.css( 'fill-opacity', '0.1' );
      tRect.css( 'stroke-opacity', '0.5' );
    }
  }


  function openMainWindow( tEvent )
  {
    console.log( 'Trying to get main window' );
    var tMain = window.opener;

    try
    {
      // Determine whether original main window is available
      var sTestTitle = tMain.document.title;

      // No exception: Original main window available
      console.log( 'Original opener available, title=' + sTestTitle );
    }
    catch( e )
    {
      // Exception: Original main window not available
      console.log( 'Original opener NOT available' );

      // Determine whether reopened main window is available
      if ( g_tMainWindow )
      {
        if ( g_tMainWindow.closed )
        {
          console.log( 'Reopened main window CLOSED' );
          g_tMainWindow = null;
        }
        else
        {
          try
          {
            sTestTitle = g_tMainWindow.document.title;
            console.log( 'Reopened main window available, title=' + sTestTitle );
          }
          catch( e )
          {
            // Reopened main window not available
            g_tMainWindow = null;
            console.log( 'Reopened main window NOT available' );
          }
        }
      }

      tMain = g_tMainWindow;
    }

    // Open main window
    if ( tMain )
    {
      // Main window is available: use it

      if ( tMain.document.title.indexOf( 'Circuits' ) != 0 )
      {
        // Main window is not currently on Circuits page
        console.log( 'Returning to Circuits page' );
        tMain.location.assign( '/' );
      }
    }
    else
    {
      // Main window is not available: Open a new one
      console.log( 'Reopening main window using goto' );

      try
      {
        g_tMainWindow = window.open( '/', 'Main' );
      }
      catch( e )
      {
        alert( 'Error: Could not reopen main window.' );
      }
    }
  }

  function closeChildWindows()
  {
    childWindowsClose( g_aImageWindows );
  }
</script>
