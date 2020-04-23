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
              <v-select @input="updateProduct" :reduce="data => data.value" :clearable="false" :searchable="false" :value="variant" :options="variants"></v-select>
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
              <div>
                <div class="addon">
                  <div class="addon-title"><button @click="goToDiscount" class="add-addon">Find More</button></div>
                  <div class="addon-price"></div>
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
        <a class="select" @click="$emit('go-next')">JOIN NOW</a>
      </div>
    </div>
  </section>
</template>

<script>
import Loading from "vue-loading-overlay";
import "vue-loading-overlay/dist/vue-loading.css";
import vSelect from "vue-select";
import Cart from "../helpers/Cart"

export default {
  computed: {
    product() {
      return this.$store.state.product;
    },
    variant() {
      return this.$store.state.product.variant;
    },
    variants() {
      if (!this.$store.state.product) {
        return []
      }
      return this.$store.state.product.variations.map((variant, index) => {
        return {
          label: variant.title,
          value: index
        }
      })
    },
  },
  data() {
    return {
      discounts: [],
      addons: [],
      isLoading: false,
      total_price: 0,
      currency: "USD",
    };
  },
  mounted() {
    this.isLoading = true;

    Cart.getSummary()
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
    Loading,
    vSelect
  },
  methods: {
    goToDiscount() {
      this.$router.push({ name: 'DiscountFinder'});
    },
    updateProduct(variant) {
      let new_variant = variant;
      this.isLoading = true;
      Cart.getCart().then(json => {
        if(json[0]) {
          let order_id = json[0].order_id;
          
          return Cart.createOrder(this.product.variations[new_variant].id).then(() => {
            let membership_items = json[0].order_items.filter((item) => {
              return item.purchased_entity.type == "membership";
            })
            if (membership_items.length) {
              return Cart.removeItem(order_id, membership_items[0].order_item_id)
            }
            return true;
          });
        }
      }).then(() => {
        return Cart.getSummary();
      }).then(json => {
        this.discounts = json.discounts.filter((item) => {
          return item.title != null
        });
        this.$store.commit('setProduct', {
          ...this.product,
          variant: new_variant
        });
        this.addons = json.addons;
        this.total_price = json.total_price;
        this.currency = json.currency;
        this.isLoading = false;
      })
      .catch(() => {
        this.isLoading = false;
      });
    }
  }
};
</script>
