<!-- Copyright 2018 Panel Spy.  All rights reserved. -->

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

    <div class="container">
      <div class="row">

        <div class="col-xs-12 col-sm-8 col-sm-push-2">

          <!-- Navigation and status -->
          <span style="white-space:nowrap;">
            <span class="glyphicon glyphicon-step-backward first" title="First Page" ></span>
            <span class="glyphicon glyphicon-backward prev" title="Previous Page" ></span>
            <small>
              <span class="pagedisplay" id="pageDisplay<?=$sWhichPager?>"></span>
            </small>
            <span class="glyphicon glyphicon-forward next" title="Next Page" ></span>
            <span class="glyphicon glyphicon-step-forward last" title="Last Page" ></span>
          </span>

        </div>

        <div class="col-xs-6 col-sm-2 col-sm-pull-8">

          <!-- Page chooser -->
          <span style="float:left;">
            <small>Page</small>
            <select class="gotoPage form-control input-sm">
            </select>
          </span>

        </div>

        <div class="col-xs-6 col-sm-2">

          <!-- Rows-per-page chooser -->
          <span style="float:right;">
            <small>Rows</small>
            <select class="pagesize form-control input-sm">
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
              <option value="all">All</option>
            </select>
          </span>

        </div>

      </div>
    </div>

  </form>

</div>
