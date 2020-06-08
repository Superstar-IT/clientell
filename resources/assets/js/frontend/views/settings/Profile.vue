<template>
  <div class="user-profile">
    <b-card>
      <h4 slot="header" class="text-center">Skills & License</h4>
      <skills></skills>
      <license></license>
      <gallery></gallery>
    </b-card>
  </div>
</template>

<script>
import * as AccountType from "../../../constants/user-account-type";
import { StarRating } from "vue-rate-it";
import Multiselect from "vue-multiselect";
import IntlTelInput from "./../../../components/Globals/IntlTelInput";
import Skills from './Skills'
import License from './License'
import Gallery from './Gallery'
export default {
  components: {
    StarRating,
    IntlTelInput,
    Multiselect,
    Skills,
    License,
    Gallery
  },
  // props: {
  //     user: {
  //         type: Object,
  //         required: true,
  //     }
  // },
  data() {
    return {
      user: window.Store.user,
      AccountType,
      editing: false,
      form: new window.Form({
        company_name: "",
        first_name: "",
        last_name: "",
        description: null,
        phone_number: "",
        phone_number_ext: "",
        alt_phone_number: "",
        alt_phone_number_ext: "",
        street_address: "",
        street_address2: "",
        city: "",
        state: "",
        email: "",
        business_url: null,
        facebook_url: null,
        twitter_url: null,
        country_id: null
      }),
      formAvatar: new window.Form({
        avatar: null
      }),
      avatarImage: null,
      value: [],
      
    };
  },
  computed: {
    isCompany() {
      return this.user.account_type === AccountType.ACCOUNT_TYPE_COMPANY;
    },
    avatar() {
      if (this.avatarImage) {
        return this.avatarImage;
      } else if (this.user.avatar_path) {
        return this.user.avatar_path;
      }
      return "/images/avatar-default.png";
    },
    authUser() {
      return window.Store.user;
    },
    canEdit() {
      return this.user.id === this.authUser.id;
    },
    countries() {
      return window.Store.countrySelectOptions;
    }
  },
  methods: {
    edit() {
      this.form.resetStatus();
      this.editing = true;
      window.$.extend(
        this.form,
        window._.pick(this.user, [
          "first_name",
          "last_name",
          "middle_name",
          "street_address",
          "street_address2",
          "city",
          "state",
          "email",
          "business_url",
          "twitter_url",
          "facebook_url",
          "phone_number",
          "phone_number_ext",
          "alt_phone_number",
          "alt_phone_number_ext",
          "description",
          "country_id"
        ])
      );
    },
    setExtensionPhone(data) {
      this.form.phone_number_ext = data;
    },
    setExtensionAltPhone(data) {
      this.form.alt_phone_number_ext = data;
    },
    submit() {
      window.App.post(`/api/v1/auth/user/update-profile`, this.form).then(
        response => {
          this.notify("success", "Changes has been saved.");
          window.$.extend(this.user, response.data);
          this.editing = false;
        }
      );
    },
    selectAvatar() {
      if (!this.canEdit) {
        return;
      }
      if (this.formAvatar.busy) {
        return;
      }
      this.$refs.inputAvatar.click();
    },
    onChangeAvatar(e) {
      window.Utils.getFileImageData(e.target).then(response => {
        this.avatarImage = response;
        this.formAvatar.avatar = e.target.files[0];
        window.App.postData(`/api/v1/auth/user/avatar`, this.formAvatar).then(
          response => {
            window.$.extend(this.user, response.data);
            this.formAvatar.avatar = null;
            this.notify("success", "Profile picture has been updated.");
          }
        );
      });
    },
    addTag (newTag) {
      const tag = {
        name: newTag,
        code: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000))
      }
      this.skills.push(newTag)
      this.value.push(newTag)
    }
  }
};
</script>
