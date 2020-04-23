<template>
  <section class="app-container">
    <div class="container">
      <loading :active.sync="isLoading"></loading>
      <div class="">
        <div class="">
          <h1 class="title">
            Thank You
          </h1>
        </div>
      </div>
      <div class="description">
        <div class="description-text">
          <b>Your membership request has been received!</b>
          <p>One of our membership advisors will contact you within 24-hours to schedule a visit, confirm details, and issue your membership card.</p>
        </div>
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
              {{product.variations[variant].title}} (${{ product.variations[variant].price | numFormat('0.00') }} /mo.)
              <div
                class="best-value"
                v-if="product.variations[variant].field_best_value == 1"
              >Best value</div>
            </div>
          </div>
          <div>
            <div class="title">Discounts & Add-Ons</div>
            <div class="options">
              <div :key="index" v-for="(discount, index) in discounts">
                <div class="addon">
                  <div class="addon-title">{{discount.title }}:</div> 
                  <div class="addon-price">- ${{ discount.amount | numFormat('0.00') }} /mo.</div>
                  <p>{{discount.member_name}}</p>
                </div>
              </div>
              <div :key="index" v-for="(addon, index) in addons">
                  <div class="addon">
                    <div class="addon-title">{{addon.title }}:</div>
                    <div class="addon-price">+ ${{ addon.amount | numFormat('0.00') }} /mo.</div>
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
      currency: "USD"
    };
  },
  mounted() {
    this.isLoading = true;

    window.jQuery
      .ajax({
        url: "/om-model/data/summary",
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
        return Cart.updateOrderStatus(json[0].uuid).then(() => {
          this.$store.dispatch('clearStorage')

          this.isLoading = false;
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
