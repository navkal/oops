<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<!DOCTYPE html>
<html>

  <!-- Head -->
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/head.php";
  ?>

  <!-- Body -->
  <body>
    <div class="container">
      <div class="page-header">
        <img src="brand.ico" class="img-responsive" alt="Panel Spy" style="width:50%; max-width:250px; margin:auto">
      </div>
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <form onsubmit="alert('form'); return false;" >
            <div style="text-align:center;" >
              <select id="facilityChooser" class="form-control" >
              </select>
              <button type="submit" onclick="alert('submit');" class="btn btn-primary" >Continue</button>
              <button type="button" onclick="alert('quit');" class="btn btn-default" >Quit</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script>
      document.title = "Choose Facility - Panel Spy";
    </script>

  </body>
</html>

<script>
  $( document ).ready( initFacilityPrompt );

  function initFacilityPrompt()
  {
    // Post request to server
    var tPostData = new FormData();

    $.ajax(
      "session/getFacilities.php",
      {
        type: 'POST',
        processData: false,
        contentType: false,
        dataType : 'json',
        data: tPostData
      }
    )
    .done( handleFacilitiesRsp )
    .fail( handleAjaxError );
  }

  function handleFacilitiesRsp( tRsp, sStatus, tJqXhr )
  {
    // Extract list of facilities accessible to signed-in user
    var tFacilityMap = tRsp.facility_map;

    // Format HTML options and save in a map
    var tHtmlMap = {};
    for ( var sName in tFacilityMap )
    {
      var sDescr = tFacilityMap[sName];
      tHtmlMap[sDescr] = '<option value="' + sName + '">' + sDescr + '</option>';
    }

    // Sort and concatenate options
    var aKeys = Object.keys( tHtmlMap ).sort();
    var sHtml = '';
    for ( var iKey in aKeys )
    {
      var sKey = aKeys[iKey];
      sHtml += tHtmlMap[sKey];
    }

    // Load options into chooser
    $( '#facilityChooser' ).html( sHtml );
  }

</script>
