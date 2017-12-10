<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";
  $iVersion = time();
?>

<!-- Utility scripts -->
<script src="/sortableTable/sortableTable.js?version=<?=$iVersion?>"></script>
<script src="/sortableTable/editSortableTableRow.js?version=<?=$iVersion?>"></script>
<script src="/sortableTable/removeSortableTableRow.js?version=<?=$iVersion?>"></script>
<link rel="stylesheet" href="/util/spinner.css?version=<?=$iVersion?>">
<link rel="stylesheet" href="/sortableTable/sortableTable.css?version=<?=$iVersion?>">

<!-- tablesorter Bootstrap theme -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.29.0/css/theme.bootstrap_3.min.css" integrity="sha256-dXZ9g5NdsPlD0182JqLz9UFael+Ug5AYo63RfujWPu8=" crossorigin="anonymous" />

<!-- tablesorter basic libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.29.0/js/jquery.tablesorter.min.js" integrity="sha256-UD/M/6ixbHIPJ/hTwhb9IXbHG2nZSiB97b4BSSAVm6o=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.29.0/js/jquery.tablesorter.widgets.min.js" integrity="sha256-/3WKCLORjkqCd7cddzHbnXGR31qqys81XQe2khfPvTY=" crossorigin="anonymous"></script>

<!-- tablesorter pager styling -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.29.0/css/jquery.tablesorter.pager.min.css" integrity="sha256-x+whz5gQKEXx3S3pxwmxPhC1OWpRiHaPXUW5Yt8/fzg=" crossorigin="anonymous" />

<!-- tablesorter pager library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.29.0/js/extras/jquery.tablesorter.pager.min.js" integrity="sha256-jkVgfYuH8sw4gTXCDEJMANM9Kf2xZLFWALNiAQ1AyZQ=" crossorigin="anonymous"></script>

<style>
.tablesorter .tablesorter-filter-row .disabled
{
  display: none;
}
</style>

<div class="container-fluid">

  <div id="spinner" class="spinner" >
  </div>

  <div id="tableTop">

    <!-- Title -->
    <span id="sortableTableTitle" class="h4"></span>

    <!-- Add button -->
    <button id="sortableTableAddButton" class="btn btn-default btn-sm pull-right" onclick="g_sAction='add';g_sUpdateTarget=null;" data-toggle="modal" data-target="#editDialog" style="display:none" >
      <span class="glyphicon glyphicon-plus"></span>
      <span id="sortableTableAddButtonText" ></span>
    </button>
    <br/>

    <!-- Subtitle -->
    <small id="sortableTableSubtitle"></small>
  </div>

  <?php
    $sWhichPager = 'Top';
    include $_SERVER["DOCUMENT_ROOT"]."/sortableTable/pager.php";
  ?>

  <div id="content" style="margin-top:15px;">

    <table id="sortableTable" class="table-condensed" >
    </table>

    <div id="sortableTableIsEmpty" class="well" style="display:none;text-align:center" >
      This table is empty.
    </div>

  </div>

  <?php
    $sWhichPager = 'Bottom';
    include $_SERVER["DOCUMENT_ROOT"]."/sortableTable/pager.php";
  ?>
</div>
