<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/util/security.php';
?>


<!-- Confirm Restore dialog -->
<div class="modal fade" id="restoreDialog" role="dialog" aria-labelledby="restoreDialogTitle">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><span class="glyphicon glyphicon-remove"></span></span></button>
        <h4 class="modal-title" id="restoreDialogTitle"></h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <form id="restoreDialogForm" class="form-horizontal" onsubmit="onSubmitRestoreDialog(event); return false;" >
              <div class="form-group">
                <label for="timestamp"></label>
                <div>
                  <input type="text" class="form-control" id="timestamp" disabled >
                </div>
              </div>
              <div class="form-group">
                <label for="remove_comment"></label>
                <div>
                  <textarea id="remove_comment" class="form-control" disabled ></textarea>
                </div>
              </div>

              <div id="restoreFields">

              </div>

              <button id="restoreDialogFormSubmitButton" type="submit" style="display:none" ></button>
            </form>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <div style="text-align:center;" >
          <button type="button" id="restoreDialogFormSubmitProxy" class="btn btn-primary" onclick="$('#restoreDialogFormSubmitButton').click()" ></button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        </div>
        <br/>
        <div id="messages" class="alert alert-danger" style="text-align:left; display:none" role="alert">
          <ul id="messageList">
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
