<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";
?>

<style>
  .pager .glyphicon:hover:not(.disabled)
  {
    cursor: pointer;
  }
</style>

<div class="pager" style="display:none" >

  <form class="form-inline" >

    <span style="float:left;">
      Page
      <select class="gotoPage form-control input-sm">
      </select>
    </span>

    <span style="white-space:nowrap;">
      <span class="glyphicon glyphicon-step-backward first" title="First Page" ></span>
      <span class="glyphicon glyphicon-backward prev" title="Previous Page" ></span>
      <span class="pagedisplay" id="<?=$sPageDisplayId?>"></span>
      <span class="glyphicon glyphicon-forward next" title="Next Page" ></span>
      <span class="glyphicon glyphicon-step-forward last" title="Last Page" ></span>
    </span>

    <span style="float:right;">
      Rows
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
