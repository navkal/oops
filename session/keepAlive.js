// Copyright 2017 Panel Spy.  All rights reserved.

$( document ).ready( live );

function live()
{
  console.log( '============> LIVE!' );
  setTimeout( poll, 3000 );
};

function poll()
{
  var tPostData = new FormData();
  console.log( '===> id=' + g_sSignInId );
  tPostData.append( 'signInId', g_sSignInId );

  $.ajax(
    "../session/signedIn.php",
    {
      type: 'POST',
      processData: false,
      contentType: false,
      dataType : 'json',
      data: tPostData
    }
  )
  .done( liveOrDie )
  .fail( die );
}

function liveOrDie( tRsp, sStatus, tJqXhr )
{
  if ( tRsp )
  {
    live();
  }
  else
  {
    die();
  }
}

function die()
{
  console.log( '============> DIE!' );

  // Try to close the window
  window.close();

  // In case window did not close, go to sign-in page
  location.assign( '/' );
}
