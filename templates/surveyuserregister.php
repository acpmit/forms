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
	->linkToRoute('forms.sureveyUser.commitRegister', ['id' => $_['formid']]);
$routeLogin = \OC::$server->getURLGenerator()
	->linkToRoute('forms.sureveyUser.loginForm', ['id' => $_['formid']]);

?>

<div id="emptycontent" class="">

	<?php if (isset($_['success']) && $_['success']): ?>

		<div>
			<p>
				<?php p($_['message']); ?>
			</p>
			<p>
				<?php print_unescaped($l->t('Please <a href="%s">click here to log in</a>.', $routeLogin)); ?>
			</p>
		</div>

	<?php else: ?>

		<?php if (isset($_['message']) && strlen($_['message']) > 0): ?>
		<div>
			<?php
				p($_['message']);
				if (isset($_['problems']) && count($_['problems']) > 0) {
					print_unescaped(
						'<ul><li>'.
						join('</li><li>', $_['problems']).
						'</li></ul>');
				}
			?>
		</div>
		<?php endif ?>

		<h2><?php p($l->t('Please provide your details below.')); ?></h2>

		<form id="su_form"
			  action="<?php p($routeRegister); ?>"
			  method="POST"
			  name="su_form">
			<div>
				<label for="su_email"><?php p($l->t('E-mail address:')); ?></label>
				<input name="su_email"
					   required
					   value="<?php p($_['su_email']); ?>"
					   placeholder="<?php p($l->t('my@address.com')); ?>" maxlength="200"
					   minlength="1" type="text" class="question__input">
			</div>
			<div>
				<label for="su_realname"><?php p($l->t('Real name:')); ?></label>
				<input name="su_realname"
					   value="<?php p($_['su_realname']); ?>"
					   placeholder="<?php p($l->t('Please enter your name')); ?>" maxlength="255"
					   type="text" class="question__input">
			</div>
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
			<div>
				<label for="su_address"><?php p($l->t('Address:')); ?></label>
				<input name="su_address"
					   value="<?php p($_['su_address']); ?>"
					   placeholder="<?php p($l->t('Please enter your postal address')); ?>"
					   minlength="1" type="text" class="question__input">
			</div>
			<div>
				<label for="su_born"><?php p($l->t('Your birth year:')); ?></label>
				<input name="su_born"
					   value="<?php p($_['su_born']); ?>"
					   placeholder="<?php p($l->t('Please enter the year you were born in')); ?>" maxlength="4"
					   minlength="1" type="number" class="question__input">
			</div>
			<div>
				<input type="submit" class="primary" value="<?php p($l->t('Register')); ?>">
			</div>
		</form>

	<?php endif ?>

</div>
