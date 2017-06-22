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

    if ( sPassword.length > <?=MAX_PASSWORD_LENGTH?> )
    {
      aMessages.push( 'Password may contain at most <?=MAX_PASSWORD_LENGTH?> characters.' );
    }

    if ( sPassword.length < <?=MIN_PASSWORD_LENGTH?> )
    {
      aMessages.push( 'Password must contain at least <?=MIN_PASSWORD_LENGTH?> characters.' );
    }

    if ( sPassword.indexOf( ' ' ) != -1 )
    {
      aMessages.push( 'Password may not contain spaces.' );
    }

    if ( sPassword.indexOf( '"' ) != -1 )
    {
      aMessages.push( 'Password may not contain qutoes (").' );
    }

    if ( aMessages.length )
    {
      tPassword.parent().addClass( 'has-error' );
    }

    return aMessages;
  }
</script>
