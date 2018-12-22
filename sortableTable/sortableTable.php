<!-- Copyright 2018 Panel Spy.  All rights reserved. -->

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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.1/css/theme.bootstrap_3.min.css" integrity="sha256-vgjicWNWkVklkfuqKnQth9ww987V7wCOzh6A0qkJ2Lw=" crossorigin="anonymous" />

<!-- tablesorter basic libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.1/js/jquery.tablesorter.min.js" integrity="sha256-uC1JMW5e1U5D28+mXFxzTz4SSMCywqhxQIodqLECnfU=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.1/js/jquery.tablesorter.widgets.min.js" integrity="sha256-Xx4HRK+CKijuO3GX6Wx7XOV2IVmv904m0HKsjgzvZiY=" crossorigin="anonymous"></script>

<!-- tablesorter pager styling -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.1/css/jquery.tablesorter.pager.min.css" integrity="sha256-5s+S8FT166PczMBb6epAGodQG9ZWgQUjDslc0ivNRq4=" crossorigin="anonymous" />

<!-- tablesorter pager library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.1/js/extras/jquery.tablesorter.pager.min.js" integrity="sha256-t1OsUny1JnHT2Vct43Q4Rg5WRkQkcUgs8iQIFuAnkMw=" crossorigin="anonymous"></script>

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
      No entries found.
    </div>

  </div>

  <?php
    $sWhichPager = 'Bottom';
    include $_SERVER["DOCUMENT_ROOT"]."/sortableTable/pager.php";
  ?>
</div>
