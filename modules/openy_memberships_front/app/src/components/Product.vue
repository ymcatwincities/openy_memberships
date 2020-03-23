<template>
  <div class="product">
    <div class="product-title"><h2>{{product.attributes.title}}</h2></div>
    <div class="product-description"><p v-html="product.attributes.field_description && product.attributes.field_description.processed"></p></div>
    <div class="product-columns">
      <div>
        <div class="title">Purchase Options</div>
        <div class="options">
          <div class="branch">
            {{product.branch && product.branch.attributes.title}}
            {{product.branch === null && 'All branches'}}
          </div>
          <select v-model="variant">
            <option :value="key" :key="variant.id" v-for="(variant, key) in product.variants"> {{variant.attributes.title}}</option>
          </select>
          <div v-if="product.variants[variant].attributes.field_best_value">Best value</div> 
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
    <div class="select">
      SELECT
    </div>
  </div>
</template>
<script>
export default {
  props: ['product', 'includes'],
  data() {
    return {
      variant: 0
    }
  },
  computed: {
    price() {
      let price = this.product.variants && this.product.variants[this.variant] ? this.product.variants[this.variant].attributes.price.formatted : 'NaN';
      return price
    }
  },
  methods: {
    updateValue() {
      this.$emit('input', this.val)
    }
  }
}
</script>

<style lang="scss">
.product {
  padding: 10px;
  border: 1px solid #636466;
  border-top: 5px solid #0060AF;
  margin-bottom: 20px;
  h2 {
    text-align: left;
    font: Medium 36px/54px Cachet;
    letter-spacing: 0;
    color: #231F20;
  }
  .select {
    background: #92278F 0% 0% no-repeat padding-box;
    border: 2px solid #92278F;
    font: Medium 24px/36px Cachet;
    letter-spacing: 0;
    color: #FFFFFF;
    text-align: center;
    padding: 15px;
    margin: 10px -10px -10px;
  }
  .product-columns {
    display: flex;
    flex-wrap: wrap;
    .options {
      margin: 5px;
      .branch {
        font: Bold 14px/21px Verdana;
        letter-spacing: 0;
        color: #231F20;
      }
    }
    .title {
      padding: 5px;
      margin: 5px;
      color: #231F20;
      background-color: #f2f2f2;
    }
    > div {
      flex: 1;
      .title {
        text-align: center;
      }
    }
  }
}
</style>