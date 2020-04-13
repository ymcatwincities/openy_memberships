<template>
  <div>
    <product @go-next="$emit('go-next')" :key="key" :product="product" v-for="(product, key) in products" />
  </div>
</template>
<script>
import Product from './Product'
export default {
  props: ['products'],
  components: {
    Product
  }
}
</script>
