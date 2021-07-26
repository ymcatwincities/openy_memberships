<template>
  <div id="app" :class="'membership-app ' + $router.currentRoute.name">
    <router-view @go-next="goNext" />

  </div>
</template>

<script>
import VueCookies from 'vue-cookies';

export default {
  name: 'App',
  computed: {
    step() {
      return this.$store.state.step
    }
  },
  mounted() {
    let stepsConfig = window.drupalSettings.openy_memberships_front && window.drupalSettings.openy_memberships_front.steps ? window.drupalSettings.openy_memberships_front.steps : null;
    if (stepsConfig) {
      this.$store.commit('setSteps', stepsConfig);
    }
    let step = this.$store.state.step;
    let steps = this.$store.state.steps;
    if (steps[step] && this.$route.name != steps[step]) {
      //this.$router.replace({ name:  steps[step] })
    }
    if (!this.$route.name) {
      this.$router.replace({ name:  steps[0] })
    }
    // Check if user has selected Home Branch and set location.
    if (VueCookies.get('home_branch') !== null) {
      this.$store.commit('setLocation', VueCookies.get('home_branch').id);
      // Move to next step if we still on initial step.
      if (step == 0 && VueCookies.get('home_branch').id !== null) {
        this.$store.commit('setStep', 1);
      }
    }
  },
  data: () => ({
    //
  }),
  methods: {
    goNext() {
      let currentStep = this.$store.state.steps.indexOf(this.$route.name);
      if(currentStep !== -1 && currentStep + 1 < this.$store.state.steps.length) {
        this.$store.commit('setStep', currentStep + 1)
      }
    }
  },
  watch: {
    '$route' (to) {
      let step = this.$store.state.step;
      let steps = this.$store.state.steps;
      let currentStep = this.$store.state.steps.indexOf(to.name);
      // Check if user has selected Home Branch, and avoid redirect to BranchSelector.
      if (VueCookies.get('home_branch') !== null && this.$store.state.location && to.name == 'BranchSelectorHome') {
        this.$store.commit('setStep', 1);
        this.$router.push({ name:  steps[1] });
      }
      else if (currentStep != -1 && step != currentStep) {
        this.$store.commit('setStep', currentStep)
      }
    },
    step() {
      let step = this.$store.state.step;
      let steps = this.$store.state.steps;
      if (steps[step] && this.$route.name != steps[step]) {
        this.$router.push({ name:  steps[step] })
      }
    }
  }
};
</script>
