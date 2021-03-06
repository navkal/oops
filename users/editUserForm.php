<!-- Copyright 2018 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";
?>

<style>
  .checkboxList
  {
    -webkit-column-width: 200px; /* Chrome, Safari, Opera */
    -moz-column-width: 200px; /* Firefox */
    column-width: 200px;
  }
</style>

<div class="row">
  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
    <form id="editUserForm" class="form-horizontal" onsubmit="onSubmitUser(event); return false;" >
      <div class="form-group">
        <label for="username"></label>
        <div>
          <input type="text" class="form-control" id="username" maxlength="<?=MAX_USERNAME_LENGTH+1?>" >
        </div>
      </div>
      <div class="form-group">
        <label for="oldPassword">Old Password</label>
        <div>
          <input type="password" class="form-control" id="oldPassword" maxlength="<?=MAX_PASSWORD_LENGTH+1?>" placeholder="Old Password" >
        </div>
      </div>
      <div class="form-group">
        <label for="password"></label>
        <div>
          <input type="password" class="form-control" id="password" maxlength="<?=MAX_PASSWORD_LENGTH+1?>" >
        </div>
      </div>
      <div class="form-group">
        <label for="confirm"></label>
        <div>
          <input type="password" class="form-control" id="confirm" maxlength="<?=MAX_PASSWORD_LENGTH+1?>" >
        </div>
      </div>
      <div class="form-group">
        <label for="role" ></label>
        <div>
          <select id="role" class="form-control">
            <option value="Visitor">Visitor</option>
            <option value="Intern">Intern</option>
            <option value="Technician">Technician</option>
            <option value="Supervisor">Supervisor</option>
            <option value="Administrator">Administrator</option>
          </select>
        </div>
      </div>
      <div class="settingsHide adminHide" >
        <div class="form-group">
          <label for="auth_facilities" ></label>
          <div style="padding: 6px 22px;" >
            <ul name="auth_facilities" id="auth_facilities" class="fakeFormControl list-unstyled checkboxList" >
            </ul>
          </div>
        </div>
      </div>
      <div class="settingsHide" >
        <div class="form-group">
          <label for="status" ></label>
          <div>
            <select id="status" class="form-control">
              <option value="Enabled">Enabled</option>
              <option value="Disabled">Disabled</option>
            </select>
          </div>
        </div>
      </div>
      <div class="settingsDisabled" >
        <div class="form-group">
          <label for="first_name" ></label>
          <div>
            <input type="text" class="form-control" id="first_name" maxlength="50" >
          </div>
        </div>
        <div class="form-group">
          <label for="last_name" ></label>
          <div>
            <input type="text" class="form-control" id="last_name" maxlength="50" >
          </div>
        </div>
        <div class="form-group">
          <label for="email_address" ></label>
          <div>
            <input type="text" class="form-control" id="email_address" maxlength="256" >
          </div>
        </div>
        <div class="form-group">
          <label for="organization" ></label>
          <div>
            <input type="text" class="form-control" id="organization" maxlength="256" >
          </div>
        </div>
      </div>
      <div class="settingsHide" >
        <div class="form-group">
          <label for="user_description" ></label>
          <div>
            <textarea class="form-control" id="user_description" maxlength="500" ></textarea>
          </div>
        </div>
      </div>
      <button id="editUserFormSubmitButton" type="submit" style="display:none" ></button>
    </form>
  </div>
</div>
