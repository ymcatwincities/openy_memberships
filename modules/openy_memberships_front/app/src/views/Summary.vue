<template>
  <section class="app-container">
    <div class="container">
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
                {{product.branch === null && 'All branches'}}
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
              <div :key="index" v-for="(discount, index) in discounts">
                {{discount.title }}: {{discount.amount }}
                <p>{{discount.member_name}}</p>
              </div>
            </div>
          </div>
          <div>
            <div class="title">Cost Summary</div>
            <div class="options">
              <div class="product-columns">
                <div class="price-title">Dues</div>
                <div class="price-value text-align-right">{{ total_price }}</div>
              </div>
            </div>
          </div>
        </div>
        <a class="select">JOIN NOW</a>
      </div>
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
        this.discounts = json.discounts;
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
  }
};
</script>

<style lang="scss" scoped>
</style>

