<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"] . '/sortableTable/sortableTable.php';
  require_once $_SERVER["DOCUMENT_ROOT"] . '/users/editUser.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/sortableTable/confirmRemoveDialog.php';
?>

<script>
  g_sSortableTableTitle = 'Users';
  g_sSortableTableType = 'user';
  g_sSortableTableEditWhat = "User";
  $( document ).ready( getSortableTable );
</script>
