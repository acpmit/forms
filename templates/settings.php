<?php

$settingsUrl = \OC::$server->getURLGenerator()
	->linkToRoute('forms.settings.postSettings');

?>
<form id="su_form"
	  action="<?php p($settingsUrl); ?>"
	  method="POST"
	  name="su_settings">

	<div id="forms-create-groups" class="section">
		<h2>Create forms</h2>
		<div><p class="settings-hint">
			Please select the user groups allowed to create new forms:
			</p>
			<?php foreach($_['createGroups'] as $value): ?>
				<p><input id="create-<?php p($value['id']); ?>" <?php if ($value['selected']) p('checked'); ?> type="checkbox" class="checkbox">
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
				<p><input id="create-<?php p($value['id']); ?>" <?php if ($value['selected']) p('checked'); ?> type="checkbox" class="checkbox">
					<label for="create-<?php p($value['id']); ?>"><?php p($value['name']); ?></label></p>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="section"><input type="submit" class="primary" value="<?php p($l->t('Save changes')); ?>"></div>

</form>
