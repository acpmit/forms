<template>
	<AppNavigationSettings :title="t('forms', 'Settings')" />
</template>

<script>

import AppNavigationSettings from '@nextcloud/vue/dist/Components/AppNavigationSettings'

export default {
	name: 'AppNavigationSettings',

	components: {
		AppNavigationSettings,
	},
}

</script>
