<template>
  <section class="app-container">
    <h1 class="title">
      Membership Builder
    </h1>
    <div class="description">
      <div class="description-text">
        How many people will be included in your membership?
      </div>
    </div>

    <div class="family-wrapper">
      <div :key="index"  class="label-row" v-for="(group, index) in age_groups">
        <div class="label-row">
          <div class="label">{{group}}</div><div class="value"><integer-minus-plus :value="$store.state.family[group]" @input="updateFamily(group, $event)" /></div>
        </div>
      </div>
    </div>
    <div class="navigation" v-if="totalCount">
      <div class="container">
        <button class="btn btn-next" @click="$emit('go-next')">Next</button>
      </div>
    </div>
  </section>
</template>

<script>
import IntegerMinusPlus from '../components/IntegerMinusPlus'
import Cart from '../helpers/Cart';
export default {
  mounted() {
    Cart.getAgeGroups().then(json => {
      this.age_groups = Object.keys(json).map((key) => {
        return json[key].title;
      })
    })
  },
  computed: {
    totalCount() {
      let count = 0;
      Object.keys(this.$store.state.family).forEach(element => {
        count = count + this.$store.state.family[element]
      });
      return count;
    }
  },
  components: {
    IntegerMinusPlus
  },
  data () {
    return {
      age_groups: [],
      family: {
        ...this.$store.state.family
      }
    }
  },
  methods: {
    updateFamily(key, value) {
      this.family[key] = value
      this.$store.commit('setFamily', this.family)
    }
  }
}
</script>
