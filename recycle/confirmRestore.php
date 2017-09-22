<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/util/security.php';
?>

<!-- Confirm Restore dialog -->
<div class="modal fade" id="restoreDialog" tabindex="-1" role="dialog" aria-labelledby="restoreObjectLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><span class="glyphicon glyphicon-remove"></span></span></button>
        <h4 class="modal-title" id="restoreObjectLabel"></h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <form id="restoreForm" onsubmit="restoreObject(event); return false;" >
              <div class="form-group">
                <label id="restoreWhatLabel" for="restoreWhat"></label>
                <input type="text" class="form-control" id="restoreWhat" disabled >
              </div>
              <div class="form-group" id="restoreCommentDiv" hidden >
                <label for="removeComment">Comment</label>
                <textarea id="removeComment" class="form-control" maxlength="200" readonly ></textarea>
              </div>
              <button type="submit" id="restoreFormSubmitButton" style="display:none" ></button>
            </form>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <div style="text-align:center;" >
          <button type="button" id="restoreFormSubmitProxy" class="btn btn-danger" onclick="$('#restoreFormSubmitButton').click()" ></button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</div>
