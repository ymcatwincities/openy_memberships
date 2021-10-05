<template>
  <div class="product">
    <div class="product-title"><h2>{{product.title}}</h2></div>
    <div class="product-description" v-html="product.field_description"></div>
    <div class="product-columns">
      <div v-if="this.product.variations.length > 1">
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
        <div class="title">Cost Summary</div>
        <div class="options">
          <div class="item">
            <div class="price-title"><b>Dues</b></div>
            <div class="price-value text-align-right"><b>${{ price | numFormat('0.00') }} / mo</b></div>
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
      });
      this.$store.commit('setItem', {
        key: 'keepCart',
        value: false,
      });
      this.$emit('go-next')
    }
  },
  components: {
    vSelect
  }
}
</script>
