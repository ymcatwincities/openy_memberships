<template>
  <section class="app-container summary-wrapper">
    <loading :active.sync="isLoading"></loading>
    <div class="">
      <div class="">
        <h1 class="title">
          Your Membership
        </h1>
      </div>
    </div>
    <div class="description">
      <div class="description-text">
        Here's a summary of your membership. Keep scrolling to take the next step!
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
              {{product.branch === null && 'All Branch Access'}}
            </div>
            <div
              class="best-value"
              v-if="product.variations[variant].field_best_value == 1"
            >Best value</div>
          </div>
        </div>
        <div>
          <div class="title">Discounts & Add-Ons</div>
          <div class="options">
            <div :key="index" v-for="(discount, index) in discounts" class="item discount">
              <div class="option-title">{{discount.title }}: <p>{{discount.member_name}}</p></div>
              <div class="option-price">- ${{ discount.amount | numFormat('0.00') }} /mo.</div>
            </div>
            <div :key="index" v-for="(addon, index) in addons" class="item addon">
              <div class="option-title">{{addon.title }}:</div>
              <div class="option-price">+ ${{ addon.amount | numFormat('0.00') }} /mo.</div>
            </div>
            <div class="find-more-wrapper">
              <div class="actions">
                <button @click="goToDiscount" class="add-addon find-more">Find More</button>
                <!-- @todo: add clear all functionality -->
                <button class="clear-all add-addon">Clear All</button>
              </div>
              <div class="info">
                <p><i>* Proof of eligibility will be required.</i></p>
                <p><i>† Member must work out at least 12 times per month to claim discount.</i></p>
              </div>
            </div>
          </div>
        </div>
        <div>
          <div class="title">Cost Summary</div>
          <div class="options">
            <div class="item">
              <div class="option-title"><b>Dues</b></div>
              <div class="option-price text-align-right"><b>${{ total_price | numFormat('0.00') }} / mo</b></div>
            </div>
          </div>
        </div>
      </div>
      <a class="select">JOIN NOW</a>
    </div>
  </section>
</template>

<script>
import Loading from "vue-loading-overlay";
import "vue-loading-overlay/dist/vue-loading.css";

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
        this.isLoading = false;
      })
      .catch(() => {
        this.isLoading = false;
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
