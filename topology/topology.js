// Copyright 2017 Panel Spy.  All rights reserved.

// -> -> -> Tree dump -> -> ->

var g_iStartTime = null;
var g_iInterval = null;
function startTreeDump( tEvent )
{
  $( '#headerContent' ).addClass( "hidden" );
  $( "#dumpTime" ).html( "00:00:00" );
  $( '#dumpStatus' ).removeClass( "hidden" );
  g_iInterval = setInterval( waitTreeDump, 1000 );
  g_iStartTime = new Date();
  return true;
}

function waitTreeDump()
{
  $( "#dumpTime" ).html( timeSince( g_iStartTime ) );

  $.ajax(
    "topology/downloadWait.php",
    {
      type: 'GET'
    }
  )
  .done( endTreeDump )
  .fail( handleAjaxError );
}

function endTreeDump( tRsp, sStatus, tJqXhr )
{
  if ( tRsp && g_iInterval )
  {
    console.log( "=> Tree dump completed.  Elapsed time: " + timeSince( g_iStartTime ) );
    clearInterval( g_iInterval );
    g_iInterval = null;
    $( '#headerContent' ).removeClass( "hidden" );
    $( '#dumpStatus' ).addClass( "hidden" );
  }
}

// <- <- <- Tree dump <- <- <-

function timeSince( startTime )
{
  var ms = new Date() - startTime;

  var x = 1000 * 60 * 60 * 24;
  var day = Math.floor( ms / x );
  ms = ms - ( day * x );

  x = 1000 * 60 * 60;
  var hr = Math.floor( ms / x );
  ms = ms - ( hr * x );

  x = 1000 * 60;
  var min = Math.floor( ms / x );
  ms = ms - ( min * x );

  x = 1000;
  var sec = Math.floor( ms / x );
  ms = ms - ( sec * x );

  sDay = day ? ( day + "d " ) : "";
  sElapsed = sDay + pad( hr, 2 ) + ":" + pad( min, 2 ) + ":" + pad( sec, 2 ) /* + "." + pad( ms, 3 ) */;

  return sElapsed;
}

function pad( iNum, iLen )
{
  var sNum = String( iNum );
  while( sNum.length < iLen )
  {
    sNum = "0" + sNum;
  }
  return sNum;
}
