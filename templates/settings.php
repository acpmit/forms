<?php

$settingsUrl = \OC::$server->getURLGenerator()
	->linkToRoute('forms.settings.postSettings');

?>
<form id="forms_settings"
	  action="<?php p($settingsUrl); ?>"
	  method="POST"
	  name="forms_settings">

	<div id="forms-create-groups" class="section">
		<p><span id="forms_settings_msg" class="msg"></span></p>

		<h2>Create forms</h2>
		<div><p class="settings-hint">
			Please select the user groups allowed to create new forms:
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
		<h2>View results</h2>
		<div><p class="settings-hint">
				Please select the user groups allowed to view new results:
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

	<input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']) ?>">
	<div class="section"><input type="button" id="forms_settings_submit" class="primary" value="<?php p($l->t('Save changes')); ?>"></div>

</form>
