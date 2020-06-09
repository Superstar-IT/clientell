<template>
  <div>
    <b-card>
      <h4 slot="header" class="text-center">Subscription</h4>
      <template v-if="user.is_free_account">
        <p>Free Forever!</p>
        <input type="checkbox" id="checkbox" v-model="updatePayment">
        <label for="checkbox">Add Payment</label>
        <template v-if="updatePayment">
          <b-form @submit.prevent="register">
            <b-form-group
              horizontal
              class="mt-4"
              label-cols="4"
              breakpoint="md"
              label="Subscription"
              label-class="text-md-right"
            >
              <div class="pt-2">
                <div class="custom-control custom-radio">
                  <input
                    type="radio"
                    id="radio-monthly"
                    name="subscription"
                    class="custom-control-input"
                    value="monthly"
                  
                  />
                  <label
                    class="custom-control-label"
                    for="radio-monthly"
                  >Monthly - {{ config.services.stripe.plan.price | currency }}</label>
                </div>
                <div class="custom-control custom-radio">
                  <input
                    type="radio"
                    id="radio-yearly"
                    name="subscription"
                    class="custom-control-input"
                    value="yearly"
                    
                  />
                  <label
                    class="custom-control-label"
                    for="radio-yearly"
                  >Yearly - {{ config.services.stripe.yearly_plan.price | currency }}</label>
                </div>
              </div>
            </b-form-group>
            <b-form-group
              horizontal
              label-cols="4"
              breakpoint="md"
              label="Credit Card"
              label-class="text-md-right"
              label-for="credit_card"
            >
              <card
                id="credit_card"
                class="stripe-card"
                :class="{ complete: card.complete }"
                :stripe="stripeKey"
                :options="card.options"
                @change="cardChange"
              ></card>
              <div
                class="text-danger small mt-1"
                v-if="form.errors.has('card_token')"
                v-text="form.errors.get('card_token')"
              ></div>
            </b-form-group>

            <b-form-group
              horizontal
              class="mb-1"
              label-cols="4"
              breakpoint="md"
              label="Promo Code"
              label-class="text-md-right"
              label-for="coupon_code"
              :invalid-feedback="form.errors.get('coupon_code')"
            >
              <template v-if="!coupon">
                <b-form-input
                  id="coupon_code"
                  v-model="form.coupon_code"
                  :state="form.errors.state('coupon_code')"
                ></b-form-input>
              </template>
              <div class="alert alert-success" v-else>
                <i class="fa fa-check-circle"></i>
                {{ coupon.name }}
              </div>
            </b-form-group>
            <b-form-group
              horizontal
              label-cols="4"
              breakpoint="md"
              label-class="text-md-right"
              v-if="!coupon"
            >
              <a href="#" @click.prevent="checkCoupon">
                <i class="fa fa-spinner fa-spin" v-if="checkingCoupon"></i> Click here to see if the promo code is valid
              </a>
            </b-form-group> 
            <div class="form-group row mb-0 mt-5">
              <b-btn type="submit" variant="primary" :disabled="form.busy" style="margin: 0 auto">
                <!--<i v-if="form.busy"
                class="fa fa-spinner fa-spin"></i>-->
                Submit
              </b-btn>
            </div>
          </b-form>
        </template>
      </template>
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
import { Card, createToken } from "vue-stripe-elements-plus";

export default {
  components: {
    Security,
    Profile,
    ImportCsv,
    ImportQuickbooks,
    DownloadApp,
    SubscribeNow,
    Card
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
      stripeValid: false,
      updatePayment: false,
      type: "",
      checkingCoupon: false,
      coupon: null,
      card: {
        complete: false,
        options: {}
      },
      redirectUrl: "/setting/account?after_signup=1",
      form: new window.Form({
        first_name:"",
        middle_name:"",
        last_name:"",
        street_address:"",
        street_address2:"",
        city:"",
        state:"",
        postal_code:"",
        phone_number:"",
        phone_number_ext:"",
        alt_phone_number:"",
        alt_phone_number_ext:"",
        is_free_account: 0,
        payment_method: "stripe"
      })
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
    },
    config() {
      return window.Store.config;
    },
    stripeKey() {
      return window.Store.config.keys.stripe;
    },
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
          if(response.data.payment_method == 'stripe')
            this.stripeValid = true;
          console.log(this.user.is_on_trial);
          if(this.user.is_free_account)
            this.updatePayment = false;
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
    },
    checkCoupon() {
      if (this.checkingCoupon) {
        return;
      }
      this.checkingCoupon = true;
      window.App.post("/web-api/coupon/check", this.form)
        .then(response => {
          this.checkingCoupon = false;
          this.coupon = response.data;
        })
        .catch(() => (this.checkingCoupon = false));
    },
    cardChange(e) {
      this.card.complete = e.complete;
    },
    register() {
      let self = this;
      this.form.first_name = this.user.first_name;
      this.form.middle_name = this.user.middle_name;
      this.form.last_name = this.user.last_name;
      this.form.street_address = this.user.street_address;
      this.form.street_address2 = this.user.street_address2;
      this.form.city = this.user.city;
      this.form.state = this.user.state;
      this.form.postal_code = this.user.postal_code;
      this.form.phone_number = this.user.phone_number;
      this.form.phone_number_ext = this.user.phone_number_ext;
      this.form.alt_phone_number = this.user.alt_phone_number;
      this.form.alt_phone_number_ext = this.user.alt_phone_number_ext;

    console.log(this.form);
      this.form.startProcessing();
      if (this.config.app.env === "testing") {
        window.App.post(
          this.user ? "/register/finish" : "/register",
          this.form
        ).then(() => {});
      } else {
        let proceed = () => {
          window.App.post(
            this.user ? "/register/finish" : "/register",
            this.form
          ).then(() => {
            this.form.startProcessing();
            if (this.shouldReportAnalytics) {
              if (typeof fbq("track", "CompleteRegistration") === "undefined") {
                window.location = this.redirectUrl;
              } else {
                window.location = this.redirectUrl;
              }
            } else {
              window.location = this.redirectUrl;
            }
          });
        };
        // if (this.config.settings.free_account_on_register_enabled) {
        if (this.form.is_free_account) {
          proceed();
        } else {
          createToken()
            .then(response => {
              this.form.card_token = response.token.id;
              proceed();
            })
            .catch(() => {
              this.form.finishProcessing(false);
              this.form.errors.add("card_token", "Invalid card details.");
            });
        }
      }
    },
  }
};
</script>

<style lang="scss" scoped>
.form-update-password,
.form-import-csv {
  max-width: 600px;
}
</style>