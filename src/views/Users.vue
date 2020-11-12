<!--
 @copyright Copyright (c) 2020 John Molakvoæ (skjnldsv) <skjnldsv@protonmail.com>

 @author ACPM IT LTD <info@acpmit.com>

 @license GNU AGPL version 3 or any later version

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as
 published by the Free Software Foundation, either version 3 of the
 License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program. If not, see <http://www.gnu.org/licenses/>.
-->

<template>
	<AppContent v-if="loadingUsers">
		<EmptyContent icon="icon-loading">
			{{ t('forms', 'Loading users …') }}
		</EmptyContent>
	</AppContent>

	<AppContent v-else>
		<TopBar>
			<div>
				<label for="filter">{{ t('forms', 'Filter results') }}:</label>
				<input id="filter"
					v-model="dataFilter"
					name="filter">
			</div>
		</TopBar>
		<div class="users-container">
			<table class="users-table">
				<thead>
					<UserRow :iam-a-header="true" />
				</thead>
				<tbody>
					<UserRow
						v-for="surveyUser in filteredResults"
						:key="surveyUser.id"
						:iam-a-header="false"
						:user="surveyUser" />
				</tbody>
			</table>
		</div>
	</AppContent>
</template>

<script>

import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import { showError } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import { generateOcsUrl } from '@nextcloud/router'
import EmptyContent from '../components/EmptyContent'
import ViewsMixin from '../mixins/ViewsMixin'
import SetWindowTitle from '../utils/SetWindowTitle'
import UserRow from '../components/Users/UserRow'

export default {
	name: 'Users',

	components: {
		UserRow,
		AppContent,
		EmptyContent,
	},

	mixins: [ViewsMixin],

	props: {
		filter: {
			type: String,
			default: '',
		},
		page: {
			type: Number,
			default: 1,
		},
	},

	data() {
		return {
			dataFilter: '',
			loadingUsers: true,
			surveyUsers: [],
		}
	},

	computed: {
		filteredResults() {
			return this.surveyUsers.filter(item => {
				return item.realname.toLowerCase().indexOf(this.dataFilter.toLowerCase()) > -1
			})
		},
	},

	beforeMount() {
		this.loadSurveyUsers(this.filter)
		SetWindowTitle(this.formTitle)
		this.dataFilter = this.$route.params.filter
	},

	methods: {
		async loadSurveyUsers(filter = null) {
			this.loadingUsers = true
			if (filter !== null && filter !== '') filter = '/' + filter
			else filter = ''

			try {
				const response = await axios.get(generateOcsUrl('apps/forms/api/v1', 2) + 'surveyusers' + filter)

				// Append questions & submissions
				this.surveyUsers = response.data
			} catch (error) {
				console.error(error)
				showError(t('forms', 'There was an error while loading the list of users'))
			} finally {
				this.loadingUsers = false
			}
		},
	},
}
</script>

<style scoped>

table.users-table {
	width: 100%;
	table-layout: fixed;
}

div.users-container {
	width: 100%;
}

</style>
