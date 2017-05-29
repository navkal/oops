<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<!DOCTYPE html>
<html lang="en">

  <!-- Head -->
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"]."/../common/headStart.php";
  ?>
  <title>Properties:
    <?php
      echo( $_REQUEST["path"] );
    ?>
  </title>
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
    var g_sRole = '<?=$_SESSION["user"]["role"]?>';
  </script>
  <?php
    $iVersion = time();
  ?>
  <script src="../util/util.js?version=<?=$iVersion?>"></script>
  <script src="properties.js?version=<?=$iVersion?>"></script>

  <?php
    require_once $_SERVER["DOCUMENT_ROOT"]."/../common/headEnd.php";
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

      <!-- Collapsible "History" section -->
      <div id="historyArea">
        <div class="panel-group" role="tablist" >
          <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="historyHeading">
              <span class="panel-title">
                <a role="button" data-toggle="collapse" href="#historyCollapse" aria-expanded="true" aria-controls="historyCollapse">
                  <span class="glyphicon glyphicon-list-alt">&nbsp;</span>History
                  <span class="glyphicon glyphicon-plus pull-right"></span>
                </a>
              </span>
            </div>
            <div id="historyCollapse" class="panel-collapse collapse" style="overflow-x:auto" role="tabpanel" aria-labelledby="historyHeading">

              <table id="historyTable" class="table table-responsive">
                <thead>
                  <tr>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Type</th>
                    <th>Description</th>
                  </tr>
                </thead>
                <tbody id="historyTableBody" >
                </tbody>
              </table>
              <div id="historyNone">
                <p class="h5 text-center" >None</p>
              </div>

            </div>
          </div>
        </div>
      </div>

      <div id="notesEditor">
        <div class="form-group">
          <label for="notes">Notes</label>
          <textarea id="notes" class="form-control" maxlength="1000"></textarea>
        </div>
        <div style="text-align:center;" >
          <button class="btn btn-primary btn-xs" onclick="saveNotes(event)" >Save</button>
          <button class="btn btn-default btn-xs" onclick="clearNotes(event)" >Clear</button>
        </div>
      </div>
    </div>
 	</body>
</html>
