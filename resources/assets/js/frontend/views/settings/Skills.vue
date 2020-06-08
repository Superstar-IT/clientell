<template>
  <div>
    <div class="d-flex justify-content-between">
      <span slot="header" class="h4 text-left mt-3">Skills & Trades</span>
      <b-btn v-if="!isEditing" variant="link" @click="isEditing=true">
        <i class="fa fa-pencil"></i> Edit Skills
      </b-btn>
    </div>
    <p class="h5" v-if="!isEditing">
      <b-badge v-for="item in value" :key="item" class="m-2">{{item}}</b-badge>
    </p>
    <div v-else>
      <multiselect
        v-model="value"
        tag-placeholder="Add this as new tag"
        placeholder="Search or add a tag"
        :options="skills"
        :multiple="true"
        :taggable="true"
        @tag="addTag"
      ></multiselect>
      <div class="text-right mt-5">
        <b-btn variant="primary" size="sm" @click="saveSkills">
          <i class="fa fa-save"></i> Save Skills
        </b-btn>
      </div>
    </div>
  </div>
</template>
<script>
import Multiselect from "vue-multiselect";

export default {
  components: {
    Multiselect
  },
  data() {
    return {
      value: window.Store.user ? window.Store.user.skills.split(",") : [],
      isEditing: false,
      user: window.Store.user,
      skills: [
        "Appliance installation",
        "Architect/engineer",
        "Drywall/Plaster",
        "Framing",
        "Masonry",
        "Painting",
        "Siding",
        "Roofing",
        "Electrician",
        "Plumber",
        "HVAC",
        "Trim/finish",
        "Cabinetry",
        "Counter tops",
        "Insulation",
        "Glazing",
        "Landscaping",
        "Lawn maintenance",
        "Irrigation",
        "Stucco",
        "Well installer",
        "Site prep",
        "Asphalt",
        "Door and Window",
        "Paving/hardscapes",
        "Pool",
        "Security systems",
        "Fence",
        "Deck",
        "Dock",
        "Handy man",
        "General contractor",
        "Trash removal",
        "Flooring"
      ]
    };
  },
  methods: {
    addTag(newTag) {
      const tag = {
        name: newTag,
        code: newTag.substring(0, 2) + Math.floor(Math.random() * 10000000)
      };
      this.skills.push(newTag);
      this.value.push(newTag);
    },
    saveSkills() {
      this.user.skills = this.value.join(",");
      console.log(this.user)
      const formData = new window.Form(this.user)
      window.App.post(`/api/v1/auth/user/update-profile`, formData).then(
        response => {
          this.notify("success", "Skills are updated");
          window.$.extend(this.user, response.data);
          this.isEditing = false;
        }
      );
    }
  }
};
</script>
<style lang="scss" scoped>
</style>