<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<!DOCTYPE html>
<html lang="en">

  <?php
    $sPath = $_REQUEST['path'];
    $sImg = '../database/images/' . $sPath . '.jpg';
  ?>

  <!-- Head -->
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"]."/../common/head.php";
  ?>

  <!-- Body -->
	<body>

    <div class="container">
      <div class="clearfix" style="padding-top: 5px">
        <button class="btn btn-link btn-xs" onclick="goBack(event,'<?=$sPath?>')" title="Back to Circuits">
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

<script>
  var g_sSignInId = localStorage.getItem( 'signInId' );

  $( document ).ready( init )
  function init()
  {
    document.title = 'Image: ' + '<?=$sPath?>';
    resizeWindow();
  }
</script>
<script src="../session/keepAlive.js?version=<?=$iVersion?>"></script>
