<template>
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
			<div :class="itemIcon" />
			<div class="icon-user users-preicon" />{{ user.realname }}
			<div v-if="userOpen"
				class="user-drawer">
				<div class="user-drawer1">
					{{ t('forms', 'Real name') }}:<br>
					{{ t('forms', 'Address') }}:<br>
					{{ t('forms', 'E-mail address') }}:<br>
					{{ t('forms', 'Phone number') }}:
				</div>
				<div class="user-drawer2">
					{{ user.realname }}<br>
					{{ user.address }}<br>
					{{ user.email }}<br>
					{{ user.phone }}
				</div>
				<div class="user-drawerbutton">
					<a v-show="!banDisabled"
						class="button icon-userban"
						@click.stop="banUserClick" />
					<div v-show="banDisabled"
						class="icon-loading user-padloading" />
				</div>
			</div>
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
		}
	},

	computed: {
		itemIcon() {
			let common = ' user-icon users-clickable user-icon-user'
			common += ' '
			return common
		},

		isFolder() {
			return this.node.type === 'folder'
		},
	},

	methods: {
		banUserClick() {
			this.$emit('user-ban-clicked', this.user)
		},
	},
}
</script>

<style>

:root {
	--drawer-height: 150px;
}

</style>

<style scoped>

td, th {
	padding: 15px;
	border-bottom: 1px solid var(--color-border);
	height: 60px;
}

tr.open {
	height: calc( 60px + var(--drawer-height) );
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
	top: 15px;
}

div.user-drawer {
	position: absolute;
	width: 100%;
	top: 60px;
	left: 0px;
	height: var(--drawer-height);
	z-index: 300;
	padding: 15px;
	background-color: var(--color-background-dark);
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
	top: 15px;
}

div.user-padloading {
	padding: 15px;
}

</style>
