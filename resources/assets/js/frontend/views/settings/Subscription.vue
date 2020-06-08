<template>
  <div>
    <b-card>
      <h4 slot="header" class="text-center">Subscription</h4>
      <p v-if="user.is_free_account">Free Forever!</p>
      <template v-else>
          <div v-if="user.is_subscribed_to_plan">
            <div v-if="user.subscription.is_on_grace_period">
              <span class="badge badge-warning">Cancelled</span>
              <span>Ends on {{ user.subscription.ends_at | date }}.</span>
              <br />
              <b-btn class="mt-2" variant="success" size="sm" @click="resume">
                <i class="fa fa-check"></i> Resume Subscription
              </b-btn>
            </div>
            <div v-else>
              
              <span class="badge badge-success">Subscribed</span>
              <span v-if="renewsAt">Automatically renews on {{ renewsAt | date }}.</span>
              <i v-else class="fa fa-spinner fa-spin"></i>
              <br />
              <div v-if="isOnStripTrial">
                <span class="badge badge-warning">On Trial Period</span>
                <span v-if="subscription.trial_end"> You are on trial period until {{ subscription.trial_end * 1000 | datetime }}.</span>
              </div>
              <b-btn class="mt-2" variant="danger" size="sm" @click="cancel">
                <i class="fa fa-times"></i> Cancel Subscription
              </b-btn>
            </div>
          </div>
          <div v-else>
            <span class="badge badge-danger">Cancelled</span>
            <span>Ended on {{ user.subscription.ends_at | date }}.</span>
            <br />
            <b-btn class="mt-2" variant="success" size="sm" @click="resume">
              <i class="fa fa-check"></i> Resume Subscription
            </b-btn>
          </div>
      </template>
      <b-modal ref="modalCancel" title="Confirmation">
        <div>Are you sure you want to cancel subscription?</div>
        <div slot="modal-footer">
          <b-btn @click="$refs.modalCancel.hide()" :disabled="cancelling">Cancel</b-btn>
          <b-btn variant="danger" @click="confirmCancel" :disabled="cancelling">
            <i class="fa" :class="{'fa-remove': !cancelling, 'fa-spinner fa-spin': cancelling}"></i> Cancel Subscription
          </b-btn>
        </div>
      </b-modal>
      <b-modal ref="modalResume" title="Confirmation">
        <div>Confirm resume subscription?</div>
        <div slot="modal-footer">
          <b-btn @click="$refs.modalResume.hide()" :disabled="resuming">Cancel</b-btn>
          <b-btn variant="success" @click="confirmResume" :disabled="resuming">
            <i class="fa" :class="{'fa-check': !resuming, 'fa-spinner fa-spin': resuming}"></i> Resume Subscription
          </b-btn>
        </div>
      </b-modal>
    </b-card>
    <!-- <b-card class="mt-3">
            <h4 slot="header" class="text-center">
                Profile
            </h4>
            <profile :user="user"/>
    </b-card>-->
  </div>
</template>

<script>
import Security from "./Security";
import Profile from "./Profile";
import ImportCsv from "./ImportCSV";
import ImportQuickbooks from "./ImportQuickbooks";
import DownloadApp from "./DownloadApp";
import SubscribeNow from "./trial/SubscribeNow";

export default {
  components: {
    Security,
    Profile,
    ImportCsv,
    ImportQuickbooks,
    DownloadApp,
    SubscribeNow
  },
  beforeRouteEnter(to, from, next) {
    next(vm => {
      vm.setType(to.params.type);
    });
  },
  beforeRouteUpdate(to, from, next) {
    console.log("object");
    this.setType(to.params.type);
  },
  data() {
    return {
      subscription: null,
      cancelling: false,
      resuming: false,
      type: ""
    };
  },
  computed: {
    user() {
      return window.Store.user;
    },
    renewsAt() {
      if (this.subscription) {
        return window.moment.unix(this.subscription.current_period_end);
      }
      return null;
    },
    trialEndsAt() {
      if (this.user.is_on_trial) {
        return window.moment.utc(this.user.trial_ends_at);
      }
      return null;
    },
    isOnStripTrial() {
      if (this.subscription) {
        return this.subscription.trial_end > (new Date().getTime() / 1000)
      } 
      return false
    }
  },
  mounted() {
    this.getSubscription();
  },
  methods: {
    getSubscription() {
      window.axios
        .get("/api/v1/auth/user?include=stripe_subscription")
        .then(response => {
          console.log(response)
          this.subscription = response.data.stripe_subscription;
          console.log(this.user.is_on_trial);
        });
    },
    cancel() {
      this.$refs.modalCancel.show();
    },
    confirmCancel() {
      if (this.cancelling) {
        return;
      }
      this.cancelling = true;
      window.axios
        .post(`/api/v1/subscription/cancel`)
        .then(() => {
          window.Store.getUser()
            .then(() => {
              this.$refs.modalCancel.hide();
            })
            .finally(() => {
              this.cancelling = false;
            });
        })
        .catch(() => {
          this.cancelling = true;
        });
    },
    resume() {
      this.$refs.modalResume.show();
    },
    confirmResume() {
      if (this.resuming) {
        return;
      }
      this.resuming = true;
      window.axios
        .post(`/api/v1/subscription/resume`)
        .then(() => {
          window.Store.getUser(null, ["stripe_subscription"])
            .then(user => {
              this.subscription = user.stripe_subscription;
              this.$refs.modalResume.hide();
            })
            .finally(() => {
              this.resuming = false;
            });
        })
        .catch(() => {
          this.resuming = true;
        });
    },
    setType(type) {
      this.type = type;
    }
  }
};
</script>

<style lang="scss" scoped>
.form-update-password,
.form-import-csv {
  max-width: 600px;
}
</style>