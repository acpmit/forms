<?php
/**
 * @copyright Copyright (c) 2020 John Molakvoæ (skjnldsv) <skjnldsv@protonmail.com>
 *
 * @author ACPM IT LTD <info@acpmit.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

$routeRegister = \OC::$server->getURLGenerator()
	->linkToRoute('forms.sureveyUser.register', ['id' => $_['formid']]);
$routeLogin = \OC::$server->getURLGenerator()
	->linkToRoute('forms.sureveyUser.login', ['id' => $_['formid']]);

//\OC::$server->getSession()->set('fake_ss', 3);
echo('FAKE value ['. (\OC::$server->getSession()->get('fake_ss')).'] hasval: '.
	(\OC::$server->getSession()->exists('fake_ss'))
	);
var_dump(\OC::$server->getSession()->get(\OCA\Forms\Service\SurveyUserService::SURVEY_USER_SESSION_ID));
var_dump(\OC::$server->getSession()->getId());

//$_SESSION['fake_s2'] = 'ok';

echo('FAKE3: '.$_SESSION['fake_s2']);
echo(' FAKE4: '.$_SESSION[\OCA\Forms\Service\SurveyUserService::SURVEY_USER_SESSION_ID]);

//var_dump($_SESSION);

?>

<div id="emptycontent" class="">

	<?php if($_['success']): ?>
		<div>
			<?php p($_['message']); ?>
		</div>
	<?php else: ?>
		<div class="icon-forms"></div>

		<h2><?php p($l->t('This form is for registered users only. If you already have an account, please log in.')); ?></h2>

		<?php if($_['message']): ?>
			<div>
				<?php p($_['message']); ?>
			</div>
		<?php endif ?>

		<form id="su_form"
			  action="<?php p($routeLogin); ?>"
			  method="POST"
			  name="su_form">
			<div>
				<label for="su_email"><?php p($l->t('E-mail address:')); ?></label>
				<input name="su_email"
					   placeholder="<?php p($l->t('my@address.com')); ?>" maxlength="200"
					   minlength="1" type="text" class="question__input">
			</div>
			<div>
				<label for="su_password"><?php p($l->t('Password:')); ?></label>
				<input name="su_password"
					   placeholder="<?php p($l->t('Enter your password here')); ?>" maxlength="200"
					   minlength="1" type="password" class="question__input">
			</div>
			<div>
				<input type="submit" class="primary" value="<?php p($l->t('Login')); ?>">
			</div>
		</form>

		<p><?php print_unescaped($l->t('If you don\'t have an account yet, you can register by <a href="%s">clicking here</a>.', $routeRegister)); ?></p>
	<?php endif ?>

</div>
