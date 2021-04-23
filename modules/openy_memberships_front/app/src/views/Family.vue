<template>
  <section class="app-container">
    <loading :active.sync="isLoading"></loading>
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
          <div class="label">{{group.title}}</div><div class="value"><integer-minus-plus :value="$store.state.family[group.uuid].count" @input="updateFamily(group.uuid, $event)" /></div>
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
import Loading from "vue-loading-overlay";
import "vue-loading-overlay/dist/vue-loading.css";
import IntegerMinusPlus from '../components/IntegerMinusPlus'
import Cart from '../helpers/Cart';
export default {
  mounted() {
    this.isLoading = true;
    this.totalCount = this.totalCountCalc();
    Cart.getAgeGroups().then(json => {
      let leave_items = {};
      this.age_groups = Object.keys(json).map((key) => {
        leave_items[json[key].uuid] = true;
         this.$store.commit('setFamilyTerm', {
            key: json[key].uuid,
            value: key
         })
        return {key: key, title: json[key].title, uuid: json[key].uuid};
      })
      Object.keys(this.$store.state.family).forEach((key) => {
        if (!leave_items[key]) {
          this.$store.commit('deleteFamilyKey', key);
        }
      })
      this.isLoading = false;
    }).catch(()=>{
      this.isLoading = false;
    });
  },
  computed: {
    family() {
      return this.$store.state.family;
    }
  },
  components: {
    IntegerMinusPlus,
    Loading
  },
  data () {
    return {
      isLoading: false,
      age_groups: [],
      totalCount: 0,
    }
  },
  methods: {
    updateFamily(key, value) {
      this.$store.commit('setFamily', {
        key,
        value
      });
      this.totalCount = this.totalCountCalc();
    },
    totalCountCalc() {
      let count = 0;
      Object.keys(this.$store.state.family).forEach(element => {
        count = count + this.$store.state.family[element].count
      });
      return count;
    }
  }
}
</script>
