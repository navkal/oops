// Copyright 2017 Panel Spy.  All rights reserved.

function submitCredentials( tEvent )
{
  // Trim the username
  var sUsername = $( '#username' ).val().trim();
  $( '#username' ).val( sUsername );
  var sPassword = $( '#password' ).val().trim();
  $( '#password' ).val( sPassword );

  // If we got a username, submit credentials to the backend
  if ( ( sUsername != '' ) && ( sPassword != '' ) )
  {
    // Post request to server
    var tPostData = new FormData();
    tPostData.append( "username", sUsername );
    tPostData.append( "password", sPassword );

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
    .fail( submitCredentialsFAILED );
  }
}

function submitCredentialsFAILED()
{
  console.log( 'submitCredentials FAILED' );
}

function signOut()
{
  // Close Topology child window
  childWindowsClose( g_aTopologyWindows );

  //
  // Close Topology and Image windows that might be openers relative to the main window.
  // This occurs only if the user has closed the main window and then reopened it via the Image window 'back' button.
  // Note that a Topology window that is 'lost', i.e., no longer reachable via the 'opener' hierarchy, will eventually close itself.
  //
  var tOpener = window.opener;
  while ( tOpener && ( tOpener != window ) && ! tOpener.closed )
  {
    tOpener.close();
    tOpener = tOpener.opener;
  }

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

function showMain( tRsp, sStatus, tJqXhr )
{
  console.log( '===> showMain(): signin id=' + tRsp );
  localStorage.setItem( 'signInId', tRsp );
  location.assign( '/' );
}
