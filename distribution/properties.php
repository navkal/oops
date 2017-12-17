<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<!DOCTYPE html>
<html lang="en">

  <!-- Head -->
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"]."/../common/head.php";
    require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";
    if ( ! isset( $_REQUEST["path"], $_REQUEST["type"], $_REQUEST["oid"] ) )
    {
      abort();
    }
  ?>

  <!-- Body -->
	<body>
    <div class="container">

      <div class="clearfix" style="padding-top: 5px">
        <span class="pull-right">
          <button id="btnUp" class="btn btn-link btn-xs" onclick="goUp()" title="Parent">
            <span class="glyphicon glyphicon-arrow-up" ></span>
          </button>
          <button id="btnDown" class="btn btn-link btn-xs" onclick="goDown()" title="Child">
            <span class="glyphicon glyphicon-arrow-down" ></span>
          </button>
        </span>
      </div>

      <div class="panel panel-default">
        <div class="panel-heading">
          <span id="propertiesTitle" class="panel-title" ></span>
          <a id="panelImage" class="pull-right" style="display:none;" >
            <button class="btn btn-link btn-xs" onclick="window.opener.openImageWindowEtc(event)" title="Panel Image" >
              <span class="glyphicon glyphicon-picture" style="font-size:18px;"></span>
            </button>
          </a>
        </div>
        <div class="panel-body">
          <div id="objectArea" style="overflow:auto;">
            <table class="table table-hover table-condensed" >
              <tbody id="objectLayout" >
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
 	</body>
</html>

<style>
  #objectArea .table > tbody > tr:first-child > td
  {
    border: none;
  }
  .glyphicon-arrow-up,.glyphicon-arrow-down
  {
    font-size: 16px;
  }
</style>

<script>
  var g_sPath = '<?=$_REQUEST["path"]?>';
  var g_sType = '<?=$_REQUEST["type"]?>';
  var g_sOid = '<?=$_REQUEST["oid"]?>';

  $( document ).ready( init )
  function init()
  {
    document.title = 'Properties: ' + g_sPath;
  }
</script>

<?php
  $iVersion = time();
?>
<script src="../util/util.js?version=<?=$iVersion?>"></script>
<script src="properties.js?version=<?=$iVersion?>"></script>
<script src="../session/keepAlive.js?version=<?=$iVersion?>"></script>
