<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/util/security.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/sortableTable/confirmRemoveDialog.php';
?>

<script>

  function removeObject()
  {
    // Post request to server
    var tPostData = new FormData();
    tPostData.append( "username", g_sUsername );

    $.ajax(
      "users/removeUser.php",
      {
        type: 'POST',
        processData: false,
        contentType: false,
        dataType : 'json',
        data: tPostData
      }
    )
    .done( removeDone )
    .fail( handleAjaxError );
  }
</script>
