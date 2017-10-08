<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<!DOCTYPE html>
<html lang="en">

  <!-- Head -->
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"]."/../common/head.php";
    require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";
    if ( ! isset( $_REQUEST["type"], $_REQUEST["id"] ) )
    {
      abort();
    }
  ?>

  <!-- Body -->
	<body>
  <br/><?=$_REQUEST["type"]?><br/><?=$_REQUEST["id"]?><br/><br/><br/>

    <?php
      require_once $_SERVER["DOCUMENT_ROOT"]."/sortableTable/sortableTable.php";
    ?>

    <script>
      g_sSortableTableTitle = 'Activity Log';
      g_sSortableTableType = 'activity';
      $( document ).ready( getSortableTable );
    </script>

 	</body>
</html>



<?php
  $iVersion = time();
?>
<script src="/util/util.js?version=<?=$iVersion?>"></script>
<script src="/session/keepAlive.js?version=<?=$iVersion?>"></script>
