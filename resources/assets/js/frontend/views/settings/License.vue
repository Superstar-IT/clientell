<template>
  <div>
    <div class="d-flex justify-content-between">
      <span slot="header" class="h4 text-left mt-3">License & Certs</span>
      <b-btn variant="link" @click="addNewLicense">
        <i class="fa fa-plus"></i> Add License
      </b-btn>
    </div>
    <div>
      <div v-if="isEditing">
        <b-form @submit.prevent="saveLicense">
          <b-form-group
            horizontal
            label-cols="4"
            breakpoint="md"
            label="License Name *"
            label-class="text-md-right"
            label-for="name"
            :invalid-feedback="form.errors.get('name')"
          >
            <b-form-input id="name" v-model="form.name" :state="form.errors.state('name')"></b-form-input>
          </b-form-group>

          <b-form-group
            horizontal
            label-cols="4"
            breakpoint="md"
            label="License # *"
            label-class="text-md-right"
            label-for="number"
            :invalid-feedback="form.errors.get('number')"
          >
            <b-form-input id="number" v-model="form.number" :state="form.errors.state('number')"></b-form-input>
          </b-form-group>

          <b-form-group
            horizontal
            label-cols="4"
            breakpoint="md"
            label="License Expiration"
            label-class="text-md-right"
            label-for="expiration"
            :invalid-feedback="form.errors.get('expiration')"
          >
            <div class="d-block position-relative">
              <date-picker v-model="form.expiration" valueType="format"></date-picker>

            </div>
          </b-form-group>

          <b-form-group
            horizontal
            class="mb-3"
            label-cols="4"
            breakpoint="md"
            label="License Insured"
            label-class="text-md-right"
            label-for="is_insured"
            :invalid-feedback="form.errors.get('is_insured')"
            :state="form.errors.state('is_insured')"
          >
            <div class="d-flex align-items-center pt-2">
              <b-form-radio-group
                id="is_insured"
                v-model="form.is_insured"
                :options="insuredOptions"
                :state="form.errors.state('is_insured')"
              ></b-form-radio-group>
            </div>
          </b-form-group>

          <b-form-group
            horizontal
            label-cols="4"
            breakpoint="md"
            label="Description"
            label-class="text-md-right"
            label-for="description"
            :invalid-feedback="form.errors.get('description')"
          >
            <b-form-input
              id="description"
              v-model="form.description"
              :state="form.errors.state('description')"
            ></b-form-input>
          </b-form-group>
          <div class="float-right">
                        <b-btn :disabled="form.busy" variant="primary" type="submit"><i class="fa fa-save"></i> Save</b-btn>
                        <b-btn :disabled="form.busy" @click="isEditing = false">Cancel</b-btn>
                    </div>
                    <div class="clearfix"></div>
        </b-form>
      </div>
      <div v-for="(license) in licenses" :key="license.id" class="p-4 pt-2 pb-2">
        <div class="d-flex justify-content-between align-items-center h6">
          {{license.name}}
          <div>
            <b-btn variant="link" @click="editLicense(license)">
              <i class="fa fa-edit"></i>Edit
            </b-btn>
            <b-btn variant="link" @click="editLicense(license)">
              <i class="fa fa-trash"></i>Delete
            </b-btn>
          </div>
        </div>
        <div>{{license.number}} • {{license.expiration}}</div>
        <b-row v-if="license.photos && !!license.photos.data.length">
          <b-col v-for="(photo, key) in license.photos" :key="key">
            <img :src="photo.url" />
          </b-col>
        </b-row>
        <div>
          <span>License Insured?</span>
          <b class="pl-5">{{license.is_insured ? 'Yes' : 'No'}}</b>
        </div>
      </div>
      
    </div>
  </div>
</template>
<script>
import DatePicker from 'vue2-datepicker';
  import 'vue2-datepicker/index.css';
 
export default {
  components: {
    DatePicker,
  },
  data() {
    return {
      user: window.Store.user,
      licenses: [],
      isEditing: false,
      form: new window.Form({
        name: "",
        number: "",
        expiration: "",
        is_insured: ""
      }),
      insuredOptions: [
        { value: 0, text: "No" },
        { value: 1, text: "Yes" }
      ]
    };
  },
  mounted() {
    window.App.get(
      `/api/v1/license`,
      new window.Form({ user_id: this.user.id })
    ).then(res => {
      this.licenses = res.data.data;
    });
  },
  methods: {
    addNewLicense() {
      this.isEditing = true;
      this.form = new window.Form({
        name: "",
        number: "",
        expiration: "",
        is_insured: 0
      });
    },
    editLicense(license) {
      this.isEditing = true;
      this.form = new window.Form(license);
    },
    deleteLicense(license) {
      window.App.delete(`/api/v1/license/${license.id}`);
    },
    saveLicense() {
      if (this.form.id) {
        const formData = new window.Form({
          id: this.form.id,
          name: this.form.name,
          number: this.form.number,
          expiration: this.form.expiration,
          is_insured: this.form.is_insured
        })
        window.App.put(`/api/v1/license/${this.form.id}`, formData).then(
          res => {
            this.notify("success", "License is updated.");
            this.isEditing = false
            const index = this.licenses.findIndex(item => item.id == formData.id)
            this.licenses[index] = res.data
          }
        );
      } else {
       
        window.App.post(`/api/v1/license`, this.form).then(res => {
          this.notify("success", "License is added.");
          console.log(res)
          if (res.data) {
            this.licenses.push(res.data)
            this.isEditing = false
          }
        });
      }
    }
  }
};
</script>