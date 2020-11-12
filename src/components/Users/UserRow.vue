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
			<div class="user-icon" />{{ user.realname }}
			<div v-if="userOpen"
				class="user-drawer">
				Hello
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

}
</script>

<style scoped>

td, th {
	padding: 15px;
	border-bottom: 1px solid var(--color-border);
	height: 60px;
}

tr.open {
	height: 120px;
	padding-bottom: 75px;
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

div.user-icon-user {
	background: var(--color-primary);
	width: 20px;
	height: 20px;
	mask-image: url('../../../img/user.svg');
}

div.user-drawer {
	position: absolute;
	width: 100%;
	top: 60px;
	left: 0px;
	height: 60px;
	z-index: 300;
	padding: 15px;
	background-color: var(--color-background-darker);
}

</style>
