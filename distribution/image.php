<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<!DOCTYPE html>
<html lang="en">

  <?php
    require_once $_SERVER["DOCUMENT_ROOT"]."/../common/util.php";
    require_once $_SERVER["DOCUMENT_ROOT"]."/util/context.php";
    require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";

    if ( ! isset( $_REQUEST["path"] ) )
    {
      abort();
    }

    $sPath = $_REQUEST['path'];

    $command = quote( getenv( "PYTHON" ) ) . " ../database/getImageFilename.py 2>&1 -p " . quote( $sPath ) . $g_sContext;
    error_log( "==> command=" . $command );
    exec( $command, $output, $status );
    error_log( "==> output=" . print_r( $output, true ) );

    $sResult = $output[ count( $output ) - 1 ];
    $tObject = json_decode( $sResult );
    $sImageFilename = $tObject->image_filename;

    $sImg = '../database/' . $_SESSION['panelSpy']['context']['enterprise'] . '/' . $_SESSION['panelSpy']['context']['facility'] . '/images/' . $sImageFilename;
  ?>

  <!-- Head -->
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"]."/../common/head.php";
  ?>

  <!-- Body -->
	<body>

    <div class="container">
      <div class="clearfix" style="padding-top: 5px">
        <button class="btn btn-link btn-xs" onclick="goBack(event,'<?=$sPath?>')" title="Back to Distribution">
          <span class="glyphicon glyphicon-arrow-left" ></span>
        </button>
      </div>
    </div>

    <div class="container-fluid">
      <img class="img-responsive" src="<?=$sImg?>" alt="<?=$sPath?>">
    </div>

  </body>
</html>

<style>
  .glyphicon-arrow-left
  {
    font-size: 16px;
  }
</style>

<?php
  $iVersion = time();
?>
<script src="image.js?version=<?=$iVersion?>"></script>
<script src="../session/keepAlive.js?version=<?=$iVersion?>"></script>

<script>
  $( document ).ready( init )
  function init()
  {
    document.title = 'Image: ' + '<?=$sPath?>';
    resizeWindow();
  }
</script>
