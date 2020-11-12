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

if (isset($_['code']))
		$routeReset = \OC::$server->getURLGenerator()
			->linkToRoute('forms.sureveyUser.resetPasswordPost', ['code' => $_['code']]);
	else
		$routeReset = \OC::$server->getURLGenerator()
			->linkToRoute('forms.sureveyUser.requireResetPasswordPost');

?>

<div id="emptycontent" class="">

	<?php if($_['logoImage']): ?>
		<div class="survey-logo">
			<img src="<?php p($_['logoImage']); ?>">
		</div>
	<?php else: ?>
		<div class="icon-forms"></div>
	<?php endif ?>

	<div class="survey-user-center">
		<?php if(isset($_['message']) && isset($_['success']) && $_['success']): ?>
			<div>
				<?php p($_['message']); ?>
			</div>
		<?php else: ?>
			<div class="survey_user_reset">
				<h2><?php
					if (isset($_['altTitle']))
						p($_['altTitle']);
					else
						p($l->t('Please enter your e-mail address, so we can send you a password reset link.'));
					?></h2>

				<?php if($_['message']): ?>
					<div class="survey-user-message">
						<?php p($_['message']); ?>
					</div>
				<?php endif ?>

				<div class="survey_user_form">
					<form id="su_form"
						  action="<?php p($routeReset); ?>"
						  method="POST"
						  name="su_form">
						<?php if(!isset($_['showresetfields'])): ?>
							<div>
								<label for="su_email"><?php p($l->t('E-mail address:')); ?></label>
								<input name="su_email"
									   placeholder="<?php p($l->t('my@address.com')); ?>" maxlength="200"
									   required
									   <?php if (isset($_['email'])) print_unescaped(' value="'.$_['email'].'"'); ?>
									   minlength="1" type="text" class="question__input">
							</div>
						<?php else: ?>
							<input type="hidden" name="submitted" value="yes" >
							<div>
								<label for="su_password"><?php p($l->t('Password:')); ?></label>
								<input name="su_password"
									   required
									   value="<?php p($_['su_password']); ?>"
									   placeholder="<?php p($l->t('Enter your password here')); ?>" maxlength="200"
									   minlength="1" type="password" class="question__input">
							</div>
							<div>
								<label for="su_password2"><?php p($l->t('Password (repeat):')); ?></label>
								<input name="su_password2"
									   required
									   value="<?php p($_['su_password2']); ?>"
									   placeholder="<?php p($l->t('Enter your password here')); ?>" maxlength="200"
									   minlength="1" type="password" class="question__input">
							</div>
						<?php endif; ?>
						<div>
							<label></label>
							<input type="submit" class="primary" value="<?php p($l->t('Reset password')); ?>">
						</div>
					</form>
				</div>
			</div>
		<?php endif ?>
	</div>

</div>
