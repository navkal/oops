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
        <div class="input-group-addon">
          <span class="glyphicon glyphicon-search">
          </span>
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
