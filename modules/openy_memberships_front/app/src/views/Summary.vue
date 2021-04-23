<template>
  <section class="app-container summary-wrapper">
    <loading :active.sync="isLoading"></loading>
    <h1 class="title">Your Membership</h1>
    <div class="description">
      <div
        class="description-text"
      >Here's a summary of your membership. Keep scrolling to take the next step!</div>
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
            <v-select
              @input="updateProduct"
              :reduce="data => data.value"
              :clearable="false"
              :searchable="false"
              :value="variant"
              :options="variants"
            ></v-select>
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
              <div class="actions">
                <button @click="goToDiscount" class="add-addon find-more">Find More</button>
                <!-- @todo: add clear all functionality -->
                <button @click="clearAddons" class="clear-all add-addon">Clear All</button>
              </div>
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
            <div class="item">
              <div class="option-title">
                <b>Dues</b>
              </div>
              <div class="option-price text-align-right">
                <b>${{ total_price | numFormat('0.00') }} / mo</b>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div>
        <a class="select" @click="$emit('go-next')">JOIN NOW</a>
      </div>
    </div>
  </section>
</template>

<script>
import Loading from "vue-loading-overlay";
import "vue-loading-overlay/dist/vue-loading.css";
import vSelect from "vue-select";
import Cart from "../helpers/Cart";

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
        return [];
      }
      return this.$store.state.product.variations.map((variant, index) => {
        return {
          label: variant.title,
          value: index
        };
      });
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

    Cart.getSummary()
      .then(json => {
        this.discounts = json.discounts.filter(item => {
          return item.title != null;
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
      this.$router.push({ name: "DiscountFinder" });
    },
    updateProduct(variant) {
      let new_variant = variant;
      this.isLoading = true;
      Cart.getCart()
        .then(json => {
          if (json[0]) {
            let order_id = json[0].order_id;

            return Cart.createOrder(
              this.product.variations[new_variant].id
            ).then(() => {
              let membership_items = json[0].order_items.filter(item => {
                return item.purchased_entity.type == "membership";
              });
              if (membership_items.length) {
                return Cart.removeItem(
                  order_id,
                  membership_items[0].order_item_id
                );
              }
              return true;
            });
          }
        })
        .then(() => {
          return Cart.getSummary();
        })
        .then(json => {
          this.discounts = json.discounts.filter(item => {
            return item.title != null;
          });
          this.$store.commit("setProduct", {
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
    },
    clearAddons() {
      this.isLoading = true;
      Cart.getCart()
        .then(json => {
          if (json[0]) {
            let order_id = json[0].order_id;
            let membership_items = json[0].order_items.filter(item => {
              return item && item.purchased_entity && item.purchased_entity.type != "membership";
            });
            return Promise.all(
              membership_items.map(item => {
                return Cart.removeItem(order_id, item.order_item_id);
              })
            );
          }
          return [];
        })
        .then(() => {
          return this.checkDiscounts();
        })
        .then(() => {
          return Cart.getSummary();
        })
        .then(json => {
          this.discounts = json.discounts.filter(item => {
            return item.title != null;
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
    checkDiscounts() {
      let members = this.$store.state.members.map(() => {
        return [0, 0]
      });
      this.$store.commit('setItem', {key: 'members', value: members});
      let checkboxes = members.reduce((total, currentValue) => {
        if (total != '') {
          total += '_';
        }
        return  total + currentValue.map(el => +el).join(',');
      }, '');
      let income = 0;
      this.$store.commit('setItem', {key: 'income', value: income});

      return window.jQuery.ajax({
        url: "/memberships/check/discounts/" + income + '/' + checkboxes,
        type: "GET",
        dataType: "json",
        data: {}
      })
    }
  },
};
</script>
