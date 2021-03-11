<template>
	<div>
		<tr v-if="iamAHeader">
			<th class="users-text">
				{{ t('forms', 'Real name') }}
			</th>
			<th class="users-addr">
				{{ t('forms', 'Address') }}
			</th>
			<th class="users-email">
				{{ t('forms', 'E-mail address') }}
			</th>
			<th class="users-phone users-text">
				{{ t('forms', 'Phone number') }}
			</th>
		</tr>
		<tr v-else
			:class="{
				'users-item': true,
				'open': userOpen,
				'users-clickable': !iamAHeader}"
			@click="userOpen = !userOpen">
			<td class="users-text users-clickable">
				<div :class="itemIcon" />{{ user.realname }}
			</td>
			<td class="users-addr users-clickable">
				{{ user.address }}
			</td>
			<td class="users-email users-clickable">
				{{ user.email }}
			</td>
			<td class="users-phone users-clickable">
				{{ user.phone }}
			</td>
		</tr>
		<tr v-if="userOpen"
			class="users-item"
			@click="userOpen = !userOpen">
			<td colspan="4"
				class="users-no-padding">
				<div class="user-drawer">
					<table class="users-sub-table">
						<tr>
							<td>{{ t('forms', 'Real name') }}:</td>
							<td>{{ user.realname }}</td>
						</tr>
						<tr>
							<td>{{ t('forms', 'Address') }}:</td>
							<td>{{ user.address }}</td>
						</tr>
						<tr>
							<td>{{ t('forms', 'E-mail address') }}:</td>
							<td>{{ user.email }}</td>
						</tr>
						<tr>
							<td>{{ t('forms', 'Phone number') }}:</td>
							<td>{{ user.phone }}</td>
						</tr>
					</table>
					<div class="user-drawerbutton">
						<a v-show="user.status === 0"
							class="button icon-disabled-user"
							:title="t('forms', 'Ban user and exclude survey results')"
							@click.stop="banUserClick" />
						<a v-show="user.status === 1"
							class="button icon-add"
							:title="t('forms', 'Unban user and include survey results')"
							@click.stop="unbanUserClick" />
						<a class="button icon-delete"
							:title="t('forms', 'Remove user data from the database and exclude survey results')"
							@click.stop="confirmOpen = !confirmOpen" />
						<div v-show="banDisabled"
							class="icon-loading user-padloading" />
					</div>
					<div v-if="confirmOpen"
						class="user-confirmdelete">
						{{ t('forms', 'This action will anonymize and remove user data from the database and can not be undone. Are you sure?') }}
						<div class="user-confirmdelete-buttons">
							<a class="button"
								:title="t('forms', 'Remove user data from the database and exclude survey results')"
								@click.stop="deleteUserClick">
								{{ t('forms', 'Remove user') }}
							</a>
							<a class="button"
								:title="t('forms', 'Cancel')"
								@click.stop="confirmOpen = false">
								{{ t('forms', 'Cancel') }}
							</a>
						</div>
					</div>
				</div>
			</td>
		</tr>
	</div>
</template>

<script>
export default {
	name: 'UserRow',

	props: {
		iamAHeader: {
			type: Boolean,
			default: false,
		},
		banDisabled: {
			type: Boolean,
			default: false,
		},
		user: {
			type: Object,
			default: null,
		},
	},

	data() {
		return {
			userOpen: false,
			confirmOpen: false,
		}
	},

	computed: {
		itemIcon() {
			return this.user.status === 0
				? 'icon-user users-preicon'
				: 'icon-userban users-preicon'
		},
	},

	methods: {
		unbanUserClick() {
			if (this.banDisabled) return
			this.$emit('user-unban-clicked', this.user)
		},

		banUserClick() {
			if (this.banDisabled) return
			this.$emit('user-ban-clicked', this.user)
		},

		deleteUserClick() {
			if (this.banDisabled) return
			this.$emit('user-delete-clicked', this.user)
		},
	},
}
</script>

<style>

:root {
	--drawer-height: 160px;
}

</style>

<style scoped>

td, th {
	padding: 15px;
	border-bottom: 1px solid var(--color-border);
	height: 60px;
}

tr.open {
	/*height: calc( 60px + var(--drawer-height) );*/
}

tr.users-selected {
	opacity: 0.2;
}

tr.users-clickable {
	user-select: none;
	cursor: pointer;
	position: relative;
}

tr.users-item {
	padding: 15px;
	border-bottom: 1px solid var(--color-border);
	position: relative;
	width: 100%;
}

tr.users-clickable:hover {
	background: var(--color-background-hover);
}

.users-text, td.users-phone, td.users-addr, td.users-email {
	overflow: hidden;
	margin-top: 3px;
	text-overflow: ellipsis;
	white-space: nowrap;
	vertical-align: top;
	padding-top: 18px;
}

td.users-phone {
	min-width: 10%;
}

td.users-addr {
	min-width: 20%;
}

td.users-email {
	min-width: 20%;
}

div.sf-load-pos {
	position: absolute;
	top: 21px;
	left: 18px;
}

.users-text {
	padding-left: 36px;
	min-width: 20%;
}

.sf-node-date {
	margin-top: 3px;
}

.sf-node-size {
	margin-top: 3px;
	padding: 0px 16px;
	text-align: right;
}

div.user-icon img {
	width: 30px;
	height: 30px;
	top: 1px;
	left: 1px;
	position: absolute;
}

div.user-icon {
	mask-repeat: no-repeat;
	mask-size: 32px 32px;
	width: 32px;
	height: 32px;
	position: absolute;
	top: 13px;
	left: 11px;
	mask-position: 0px center;
}

div.users-preicon {
	width: 20px;
	height: 20px;
	position: absolute;
	left: 8px;
	top: 19px;
}

div.user-drawer {
	/*position: absolute;*/
	/*height: var(--drawer-height);*/
	/*width: 100%;*/
	/*top: 60px;*/
	/*left: 0px;*/
	/*z-index: 300;*/
	/*padding: 15px;*/
	/*border-bottom: 5px solid var(--color-border);*/
	background: var(--color-background-dark);
	/*border-top: 1px solid var(--color-border);*/
}

div.user-drawer1 {
	position: absolute;
	left: 35px;
	top: 15px;
	line-height: 30px;
}

div.user-drawer2 {
	position: absolute;
	left: 145px;
	top: 15px;
	line-height: 30px;
}

div.user-drawerbutton {
	position: absolute;
	right: 15px;
	top: 18px;
}

div.user-padloading {
	position: absolute;
	top: 2px;
	right: 9px;
}

table.users-sub-table {
	width: 100%;
}

td.users-no-padding {
	padding: 0px;
}

table.users-sub-table td {
	border-bottom: 1px solid var(--color-main-background);
}

div.user-confirmdelete {
	background: var(--color-warning);
	width: 100%;
	padding: 15px;
	position: relative;
}

div.user-confirmdelete-buttons {
	position: absolute;
	right: 15px;
	top: 16px;
}

</style>
