// Copyright 2017 Panel Spy.  All rights reserved.

function submitCredentials( tEvent )
{
  // Trim the username
  var sUsername = $( '#username' ).val().trim();
  $( '#username' ).val( sUsername );

  // If we got a username, submit credentials to the backend
  if ( sUsername != '' )
  {
    // Post request to server
    var tPostData = new FormData();
    tPostData.append( "username", $( '#username' ).val() );
    tPostData.append( "password", $( '#password' ).val() );

    $.ajax(
      "session/signIn.php",
      {
        type: 'POST',
        processData: false,
        contentType: false,
        dataType : 'json',
        data: tPostData
      }
    )
    .done( showMain )
    .fail( handleAjaxError );
  }
}

function signOut()
{
  // Post request to server
  var tPostData = new FormData();

  $.ajax(
    "session/signOut.php",
    {
      type: 'POST',
      processData: false,
      contentType: false,
      dataType : 'json',
      data: tPostData
    }
  )
  .done( showMain )
  .fail( handleAjaxError );
}

function showMain()
{
  location.assign( '/' );
}

function clearCredentials( tEvent )
{
  $( '#username' ).val( '' );
  $( '#password' ).val( '' );
}


