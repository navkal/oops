<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<script src="topology/topology.js?version=<?=time()?>"></script>

<div class="container">
  <div id="headerContent" class="page-header" >
    <div class="row" >
      <div class="col-xs-12 col-sm-4 col-md-3 col-lg-2 headerButton" >
        <a class="btn btn-default btn-sm" href="topology/circuitTopology.pdf" target="_blank" title="View diagram of Circuit Topology" >
          <span class="glyphicon glyphicon-blackboard"></span> View Circuit Topology
        </a>
      </div>
      <div class="col-xs-12 col-sm-8 col-md-9 col-lg-10 headerButton" >
        <a class="btn btn-default btn-sm" href="topology/downloadTree.php" onclick="return startTreeDump(event);" title="Generate and download dump of Circuit Tree" ><span class="glyphicon glyphicon-download-alt"></span> Download Tree Dump <sup><span class="glyphicon glyphicon-asterisk"></span></sup></a>
        <span class="well-sm text-info" style="white-space:nowrap;" >
          <small>
            <sup>
              <span class="glyphicon glyphicon-asterisk"></span>
            </sup>
            Takes several minutes
          </small>
        </span>
      </div>
    </div>
  </div>
  <div id="dumpStatus" class="well well-sm hidden" >
    <span class="glyphicon glyphicon-hourglass" style="font-size:18px;" ></span> Generating tree. <span id="dumpTime"></span>
  </div>

</div>
