<!--
  - @copyright Copyright (c) 2020 John Molakvoæ <skjnldsv@protonmail.com>
  -
  - @author John Molakvoæ <skjnldsv@protonmail.com>
  -
  - @license GNU AGPL version 3 or any later version
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program.  If not, see <http://www.gnu.org/licenses/>.
  -
  -
  - UPDATE: Adds Quiz option and takes the input:
  - is yet to store input of quizzes and cannot represtent them
  - requires quizFormItem.vue (should be added to svn)
  -->
<template>
	<div class="top-bar" role="toolbar">
		<slot />
	</div>
</template>
<script>
export default {
	name: 'TopBar',
}
</script>

<style lang="scss" scoped>
$top-bar-height: 60px;

.top-bar {
	position: sticky;
	z-index: 100;
	top: var(--header-height);
	display: flex;
	align-items: center;
	align-self: flex-end;
	justify-content: flex-end;
	height: var(--top-bar-height);
	// margin-top: calc(var(--top-bar-height) * -1);
	padding: 0 6px;

	button {
		cursor: pointer;

		// Fix button having too little spacing left and right of text
		&:not(:first-child) {
			width: 44px;
			height: 44px;
			border: none;
			background-color: transparent;
			&:hover, a:active, a:focus {
				background-color: var(--color-background-darker);
			}
		}

		> span {
			cursor: pointer;
			opacity: 1;
			background-size: 16px;
		}
	}
}

</style>
