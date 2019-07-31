<template>
    <header class="app-logo-wrapper">
        <router-link :to="{name: 'home'}" class="app-logo">
            <img src="@/assets/images/logogram.svg" alt="Presentator logo" class="img" width="44">
            <span class="txt">Presentator</span>
        </router-link>
    </header>
</template>

<script>
export default {
    name: 'app-header',
}
</script>
