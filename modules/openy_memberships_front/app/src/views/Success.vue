<template>
  <section class="app-container success-wrapper">
    <loading :active.sync="isLoading"></loading>
    <h1 class="title">
      Thank You
    </h1>
    <div class="description">
      <div class="description-text">
        <p><b>Your membership request has been received!</b></p>
        <p>One of our membership advisors will contact you within 24-hours to schedule a visit, confirm details, and issue your membership card.</p>
      </div>
    </div>
    <div class="print-wrapper">
      <a :href="'/memberships/get-summary-pdf/' + this.order_uuid" ><i class="fa fa-print"></i> Print</a>
    </div>
    <div class="product">
      <div class="product-title">
        <h2>{{product.title}}</h2>
      </div>
      <div class="product-description">
        <p v-html="product.field_description"></p>
      </div>
      <div class="product-columns">
        <div>
          <div class="title">Purchase Options</div>
          <div class="options">
            <div class="branch">
              {{product.branch && product.branch.title}}
              {{product.branch === null && 'All branches'}}
            </div>
            <div
              class="best-value"
              v-if="product.variations[variant].field_best_value == 1"
            >Best value</div>
          </div>
        </div>
        <!-- Remove condition if Discounts & Add-Ons is needed. -->
        <div v-if="false">
          <div class="title">Discounts & Add-Ons</div>
          <div class="options">
            <div :key="index" v-for="(discount, index) in discounts" class="item discount">
              <div class="option-title">
                {{discount.title }}:
                <p>{{discount.member_name}}</p>
              </div>
              <div class="option-price">- ${{ discount.amount | numFormat('0.00') }} /mo.</div>
            </div>
            <div :key="index" v-for="(addon, index) in addons" class="item addon">
              <div class="option-title">{{addon.title }}:</div>
              <div class="option-price">+ ${{ addon.amount | numFormat('0.00') }} /mo.</div>
            </div>
            <div class="find-more-wrapper">
              <div class="info">
                <p>
                  <i>* Proof of eligibility will be required.</i>
                </p>
                <p>
                  <i>† Member must work out at least 12 times per month to claim discount.</i>
                </p>
              </div>
            </div>
          </div>
        </div>
        <div>
          <div class="title">Cost Summary</div>
          <div class="options">
            <div class="product-columns">
              <div class="price-title"><b>Dues</b></div>
              <div class="price-value text-align-right"><b>${{ total_price | numFormat('0.00') }} / mo</b></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import Loading from "vue-loading-overlay";
import "vue-loading-overlay/dist/vue-loading.css";
import Cart from '../helpers/Cart';

export default {
  computed: {
    product() {
      return this.$store.state.product;
    },
    variant() {
      return this.$store.state.product.variant;
    },
    variants() {
      return this.$store.state.product.variants;
    }
  },
  data() {
    return {
      discounts: [],
      addons: [],
      isLoading: false,
      total_price: 0,
      currency: "USD",
      order_uuid: ''
    };
  },
  mounted() {
    this.isLoading = true;

    window.jQuery
      .ajax({
        url: "/memberships/api/summary",
        dataType: "json",
        data: {}
      })
      .then(json => {
        this.discounts = json.discounts.filter((item) => {
          return item.title != null
        });
        this.addons = json.addons;
        this.total_price = json.total_price;
        this.currency = json.currency;
      })
      .then(() => {
        return Cart.getCart();
      })
      .then((json) => {
        return Cart.sendSummaryEmails(json[0].uuid).then(() => {
          return json;
        })
      })
      .then((json) => {
        return Cart.updateOrderStatus(json[0].uuid).then(() => {
          this.$store.dispatch('clearStorage')

          this.isLoading = false;
          this.order_uuid = json[0].uuid;
        })
      })
      .catch(() => {
        this.isLoading = false;
        this.$store.commit('setStep', 0);
      });
  },
  components: {
    Loading
  },
  methods: {
    goToDiscount() {
      this.$router.push({ name: 'DiscountFinder'});
    }
  }
};
</script>
