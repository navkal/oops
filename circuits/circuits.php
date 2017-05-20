<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  $_SESSION["reservedDelimiter"] = '-_-_-';
  $_SESSION['user'] = [];
  $_SESSION['user']['role'] = ( isset( $_REQUEST['role'] ) && ( ( strpos( $_SERVER['TMP'], 'xampp' ) !== false ) || ( $_SERVER['SERVER_ADDR'] == '192.168.1.194' ) ) ) ? $_REQUEST['role'] : '';

  $iVersion = time();
  $sGoto = isset( $_REQUEST['goto'] ) ? $_REQUEST['goto'] : '';
?>


<!-- Circuit Navigator scripts -->
<link rel="stylesheet" href="circuits/circuits.css?version=<?=$iVersion?>">
<script src="circuits/circuits.js?version=<?=$iVersion?>"></script>

<!-- Search scripts -->
<link rel="stylesheet" href="circuits/search.css?version=<?=$iVersion?>">
<script src="circuits/search.js?version=<?=$iVersion?>"></script>

<!-- Goto parameter -->
<input id="goto" type="hidden" value="<?=$sGoto?>"/>

<div class="container">

  <h4>Circuits</h4>

  <!-- Search -->
  <div class="row" >
    <div id="search" class="col-xs-12 col-sm-12 col-md-12 col-lg-12" >
      <div class="form-group">

        <!-- Search input control -->
        <div id="search-control" class="input-group" >
          <div class="input-group-btn">
            <button type="button" id="searchTargetButton" class="btn btn-default" title="Search Targets" data-toggle="modal" data-target="#searchTargetDialog">
              <span class="glyphicon glyphicon-search">
              </span>
            </button>
          </div>
          <input class="form-control search-input" id="search-input" type="text" placeholder="Search..." >
          <div class="input-group-btn">
            <button type="button" id="search-input-clear" class="btn btn-default" title="Clear search input" onclick="clearSearchInput()">
              <span class="glyphicon glyphicon-remove">
              </span>
            </button>
          </div>
        </div>

        <!-- Menu showing search results -->
        <div id="search-menu" class="search-menu" style="position:absolute; top:100%; left:0px; z-index:100; display:none; overflow:auto; resize:both; ">
          <div id="search-results">
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Tree -->
  <div class="row" >
    <div class="just-padding">
      <div id="circuitTree" class="list-group list-group-root well" style="overflow:auto; min-height:36px" >
      </div>
    </div>
  </div>

</div>


<!-- Search Target dialog -->
<div class="modal fade" id="searchTargetDialog" tabindex="-1" role="dialog" aria-labelledby="searchTargetLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><span class="glyphicon glyphicon-remove"></span></span></button>
        <h4 class="modal-title" id="searchTargetLabel">Search Targets</h4>
      </div>
      <div class="modal-body bg-info">
        <div class="row" >
          <div class="col-xs-2 col-sm-2 col-md-3 col-lg-3" >
          </div>
          <div class="col-xs-10 col-sm-10 col-md-9 col-lg-9" >
            <ul id="searchTargetList" class="list-unstyled" >
              <li>
                <span class="btn-group btn-group-xs" >
                  <button type="button" class="btn btn-default btn-xs" title="Select All" onclick="checkAllSearchTargets(true)" >
                    <span class="glyphicon glyphicon-check" ></span>
                    All
                  </button>
                  <button type="button" class="btn btn-default btn-xs" title="Select None" onclick="checkAllSearchTargets(false)" >
                    None
                  </button>
                </span>
              </li>
              <li>
                <label class="checkbox checkbox-inline" >
                  <input type="checkbox" value="Circuit" checked >
                  <span>Distribution</span>
                </label>
              </li>
              <li>
                <label class="checkbox checkbox-inline" >
                  <input type="checkbox" value="Panel" checked >
                  <span>Panels</span>
                </label>
              </li>
              <li>
                <label class="checkbox checkbox-inline" >
                  <input type="checkbox" value="Transformer" checked >
                  <span>Transformers</span>
                </label>
              </li>
              <li>
                <label class="checkbox checkbox-inline" >
                  <input type="checkbox" value="Device" checked >
                  <span>Devices</span>
                </label>
              </li>
              <li>
                <label class="checkbox checkbox-inline" >
                  <input type="checkbox" value="Path" checked >
                  <span>Paths</span>
                </label>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal" >Close</button>
      </div>
    </div>
  </div>
</div>
