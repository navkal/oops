<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<button id="addUserButton" class="btn btn-default btn-sm pull-right" onclick="initAdd()" data-toggle="modal" data-target="#editUserDialog" data-backdrop="static" data-keyboard=false style="display:none" >
  <span class="glyphicon glyphicon-plus"></span> Add User
</button>

<?php
  $sUsername = '';
  $sUsernameReadonly = '';
  $sSubmitAction = 'add';
  $sSubmitLabel = 'Add User';
  $sAutofocusId = 'username';
  require_once $_SERVER["DOCUMENT_ROOT"]."/users/editUserDialog.php";
?>

<script>
  function initAdd()
  {
    alert( 'add' );
  }

  function initUpdate( sUsername )
  {
    alert( 'update ' + sUsername );
  }
</script>
