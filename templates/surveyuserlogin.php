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
$routeReset = \OC::$server->getURLGenerator()
	->linkToRoute('forms.sureveyUser.requireResetPassword');

?>

<div id="emptycontent" class="">

	<?php if(isset($_['bgImage']) && $_['bgImage']): ?>
		<style>
			body {
				background-image: url(<?php print_unescaped($_['bgImage']); ?>);
			}
		</style>
	<?php endif ?>

	<?php if(isset($_['logoImage']) && $_['logoImage']): ?>
		<div class="survey-logo">
			<img src="<?php p($_['logoImage']); ?>">
		</div>
	<?php else: ?>
		<div class="icon-forms"></div>
	<?php endif ?>

	<div class="survey-user-center">
		<?php if(isset($_['success']) && isset($_['message']) && $_['success']): ?>
			<div>
				<?php p($_['message']); ?>
			</div>
		<?php else: ?>
			<div class="survey_user_login">
				<h2><?php if(isset($_['activationMessage']) && $_['activationMessage'])
								p($_['activationMessage']);
							else
								p($l->t('This form is for registered users only. If you already have an account, please log in.')); ?></h2>
				<?php if(isset($_['message']) && $_['message']): ?>
					<div class="survey-user-message">
						<?php p($_['message']); ?>
					</div>
				<?php endif ?>

				<div class="survey_user_form">
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
							<label></label>
							<input type="submit" class="primary" value="<?php p($l->t('Login')); ?>">
						</div>
					</form>
				</div>
			</div>

			<p><?php print_unescaped($l->t('Did you forgot your password? <a href="%s">Click here to start a password reset</a>!', $routeReset)); ?></p>
			<p><?php print_unescaped($l->t('If you don\'t have an account yet, you can register by <a href="%s">clicking here</a>.', $routeRegister)); ?></p>
		<?php endif ?>
	</div>

</div>
