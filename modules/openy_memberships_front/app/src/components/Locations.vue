<template>
  <div class="locations">
    <location @input="updateLocation" :value="loc" :val="'all'" :name="'- All branches -'" class="location-wrapper" v-if="this.allBranchesBtnEnabled"/>
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
      value: null,
      allBranchesBtnEnabled: 0
    }
  },
  methods: {
    updateLocation(e) {
      this.value = e;
      this.$store.commit('setLocation', this.value);
    }
  },
  mounted() {
    this.allBranchesBtnEnabled = window.drupalSettings.openy_memberships_front && window.drupalSettings.openy_memberships_front.all_branches_btn ? window.drupalSettings.openy_memberships_front.all_branches_btn : 0;
  }
}
</script>
