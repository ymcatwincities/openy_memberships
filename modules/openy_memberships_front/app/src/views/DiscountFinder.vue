<template>
  <section class="container">
    <div>
      <div class="">
        <div class="">
          <h1 class="title">
            Membership Builder
          </h1>
        </div>
      </div>
      <div class="description">  
        <div class="description-text">
          Check out these great options!
        </div>
      </div>
      <div>
        <loading :active.sync="isLoading"></loading>
        <products :products="products" />
      </div>
    </div>
  </section>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Products from '../components/Products'

export default {
  mounted() {
    this.isLoading = true;
    window.jQuery.ajax({
      url: '/jsonapi/commerce_product/membership?include=variations',
      dataType: 'json'
    }).then((data)=>{
      this.isLoading = false
      let included = {}
      Object.keys(data.included).forEach(key => {
        let variant = data.included[key];
        included[variant.id] = variant
      })
      this.products = Object.keys(data.data).map(key => {
        let product = data.data[key];
        let variants = data.data[key].relationships.variations.data.map(variant => {
          return included[variant.id];
        })
        return {
          ...product,
          variants
        }
      })
    }).catch(() => {
      this.isLoading = false
    })
  },
  components: {
    Loading,
    Products
  },
  data () {
    return {
      isLoading: false,
      products: []
    }
  }
}
</script>

<style lang="scss" scoped>

</style>

