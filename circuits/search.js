// Copyright 2017 Panel Spy.  All rights reserved.

var g_iLastRequestTime = 0;
var g_sLastText = '';
var g_nResultHeight = null;

$(document).ready( initSearch );

function initSearch()
{
  // Set focus on the search input control
  $( '#search-input' ).focus();

  // Set size of search input control
  resizeSearchInput();

  // Set handlers
  $( window ).resize( resizeSearchInput );
  $( '#search-input' ).on( 'keydown', cycleCursor );
  $( '#search-input' ).on( 'keyup', getSearchResults );
  $( '#search-input' ).on( 'blur', hideSearchResults );
  $( '#search-input' ).on( 'click', showSearchResults );
}

function resizeSearchInput()
{
  var sWidth = '' + $( '#search-input' ).closest( '.container' ).width() + 'px';
  $( '#search-control, #search-menu' ).css( 'width', sWidth );
}

function getSearchResults()
{
  var sText = $( '#search-input' ).val();

  if ( sText == '' )
  {
    closeSearchResults();
  }
  else
  {
    if ( sText != g_sLastText )
    {
      g_iLastRequestTime = Date.now();

      // Post request to server
      var tPostData = new FormData();
      tPostData.append( "requestTime", g_iLastRequestTime );
      tPostData.append( "searchText", sText );
      tPostData.append( "searchTargets", 'Path,Circuit,Panel,Transformer,Device' );

      $.ajax(
        "circuits/search.php",
        {
          type: 'POST',
          processData: false,
          contentType: false,
          dataType : 'json',
          data: tPostData
        }
      )
      .done( loadSearchResults )
      .fail( handleAjaxError );
    }
  }

  g_sLastText = sText;
};

function cycleCursor( tEvent )
{
  var nResults = $( '#search .search-result' ).length;

  if ( nResults )
  {
    // Determine whether menu is visible
    var bVisible = $( '#search-menu' ).is( ':visible' );

    // Determine current cursor index
    var iCursor = $( '#search .search-result.search-cursor' ).index();

    switch( tEvent.keyCode )
    {
      case 13:
        // Enter: Select highlighted result or show hidden menu
        if ( bVisible )
        {
          var tCursor = $( '#search .search-cursor' );
          if ( tCursor.length )
          {
            selectSearchResult( { target: tCursor[0] } );
          }
        }
        else
        {
          showSearchResults( tEvent );
        }
        break;

      case 38:
        // Up-arrow: Cycle cursor upward
        if ( bVisible )
        {
          tEvent.preventDefault();

          if ( iCursor < 1 )
          {
            iCursor = nResults;
          }

          moveCursor( -- iCursor, nResults );
        }
        break;

      case 40:
        // Down-arrow: Cycle cursor downward
        if ( bVisible )
        {
          tEvent.preventDefault();

          if ( iCursor >= ( nResults - 1 ) )
          {
            iCursor = -1;
          }

          moveCursor( ++ iCursor, nResults );
        }
        break;
    }
  }
}

function moveCursor( iCursor, nResults )
{
  // Clear existing cursor
  $( '#search .search-cursor' ).removeClass( 'search-cursor' );

  // If new cursor index is within range, update display
  if ( ( iCursor >= 0 ) && ( iCursor < nResults ) )
  {
    var tResult = $( $( '#search .search-result' )[iCursor] );
    tResult.addClass( 'search-cursor' );

    var tMenu = $( '#search-menu' );
    scrollToVisible( tMenu, tResult, iCursor, nResults );
  }
}

function scrollToVisible( tMenu, tResult, iCursor, nResults )
{
  var iTop = tMenu.scrollTop();
  var iHeight = tMenu.height();
  var iFirst = ( iTop / g_nResultHeight );
  var iLast = Math.floor( ( iTop + iHeight ) / g_nResultHeight );

  if ( iCursor == 0 )
  {
    // Scroll to top
    tMenu.scrollTop( 0 );
  }
  else if ( iCursor == ( nResults - 1 ) )
  {
    // Scroll to bottom
    tMenu.scrollTop( ( nResults * g_nResultHeight ) - iHeight );
  }
  else if ( iCursor < iFirst )
  {
    // Scroll up
    tMenu.scrollTop( iCursor * g_nResultHeight );
  }
  else if ( iCursor >= iLast )
  {
    // Scroll down
    tMenu.scrollTop( iTop + g_nResultHeight );
  }

  // Handle possibility that selection is still hidden due to previous resize or scroll by mouse
  var iMenuTop = tMenu.scrollTop();
  var iMenuBottom = iMenuTop + tMenu.outerHeight();
  var iResultTop = iMenuTop + tResult.position().top;
  var iResultBottom = iResultTop + tResult.outerHeight();

  if ( iResultBottom > iMenuBottom )
  {
    tMenu.scrollTop( iResultBottom - tMenu.height() );
  }
}

function loadSearchResults( tResults )
{
  // If handling response to latest request, update results display
  if ( tResults.requestTime == g_iLastRequestTime )
  {
    var sSearchText = tResults.searchText;
    var iSearchTextLen = sSearchText.length;
    var sSearchLower = sSearchText.toLowerCase();
    var aResults = tResults.searchResults;

    if ( aResults.length )
    {
      // Generate the HTML
      var sHtml = '';
      for ( var iResult in aResults )
      {
        var aResult = aResults[iResult];
        var sPath = aResult[0];
        var sResult = aResult[1];
        var sResultLower = sResult.toLowerCase();

        var sResultFormat = '';
        while( sResult != '' )
        {
          var iPos = sResultLower.indexOf( sSearchLower );
          if ( iPos >= 0 )
          {
            var sLeading = sResult.substr( 0, iPos );
            var sMatch = sResult.substr( iPos, iSearchTextLen );
            sResultFormat += sLeading;
            sResultFormat += '<span class="search-result-highlight">';
            sResultFormat += sMatch;
            sResultFormat += '</span>';
            sResult = sResult.substr( iPos + iSearchTextLen );
            sResultLower = sResultLower.substr( iPos + iSearchTextLen );
          }
          else
          {
            sResultFormat += sResult;
            sResult = '';
            sResultLower = '';
          }
        }

        sHtml += '<div class="search-result" path="' + sPath + '" title="' + sPath + '" >';
        sHtml += sResultFormat;
        sHtml += '</div>';
      }

      // Replace HTML in results div
      $( '#search-results' ).html( sHtml );

      // Set handlers
      $( '#search .search-result' ).on( 'mousedown', selectSearchResult );

      // Show the results menu
      showSearchResults();
    }
    else
    {
      closeSearchResults();
    }
  }
}

function selectSearchResult( tEvent )
{
  // Get event target
  var tTarget = $( tEvent.target ).closest( '.search-result' );

  // Set cursor on search result
  $( '#search .search-cursor' ).removeClass( 'search-cursor' );
  tTarget.addClass( 'search-cursor' );

  // Hide search results
  hideSearchResults();

  // Navigate to selected search result in tree
  g_sSearchTargetPath = tTarget.attr( 'path' );
  navigateToSearchTarget();
}

function showSearchResults( tEvent )
{
  var aResults = $( '#search .search-result' )
  var nResults = aResults.length;

  if ( nResults > 0 )
  {
    var tMenu = $( '#search-menu' );

    // If displaying new results, resize and clear scroll position
    if ( ! tEvent )
    {
      // Set menu width
      var nWidth = tMenu.closest( '.container' ).width();
      tMenu.width( nWidth );

      // Calculate per-result height
      if ( g_nResultHeight == null )
      {
        var tResult = $( aResults[0] );
        var nLineHeight = parseInt( tResult.css( 'line-height' ) );
        var nPadTop = parseInt( tResult.css( 'padding-top' ) );
        var nPadBottom = parseInt( tResult.css( 'padding-bottom' ) );
        var nMarginTop = parseInt( tResult.css( 'margin-top' ) );
        var nMarginBottom = parseInt( tResult.css( 'margin-bottom' ) );
        g_nResultHeight = nLineHeight + nPadTop + nPadBottom + nMarginTop + nMarginBottom;
      }
      var nResultsHeight = g_nResultHeight * Math.min( nResults, 10 );

      // Calculate additional menu height
      var nMenuPadBottom = parseInt( tMenu.css( 'padding-bottom' ) );
      var nMenuPadTop = parseInt( tMenu.css( 'padding-top' ) );
      var nMenuMarginBottom = parseInt( tMenu.css( 'margin-bottom' ) );
      var nMenuMarginTop = parseInt( tMenu.css( 'margin-top' ) );
      var nMenuRadius = parseInt( tMenu.css( 'border-radius' ) || tMenu.css( '-moz-border-radius' ) || tMenu.css( '-webkit-border-radius' ) || '8px' );
      var nMenuExtraHeight = nMenuPadTop + nMenuPadBottom + nMenuMarginTop + nMenuMarginBottom + nMenuRadius;

      // Set menu height
      var nHeight = nResultsHeight + nMenuExtraHeight;
      tMenu.height( nHeight );

      // Clear scroll top
      tMenu.scrollTop( 0 );
    }

    tMenu.show();
  }
}

function clearSearchInput()
{
  $( '#search-input' ).val( '' );
  getSearchResults();
}

function closeSearchResults()
{
  // Clear scroll top
  $( '#search-menu' ).show(); // Must be showing to set scrollTop
  $( '#search-menu' ).scrollTop( 0 );

  hideSearchResults();
  $( '#search-results' ).html( '' );
  $( '#search-input' ).focus();
}

function hideSearchResults()
{
  $( '#search-menu' ).hide();
}
