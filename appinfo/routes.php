<?php
/**
 * @copyright Copyright (c] 2017 Vinzenz Rosenkranz <vinzenz.rosenkranz@gmail.com>
 *
 * @author affan98 <affan98@gmail.com>
 * @author John Molakvoæ (skjnldsv) <skjnldsv@protonmail.com>
 * @author Jonas Rittershofer <jotoeri@users.noreply.github.com>
 * @author Roeland Jago Douma <roeland@famdouma.nl>
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

return [
	'routes' => [
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],

		['name' => 'sureveyUser#register', 'url' => '/sureveyuser/register/form/{id}', 'verb' => 'GET', 'defaults' => ['id' => '']],
		['name' => 'sureveyUser#commitRegister', 'url' => '/sureveyuser/register/form/{id}', 'verb' => 'POST', 'defaults' => ['id' => '']],
		['name' => 'sureveyUser#loginForm', 'url' => '/sureveyuser/login/form/{id}', 'verb' => 'GET', 'defaults' => ['id' => '']],
		['name' => 'sureveyUser#login', 'url' => '/sureveyuser/login/{id}', 'verb' => 'POST', 'defaults' => ['id' => '']],
		['name' => 'sureveyUser#logout', 'url' => '/sureveyuser/logout', 'verb' => 'GET'],
		['name' => 'sureveyUser#verifyEmail', 'url' => '/sureveyuser/verifyemail/{code}/{formid}', 'verb' => 'GET', 'defaults' => ['formid' => '']],
		['name' => 'sureveyUser#requireResetPassword', 'url' => '/sureveyuser/resetpassword/require', 'verb' => 'GET'],
		['name' => 'sureveyUser#requireResetPasswordPost', 'url' => '/sureveyuser/resetpassword/require', 'verb' => 'POST'],
		['name' => 'sureveyUser#resetPassword', 'url' => '/sureveyuser/resetpassword/{code}', 'verb' => 'GET'],
		['name' => 'sureveyUser#resetPasswordPost', 'url' => '/sureveyuser/resetpassword/{code}', 'verb' => 'POST'],
		// Survey user management
		['name' => 'page#userAdminIndex', 'url' => '/surveyuseradmin/listusers/{page}/{filter}', 'verb' => 'GET', 'defaults' => ['filter' => '', 'page' => 1]],

		// Settings
		['name' => 'settings#postSettings', 'url' => '/settings', 'verb' => 'POST'],
		['name' => 'settings#getLogoImage', 'url' => '/settings/logoimage', 'verb' => 'GET'],
		['name' => 'settings#getBackgroundImage', 'url' => '/settings/backgroundimage', 'verb' => 'GET'],

		// Before /{hash} to avoid conflict
		['name' => 'page#index', 'url' => '/new', 'verb' => 'GET', 'postfix' => 'create'],
		['name' => 'page#index', 'url' => '/{hash}/edit', 'verb' => 'GET', 'postfix' => 'edit'],
		['name' => 'page#index', 'url' => '/{hash}/clone', 'verb' => 'GET', 'postfix' => 'clone'],
		['name' => 'page#index', 'url' => '/{hash}/results', 'verb' => 'GET', 'postfix' => 'results'],

		['name' => 'page#goto_form', 'url' => '/{hash}', 'verb' => 'GET'],
	],
	'ocs' => [
		// Forms
		['name' => 'api#getForms', 'url' => '/api/v1/forms', 'verb' => 'GET'],
		['name' => 'api#newForm', 'url' => '/api/v1/form', 'verb' => 'POST'],
		['name' => 'api#getForm', 'url' => '/api/v1/form/{id}', 'verb' => 'GET'],
		['name' => 'api#updateForm', 'url' => '/api/v1/form/update', 'verb' => 'POST'],
		['name' => 'api#deleteForm', 'url' => '/api/v1/form/{id}', 'verb' => 'DELETE'],

		// Questions
		['name' => 'api#newQuestion', 'url' => '/api/v1/question', 'verb' => 'POST'],
		['name' => 'api#updateQuestion', 'url' => '/api/v1/question/update', 'verb' => 'POST'],
		['name' => 'api#reorderQuestions', 'url' => '/api/v1/question/reorder', 'verb' => 'POST'],
		['name' => 'api#deleteQuestion', 'url' => '/api/v1/question/{id}', 'verb' => 'DELETE'],

		// Answers
		['name' => 'api#newOption', 'url' => '/api/v1/option', 'verb' => 'POST'],
		['name' => 'api#updateOption', 'url' => '/api/v1/option/update', 'verb' => 'POST'],
		['name' => 'api#deleteOption', 'url' => '/api/v1/option/{id}', 'verb' => 'DELETE'],

		// Submissions
		['name' => 'api#getSubmissions', 'url' => '/api/v1/submissions/{hash}', 'verb' => 'GET'],
		['name' => 'api#deleteAllSubmissions', 'url' => '/api/v1/submissions/{formId}', 'verb' => 'DELETE'],
		['name' => 'api#insertSubmission', 'url' => '/api/v1/submission/insert', 'verb' => 'POST'],
		['name' => 'api#deleteSubmission', 'url' => '/api/v1/submission/{id}', 'verb' => 'DELETE'],

		// Access
		['name' => 'settings#getAccess', 'url' => '/api/v1/access', 'verb' => 'GET'],

		// Survey user management
		['name' => 'sureveyUser#apiList', 'url' => '/api/v1/surveyusers/list/{page}/{filter}', 'verb' => 'GET', 'defaults' => ['page' => 0, 'filter' => '']],
		['name' => 'sureveyUser#apiListExport', 'url' => '/api/v1/surveyusers/exportlist', 'verb' => 'GET'],
		['name' => 'sureveyUser#apiSetStatus', 'url' => '/api/v1/surveyusers/status/{user}/{status}', 'verb' => 'PUT', 'defaults' => ['user' => 0, 'status' => 0]],
	]
];
