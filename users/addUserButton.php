<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<button id="addUserButton" class="btn btn-default btn-sm pull-right" onclick="initAdd()" data-toggle="modal" data-target="#editUserDialog" data-backdrop="static" data-keyboard=false style="display:none" >
  <span class="glyphicon glyphicon-plus"></span> Add User
</button>

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/users/editUserDialog.php";
?>

<script>

  function initAdd()
  {
    g_sAction = 'add';
    g_sSubmitLabel = 'Add User';
    g_sUsername = '';
    g_sUsernameReadonly = false;
    g_sFocusId = 'username';
  }

  function initUpdate( sUsername )
  {
    g_sAction = 'update';
    g_sSubmitLabel = 'Update User';
    g_sUsername = sUsername;
    g_sUsernameReadonly = true;
    g_sFocusId = 'password';
  }
</script>
