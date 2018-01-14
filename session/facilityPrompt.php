<!-- Copyright 2018 Panel Spy.  All rights reserved. -->

<!DOCTYPE html>
<html>

  <!-- Head -->
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/head.php";
    require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";
  ?>

  <!-- Body -->
  <body>
    <div class="container" id="facilityContainer" style="display:none" >
      <div class="page-header">
        <img src="/database/<?=$_SESSION['panelSpy']['context']['enterprise']?>/enterprise.ico" class="img-responsive" alt="Panel Spy" style="width:50%; max-width:250px; margin:auto">
      </div>
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <form onsubmit="return false;" >
            <div class="form-group">
              <label for="facilityChooser">Facility</label>
              <select id="facilityChooser" class="form-control" autofocus >
              </select>
            </div>
            <div style="text-align:center;" >
              <button type="submit" onclick="handleFacilityPick();" class="btn btn-primary" >Continue</button>
              <button type="button" onclick="handleFacilityQuit();" class="btn btn-default" >Quit</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </body>
</html>

<script>
  $( document ).ready( initFacilityPrompt );

  function initFacilityPrompt()
  {
    if ( typeof handleAjaxError === 'function' )
    {
      // Post request to server
      var tPostData = new FormData();
      tPostData.append( "postSecurity", "" );

      $.ajax(
        "session/getAuthFacilities.php",
        {
          type: 'POST',
          processData: false,
          contentType: false,
          dataType : 'json',
          data: tPostData
        }
      )
      .done( makeFacilityDropdown )
      .fail( handleAjaxError );
    }
  }

  function makeFacilityDropdown( tRsp, sStatus, tJqXhr )
  {
    if ( tRsp.sorted_fullnames && tRsp.fullname_map )
    {
      var aFullnames = tRsp.sorted_fullnames;
      var tMap = tRsp.fullname_map;

      var sHtml = '';
      for ( var iFullname in aFullnames )
      {
        var sFullname = aFullnames[iFullname];
        var sName = tMap[sFullname];
        sHtml += '<option value="' + sName + '">' + sFullname + '</option>';
      }

      // Load options into chooser and preset previous selection
      $( '#facilityChooser' ).html( sHtml );
      presetSelectedFacility();

      // Show the chooser
      $( '#facilityContainer' ).css( 'display', 'block' );
      document.title = "Choose Facility - Panel Spy";
    }
    else
    {
      showMain( tRsp, sStatus, tJqXhr );
    }
  }

  function handleFacilityPick()
  {
    saveSelectedFacility();

    // Post request to server
    var tPostData = new FormData();
    tPostData.append( 'facility', $( '#facilityChooser' ).val() );
    tPostData.append( 'facilityFullname', $( '#facilityChooser option:selected' ).text() );

    $.ajax(
      "session/setFacility.php",
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

  function handleFacilityQuit()
  {
    signOut();
  }

  function presetSelectedFacility()
  {
    // Get selected facility data structure
    var tSelFacility = JSON.parse( localStorage.getItem( 'panelSpy.selectedFacility' ) );

    // Get enterprise and username
    var tSession = JSON.parse( localStorage.getItem( 'panelSpy.session' ) );
    var sEnterprise = tSession['context']['enterprise'];
    var sUsername = tSession['user']['username'];

    // If previous selection exists, preset it
    if ( tSelFacility && tSelFacility[sEnterprise] && tSelFacility[sEnterprise][sUsername] )
    {
      $( '#facilityChooser' ).val( tSelFacility[sEnterprise][sUsername] );
    }
  }

  function saveSelectedFacility()
  {
    // Get enterprise and username
    var tSession = JSON.parse( localStorage.getItem( 'panelSpy.session' ) );
    var sEnterprise = tSession['context']['enterprise'];
    var sUsername = tSession['user']['username'];

    // Get selected facility data structure
    var tSelFacility = JSON.parse( localStorage.getItem( 'panelSpy.selectedFacility' ) );

    // Load current selection into data structure
    if ( ! tSelFacility )
    {
      tSelFacility = {};
    }

    if ( ! tSelFacility[sEnterprise] )
    {
      tSelFacility[sEnterprise] = {};
    }

    tSelFacility[sEnterprise][sUsername] = $( '#facilityChooser' ).val();

    // Save
    localStorage.setItem( 'panelSpy.selectedFacility', JSON.stringify( tSelFacility ) );
  }

</script>
