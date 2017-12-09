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
<link rel="stylesheet" href="/lib/tablesorter/theme.bootstrap_3.css">

<!-- tablesorter basic libraries -->
<script type="text/javascript" src="/lib/tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript" src="/lib/tablesorter/jquery.tablesorter.widgets.js"></script>

<!-- tablesorter pager styling -->
<link rel="stylesheet" href="/lib/tablesorter/jquery.tablesorter.pager.css">

<!-- tablesorter pager library -->
<script type="text/javascript" src="/lib/tablesorter/jquery.tablesorter.pager.js"></script>

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

    <div id="sortableTableIsEmpty" class="panel-heading" style="display:none;text-align:center" >
      <h3 class="panel-title">
        This table is empty.
      </h3>
    </div>

  </div>

  <?php
    $sWhichPager = 'Bottom';
    include $_SERVER["DOCUMENT_ROOT"]."/sortableTable/pager.php";
  ?>
</div>
