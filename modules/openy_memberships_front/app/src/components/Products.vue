<template>
  <div>
    <product :key="key" :product="product" v-for="(product, key) in products" :includes="includes" />
  </div>
</template>
<script>
import Product from './Product'
export default {
  props: ['products', 'includes'],
  components: {
    Product
  }
}
</script>

<style lang="scss">
  
</style>