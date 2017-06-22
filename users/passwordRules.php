<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  define( 'MIN_PASSWORD_LENGTH', 6 );
  define( 'MAX_PASSWORD_LENGTH', 32 );
?>
<script>
  function validatePassword()
  {
    var tPassword = $( '#password' );
    var sPassword = tPassword.val();
    var tConfirm = $( '#confirm' );
    var sConfirm = tConfirm.val();

    var aMessages = [];

    if ( sPassword != sConfirm )
    {
      aMessages.push( 'Passwords do not match.' );
      tConfirm.parent().addClass( 'has-error' );
    }

    if ( ( sPassword.length > <?=MAX_PASSWORD_LENGTH?> ) || ( sPassword.length < <?=MIN_PASSWORD_LENGTH?> ) || ( sPassword.indexOf( '"' ) != -1 ) )
    {
      aMessages.push( 'Password must contain between <?=MIN_PASSWORD_LENGTH?> and <?=MAX_PASSWORD_LENGTH?> of any printable characters other than quote (").' );
    }

    if ( aMessages.length )
    {
      tPassword.parent().addClass( 'has-error' );
    }

    return aMessages;
  }
</script>
