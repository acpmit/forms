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
		<th class="users-birth users-text">
			{{ t('forms', 'Birth year') }}
		</th>
	</tr>
	<tr v-else
		:class="{
			'users-item': true,
			'users-clickable': !iamAHeader}"
		@click="itemClicked">
		<td class="users-text users-clickable">
			<div :class="itemIcon" />
			<div class="user-icon" />{{ user.realname }}
		</td>
		<td class="users-addr users-clickable">
			{{ user.address }}
		</td>
		<td class="users-email users-clickable">
			{{ user.email }}
		</td>
		<td class="users-birth users-clickable">
			{{ user.bornyear }}
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
		itemClicked(event) {
			if (this.iamAHeader || this.node === undefined) return

			if (!this.isFolder) {
				this.$store.commit('TOGGLE_FILE', this.node)
			} else {
				this.loading = true
				this.$emit('change-folder', this.node.id)
			}
		},
	},
}
</script>

<style scoped>

td, th {
	padding: 15px;
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

td.users-birth {
	width: 10%;
}

td.users-addr {
	width: 30%;
}

td.users-email {
	width: 30%;
}

div.sf-load-pos {
	position: absolute;
	top: 21px;
	left: 18px;
}

.users-text {
	padding-left: 36px;
	margin-top: 3px;
	overflow: hidden;
	white-space: nowrap;
	text-overflow: ellipsis;
	width: 30%;
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

</style>
