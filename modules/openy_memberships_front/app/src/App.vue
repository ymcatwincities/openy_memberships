<template>
  <div id="app" :class="'membership-app ' + $router.currentRoute.name">
    <router-view @go-next="goNext" />

  </div>
</template>

<script>

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
      let currentStep = this.$store.state.steps.indexOf(to.name);
      if (currentStep != -1 && step != currentStep) {
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
