<?php

$settingsUrl = \OC::$server->getURLGenerator()
	->linkToRoute('forms.settings.postSettings');

?>
<form id="forms_settings"
	  action="<?php p($settingsUrl); ?>"
	  method="POST"
	  name="forms_settings">

	<div id="forms-general" class="section">
		<p><span id="forms_settings_msg" class="msg"></span></p>
		<h2><?php p($l->t('General settings')); ?></h2>
		<div><p class="settings-hint">
				<?php p($l->t('These are the controls for the general functions:')); ?>
			</p>
			<p><input name="enable-access"
					  id="enable-access"
					<?php if ($_['enableAccess']) p('checked'); ?>
					  type="checkbox"
					  value="yes"
					  class="checkbox">
				<label for="enable-access"><?php p($l->t('Enable advanced access controls (allow access based on the settings below to create forms and view results)')); ?></label></p>
			<p><input name="allow-all"
					  id="allow-all"
					<?php if ($_['enableAll']) p('checked'); ?>
					  type="checkbox"
					  value="yes"
					  class="checkbox">
				<label for="allow-all"><?php p($l->t('Allow all instance users to access all forms (no ownership is checked)')); ?></label></p>
		</div>
	</div>

	<div id="forms-policies" class="section">
		<h2><?php p($l->t('Policies')); ?></h2>
		<div><p class="settings-hint">
				<?php p($l->t('These are the policies the registered users have to accept to fill the surveys')); ?>
			</p>
			<p><label for="policy-pp"><?php p($l->t('Privacy policy URL')); ?></label>
				<input name="policy-pp"
					  id="policy-pp"
					  value="<?php print_unescaped($_['policy-pp']); ?>"
					  type="text">
				</p>
			<p><label for="policy-tos"><?php p($l->t('Terms of service URL')); ?></label>
				<input name="policy-tos"
					  id="policy-tos"
					  value="<?php print_unescaped($_['policy-tos']); ?>"
					  type="text">
				</p>
		</div>
	</div>

	<div id="forms-create-groups" class="section">
		<h2><?php p($l->t('Create forms')); ?></h2>
		<div class="section-content">
			<div class="forms-access forms-access-curtain"></div>
			<p class="settings-hint">
				<?php p($l->t('Please select the user groups allowed to create new forms:')); ?>
			</p>
			<?php foreach($_['createGroups'] as $value): ?>
				<p><input name="create-<?php p($value['id']); ?>"
						  id="create-<?php p($value['id']); ?>"
						  <?php if ($value['selected']) p('checked'); ?>
						  type="checkbox"
						  value="yes"
						  class="checkbox">
					<label for="create-<?php p($value['id']); ?>"><?php p($value['name']); ?></label></p>
			<?php endforeach; ?>
		</div>
	</div>

	<div id="forms-view-groups" class="section">
		<h2><?php p($l->t('View results')); ?></h2>
		<div class="section-content">
			<div class="forms-access forms-access-curtain"></div>
			<p class="settings-hint">
				<?php p($l->t('Please select the user groups allowed to view the survey results:')); ?>
			</p>
			<?php foreach($_['viewGroups'] as $value): ?>
				<p><input name="view-<?php p($value['id']); ?>"
						  id="view-<?php p($value['id']); ?>"
						  <?php if ($value['selected']) p('checked'); ?>
						  type="checkbox"
						  value="yes"
						  class="checkbox">
					<label for="view-<?php p($value['id']); ?>"><?php p($value['name']); ?></label></p>
			<?php endforeach; ?>
		</div>
	</div>

	<div id="forms-personal-groups" class="section">
		<h2><?php p($l->t('View personal data')); ?></h2>
		<div class="section-content">
			<div class="forms-access forms-access-curtain"></div>
			<p class="settings-hint">
				<?php p($l->t('Please select the user groups allowed to view the personal data connected to the survey results:')); ?>
			</p>
			<?php foreach($_['personalDataGroups'] as $value): ?>
				<p><input name="personal-<?php p($value['id']); ?>"
						  id="personal-<?php p($value['id']); ?>"
						<?php if ($value['selected']) p('checked'); ?>
						  type="checkbox"
						  value="yes"
						  class="checkbox">
					<label for="personal-<?php p($value['id']); ?>"><?php p($value['name']); ?></label></p>
			<?php endforeach; ?>
		</div>
	</div>

	<div id="forms-logo" class="section">
		<h2><?php p($l->t('Logo for the survey user interface')); ?></h2>
		<div class="section-content">
			<label for="uploadlogo"><span><?php p($l->t('Logo')) ?></span></label>
			<input id="uploadLogoData" name="uploadLogoData" type="hidden" />
			<input id="uploadlogo" class="fileupload" name="uploadlogo" type="file" />
		</div>
	</div>

	<input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']) ?>">
	<div class="section"><input type="button" id="forms_settings_submit" class="primary" value="<?php p($l->t('Save changes')); ?>"></div>
</form>

