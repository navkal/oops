// Copyright 2017 Panel Spy.  All rights reserved.

$( document ).ready( live );

function live()
{
  setTimeout( poll, 5000 );
};

function poll()
{
  $.ajax(
    "../session/keepAlive.php",
    {
      type: 'GET',
      cache: false,
      dataType : 'json'
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
  window.close();
}
