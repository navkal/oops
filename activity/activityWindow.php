<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<!DOCTYPE html>
<html lang="en">

  <!-- Head -->
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"]."/../common/head.php";
    require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";
    if ( ! isset( $_REQUEST['type'], $_REQUEST['id'] ) )
    {
      abort();
    }
  ?>

  <!-- Body -->
	<body>
    <br/>
    <?php
      require_once $_SERVER["DOCUMENT_ROOT"]."/sortableTable/sortableTable.php";
      require_once $_SERVER['DOCUMENT_ROOT'] . '/activity/editNote.php';
    ?>

    <script>
      g_sSortableTableTitle = 'Activity Log';
      g_sSortableTableSubtitle = <?=quote( $_REQUEST["type"] . ': ' . $_REQUEST["subtitle"] )?>;
      g_sSortableTableType = 'Activity';
      g_sSortableTableEditWhat = 'Note';

      $( document ).ready( getSortableTable );
      g_sSortableTableParams =
      {
        target_object_type: '<?=$_REQUEST["type"]?>',
        target_object_id: '<?=$_REQUEST["id"]?>'
      };
    </script>

 	</body>
</html>


<?php
  $iVersion = time();
?>
<script src="/util/util.js?version=<?=$iVersion?>"></script>
<script src="/session/keepAlive.js?version=<?=$iVersion?>"></script>
