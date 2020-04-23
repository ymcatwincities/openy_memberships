<template>
  <section class="app-container">
    <loading :active.sync="isLoading"></loading>
    <h1 class="title">
      Join Now
    </h1>
    <div class="description">
      <div class="description-text">
        Fill out the form below and membership advisor will contact you within 24-hours.
      </div>
    </div>
    <div class="billing">
      <div class="billing-wrapper">
        <div class="field required" :class="{'error': errors.firstName}">
        <label for="">First Name</label>
        <input v-model="billing.firstName" />
        <span class="error">Enter valid First Name</span>
      </div>
      <div class="field required" :class="{'error': errors.lastName}">
        <label for="">Last Name</label>
        <input v-model="billing.lastName" />
        <span class="error">Enter valid Last Name</span>
      </div>
      <div class="field required" :class="{'error': errors.email}">
        <label for="">Email Address</label>
        <input v-model="billing.email" />
        <span class="error">Enter valid email</span>
      </div>
      <div class="field required" :class="{'error': errors.phone}">
        <label for="">Phone Number</label>
        <input v-model="billing.phone" />
        <span class="error">Enter valid phone</span>
      </div>
      </div>
    </div>
    <div class="navigation" v-if="valid">
      <div class="container">
        <button class="btn btn-next" @click="submit">Next</button>
      </div>
    </div>
  </section>
</template>

<script>
import Loading from "vue-loading-overlay";
import "vue-loading-overlay/dist/vue-loading.css";
import User from '../helpers/User';
import Cart from '../helpers/Cart';

export default {
  computed: {
    valid() {
      return this.billing.firstName.length && this.billing.lastName.length && this.billing.email.length && this.billing.phone.length;
    }
  },
  data() {
    return {
      billing: {
        firstName: '',
        lastName: '',
        email: '',
        phone: ''
      },
      errors: {
        firstName: false,
        lastName: false,
        email: false,
        phone: false
      },
      validateFlag: false,
      token: null,
      isLoading: false
    };
  },
  mounted() {

  },
  components: {
    Loading
  },
  methods: {
    setBilling(order_id) {
      return Cart.setBilling(order_id, this.billing);
    },
    validate() {
      if(!this.validateFlag) {
        return true;
      }
      let mailformat = /\S+@\S+\.\S+/;
      this.errors = {
        firstName: false,
        lastName: false,
        email: false,
        phone: false
      }
      if(this.billing.firstName == '') {
        this.errors.firstName = true
      }
      if(this.billing.lastName == '') {
        this.errors.lastName = true
      }
      if(!this.billing.email.match(mailformat)) {
        this.errors.email = true
      }
      if(this.billing.phone == '') {
        this.errors.email = true
      }
    },
    submit() {
      this.validateFlag = true;
      this.validate();
      let valid = Object.keys(this.errors).filter((error) => {
        return this.errors[error] == true
      })
      if (valid.length == 0) {
        let operations = [
          Cart.getCart(),
          User.getUserInfo(),
        ];
        this.isLoading = true;
        Promise.all(operations).then((json) => {
          let cart = json[0];
          return this.setBilling(cart[0].order_id);
        }).then(() => {
           this.$emit('go-next');
           this.isLoading = false;
        }).catch(() => {
           this.isLoading = false;
        })
      }
    }
  },
  watch: {
    billing: {
      deep: true,
      handler() {
        this.validate();
        this.$store.commit('setItem', {
          key: 'billing',
          value: this.billing
        })
      }
    }
  }
};
</script>
