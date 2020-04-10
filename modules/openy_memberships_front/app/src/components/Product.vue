<template>
  <div class="product">
    <div class="product-title"><h2>{{product.title}}</h2></div>
    <div class="product-description"><p v-html="product.field_description"></p></div>
    <div class="product-columns">
      <div>
        <div class="title">Purchase Options</div>
        <div class="options">
          <div class="branch">
            {{product.branch && product.branch.title}}
            {{product.branch === null && 'All branches'}}
          </div>

          <v-select :reduce="data => data.value" :clearable="false" :searchable="false" v-model="variant" :options="variants"></v-select>
          <div class="best-value" v-if="product.variations[variant].field_best_value == 1">Best value</div> 
        </div>
      </div>
      <div>
        <div class="title">Discounts & Add-Ons</div>
        <div class="options"></div>
      </div>
      <div>
        <div class="title">Cost Summary</div>
        <div class="options">
          <div class="product-columns">
            <div class="price-title">Dues</div>
            <div class="price-value text-align-right">{{ price }}</div>
          </div>
        </div>
      </div>
    </div>
    <a @click="selectProduct" class="select">
      SELECT
    </a>
  </div>
</template>
<script>
import vSelect from "vue-select";

export default {
  props: ['product', 'includes'],
  data() {
    return {
      variant: 0
    }
  },
  computed: {
    variants() {
      if (!this.product) {
        return []
      }
      return this.product.variations.map((variant, index) => {
        return {
          label: variant.title,
          value: index
        }
      })
    },
    price() {
      let price = this.product.variations && this.product.variations[this.variant] ? this.product.variations[this.variant].price : 'NaN';
      return price
    }
  },
  methods: {
    updateValue() {
      this.$emit('input', this.val)
    },
    selectProduct() {
      this.$store.commit('setProduct', {
        ...this.product,
        variant: this.variant
      })
      this.$emit('go-next')
    }
  },
  components: {
    vSelect
  }
}
</script>

<style lang="scss">
$vs-state-active-bg: #0060AF;
@import "vue-select/src/scss/vue-select.scss";
.vs__dropdown-toggle {
  padding: 10px 5px;
}
.best-value {
  background: #006600;
  color: #FFFFFF;
  text-transform: uppercase;
  display: flex;
  height: 30px;
  overflow: hidden;
  align-items: center;
  justify-content: space-between;
  font: 500 12px/18px Cachet, Verdana;

  margin: 15px 0 0;
  &::after {
    content: '';
    display: block;
    background-color: #FFFFFF;
    width: 30px;
    height: 30px;
    transform: translate(20px, 0px) rotate(45deg);
  }
  &::before {
    transform: translate(-20px, 0px) rotate(45deg);
    background-color: #FFFFFF;
    content: '';
    display: block;
    width: 30px;
    height: 30px;
  }
}
.membership-app {
  .product {
    padding: 15px;
    border: 1px solid #636466;
    border-top: 5px solid #0060AF;
    margin-bottom: 30px;
    h2 {
      text-align: left;
      font: 500 36px/54px 'Cachet', Verdana, sans-serif;
      letter-spacing: 0;
      color: #231F20;
      margin: 0px;
      padding: 0;
    }
    .select {
      background: #92278F 0% 0% no-repeat padding-box;
      border: 2px solid #92278F;
      font: 500 24px/36px 'Cachet', Verdana, sans-serif;
      letter-spacing: 0;
      color: #FFFFFF;
      text-align: center;
      padding: 10px;
      margin: 10px -15px -15px;
      text-decoration: none;
      cursor: pointer;
      display: block;
    }
    .product-columns {
      display: flex;
      flex-wrap: wrap;
      margin: 0 -5px;
      .options {
        
        .branch {
          padding: 15px 0;
          font: Bold 14px/21px Verdana;
          letter-spacing: 0;
          color: #231F20;
        }
      }
      .title {
        padding: 5px;
        color: #231F20;
        background-color: #f2f2f2;
        font: 500 18px/27px 'Cachet', Verdana, sans-serif;
      }
      > div {
        padding: 5px;
        flex: 1;
        .title {
          text-align: center;
        }
      }
    }
  }
}
</style>