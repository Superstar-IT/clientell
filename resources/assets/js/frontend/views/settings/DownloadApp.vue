<template>
  <div>
    <b-card>
      <h4 slot="header" class="text-center">Download the ClienTell App</h4>
      <div class="row text-center mt-5 mb-5">
        <div class="col-md-6 pt-2">
          <p>If you have an Android phone or tablet:</p>
          <a :href="mobileApps.android" target="_blank">
            <img src="/images/download-google-play.png" height="60" />
          </a>
        </div>
        <div class="col-md-6 pt-2">
          <p>If you have an iPhone or IPad:</p>
          <a :href="mobileApps.ios" target="_blank">
            <img src="/images/download-app-store.svg" height="60" />
          </a>
        </div>
      </div>
    </b-card>
  </div>
</template>

<script>
export default {
  computed: {
    mobileApps() {
      return window.Store.config.settings.mobile_apps;
    }
  }
};
</script>

<style scoped>
</style>
