<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  define( 'MAX_USERNAME_LENGTH', 20 );
  define( 'MIN_USERNAME_LENGTH', 6 );
?>
<script>
  function validateUsername()
  {
    var tUsername = $( '#username' );
    var aMessages = [];

    if ( ! tUsername.prop( 'readonly' ) )
    {
      var sUsername = tUsername.val();

      if ( sUsername.length > <?=MAX_USERNAME_LENGTH?> )
      {
        aMessages.push( 'Username may contain at most <?=MAX_USERNAME_LENGTH?> characters.' );
      }

      if ( sUsername.length < <?=MIN_USERNAME_LENGTH?> )
      {
        aMessages.push( 'Username must contain at least <?=MIN_USERNAME_LENGTH?> characters.' );
      }

      if ( sUsername.indexOf( ' ' ) != -1 )
      {
        aMessages.push( 'Username may not contain spaces.' );
      }

      if ( ( sUsername.length > 0 ) && ! sUsername.match( /^[a-zA-Z0-9\-_]+$/ ) )
      {
        aMessages.push( 'Username can contain only alphanumeric, hyphen, and underscore characters.' );
      }

      if ( aMessages.length )
      {
        tUsername.parent().addClass( 'has-error' );
      }
    }

    return aMessages;
  }
</script>
