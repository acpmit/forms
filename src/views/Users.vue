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
		<div class="topbar-container">
			<div class="topbar-relative">
				<label for="filter">{{ t('forms', 'Filter results') }}:</label>
				<input id="filter"
					v-model="dataFilter"
					name="filter">
				<input id="filterBanned"
					v-model="dataFilterBanned"
					type="checkbox"
					class="checkbox user-padleft"
					name="filterBanned">
				<label for="filterBanned"
					class="user-padleft">{{ t('forms', 'Hide banned users') }}</label>
				<div v-show="filterLoading"
					class="loading-pos">
					<div class="icon-loading-small" />
				</div>
			</div>
		</div>
		<div class="users-container">
			<table class="users-table">
				<thead>
					<UserRow :iam-a-header="true" />
				</thead>
				<tbody>
					<UserRow
						v-for="surveyUser in filteredSurveyUsers"
						:key="surveyUser.id"
						:iam-a-header="false"
						:ban-disabled="banDisabled"
						:user="surveyUser"
						@user-unban-clicked="unbanUser"
						@user-ban-clicked="banUser" />
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
import SetWindowTitle from '../utils/SetWindowTitle'
import UserRow from '../components/Users/UserRow'
import debounce from 'debounce'

export default {
	name: 'Users',

	components: {
		UserRow,
		AppContent,
		EmptyContent,
	},

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
			dataFilterBanned: true,
			dataFilter: '',
			loadedFilter: null,
			banDisabled: false,
			lastSentFilter: '',
			loadingUsers: true,
			filterLoading: false,
			surveyUsers: [],
			dataPage: 0,
		}
	},

	computed: {
		filteredSurveyUsers() {
			if (!this.dataFilterBanned) return this.surveyUsers.results
			return this.surveyUsers.results.filter(user => { return user.status === 0 })
		},
	},

	watch: {
		dataFilter(val) {
			this.debounceLoadSurveyUsers(val)
		},
	},

	beforeMount() {
		this.dataFilter = this.$route.params.filter
		this.loadUsers(this.dataFilter)
		SetWindowTitle(this.formTitle)
	},

	methods: {
		banUser(sender) {
			this.sendBanUser(sender)
		},

		unbanUser(sender) {
			this.sendBanUser(sender, 0)
		},

		debounceLoadSurveyUsers: debounce(function(filter = null) {
			this.loadUsers(filter)
		}, 500),

		sendBanUser(user, status = 1) {
			this.banDisabled = true
			axios.put(generateOcsUrl('apps/forms/api/v1', 2) + 'surveyusers/status/' + user.id + '/' + status)
				.then(response => {
					user.status = status
				})
				.catch(error => {
					console.error(error)
					showError(t('forms', 'There was an error while modifying the user account'))
				}).finally(() => {
					this.banDisabled = false
				})
		},

		loadUsers(filter = null) {
			if (filter !== null && filter !== '') filter = '/' + filter
			else filter = ''

			if (this.loadedFilter === filter) return

			this.filterLoading = true
			axios.get(generateOcsUrl('apps/forms/api/v1', 2) + 'surveyusers/list/' + this.dataPage + filter)
				.then(response => {
					console.info('response')
					this.surveyUsers = response.data
					this.loadedFilter = filter
				})
				.catch(error => {
					console.error(error)
					showError(t('forms', 'There was an error while loading the list of users'))
				}).finally(() => {
					this.filterLoading = false
					this.loadingUsers = false
				})
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
	margin-top: 10px;
}

div.loading-pos {
	position: absolute;
	right: 12px;
	top: 65px;
}

div.topbar-relative {
	position: relative;
	margin-left: 40px;
	padding: 2px;
}

div.topbar-container {
	position: relative;
	width: 100%;
	background: var(--color-background-darker);
}

.user-padleft {
	padding-left: 30px;
}

</style>
