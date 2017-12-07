<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";
?>

<style>
  .pager .glyphicon:hover:not(.disabled)
  {
    cursor: pointer;
  }

  .tablesorter-pager
  {
    padding: 0px;
  }
</style>

<div class="pager" style="display:none;" >

  <form class="form-inline" >

    <!-- Page chooser -->
    <span style="float:left;">
      <small>Page</small>
      <select class="gotoPage form-control input-sm">
      </select>
    </span>

    <!-- Navigation and status -->
    <span style="white-space:nowrap;">
      <span class="glyphicon glyphicon-step-backward first" title="First Page" ></span>
      <span class="glyphicon glyphicon-backward prev" title="Previous Page" ></span>
      <small>
        <span class="pagedisplay" id="<?=$sPageDisplayId?>"></span>
      </small>
      <span class="glyphicon glyphicon-forward next" title="Next Page" ></span>
      <span class="glyphicon glyphicon-step-forward last" title="Last Page" ></span>
    </span>

    <!-- Rows-per-page chooser -->
    <span style="float:right;">
      <small>Rows</small>
      <select class="pagesize form-control input-sm">
        <option value="10">10</option>
        <option value="20">20</option>
        <option value="30">30</option>
        <option value="40">40</option>
        <option value="all">All</option>
      </select>
    </span>

  </form>

</div>
