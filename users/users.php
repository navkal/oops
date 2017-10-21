<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"] . '/sortableTable/sortableTable.php';
  require_once $_SERVER["DOCUMENT_ROOT"] . '/users/editUser.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/sortableTable/confirmRemove.php';
?>

<script>
  g_sSortableTableTitle = 'Users';
  g_sSortableTableType = 'User';
  g_sSortableTableEditWhat = "User";
  g_sRemoveCodeFolder = 'users';
  $( document ).ready( getSortableTable );
</script>
