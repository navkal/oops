<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<button id="addUserButton" class="btn btn-default btn-sm pull-right" data-toggle="modal" data-target="#editUserDialog" data-backdrop="static" data-keyboard=false style="display:none" >
  <span class="glyphicon glyphicon-plus"></span> Add User
</button>

<?php
  $sUsernameReadonly = 'readonly';
  $sSubmitAction = 'add';
  $sSubmitLabel = 'Add';
  require_once $_SERVER["DOCUMENT_ROOT"]."/users/editUserDialog.php";
?>
