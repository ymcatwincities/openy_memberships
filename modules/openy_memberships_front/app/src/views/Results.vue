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
        <products @go-next="$emit('go-next')" v-if="this.products.length" :products="products" />
        <div v-if="!this.products.length">
          No suitable products was found.
        </div>
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
      url: '/jsonapi/commerce_product/membership?include=variations,field_product_branch',
      dataType: 'json',
      data: {
        filter: {
          'branch-group' : {
            'group': {
              'conjunction': 'OR'
            }
          },
          'branch-filter': {
            'condition': {
              'path': 'field_product_branch.drupal_internal__nid',
              'operator': '=',
              'value': this.$store.state.location,
              'memberOf': 'branch-group'
            }
          },
          'branch-filter-null': {
            'condition': {
              'path': 'field_product_branch.drupal_internal__nid',
              'operator': 'IS NULL',
              'memberOf': 'branch-group'
            }
          }
        }
      }
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
        let branch = data.data[key].relationships.field_product_branch.data;
        branch = branch && branch.id ? included[branch.id] :  null;
        return {
          ...product,
          variants,
          branch
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

