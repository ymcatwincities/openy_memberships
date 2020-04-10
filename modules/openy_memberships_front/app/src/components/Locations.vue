<template>
  <div class="locations">

      <location @input="updateLocation" :value="loc" :val="location.value" :key="key" v-for="(location, key) in locations" :name="location.name" :address="location.address" class="location-wrapper" />
  </div>
</template>
<script>
import Location from './Location';
import { mapState } from 'vuex';

export default {
  props: ['locations'],
  computed: mapState({
    loc: state => state.location,
  }),
  components: {
    Location
  },
  data() {
    return {
      value: null
    }
  },
  methods: {
    updateLocation(e) {
      this.value = e;
      this.$store.commit('setLocation', this.value);
    }
  }
}
</script>
