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
<style lang="scss">
  .membership-app {
    .navigation {
      border-top: 5px solid rgba(112, 112, 112, 0.2);
      text-align: right;
    }
    
    .btn {
      &.btn-next {
        padding: 15px 40px;
        background: #92278F 0% 0% no-repeat padding-box;
        border-radius: 5px;
        text-align: center;
        font: 500 24px/26px 'Cachet', Verdana, sans-serif;

        letter-spacing: 0;
        color: #FFFFFF;
        text-transform: uppercase;
        margin: 10px;
      }
    }
    h1.title {
      color: #231F20;
      margin: 15px 0px;
      font: 500 48px/72px Cachet, Verdana, sans-serif;
    }
    .description {
      margin-bottom: 30px;
      color: #231F20;
      font: Regular 14px/21px Verdana;
    }
    h3 {
      font: Medium 24px/36px Cachet, Verdana, sans-serif;
      letter-spacing: 0px;
      color: #231F20;
    }
    h2 {
      font: Medium 36px/36px Cachet, Verdana, sans-serif;
      letter-spacing: 0px;
      color: #231F20;
      padding: 10px 0px;
    }

  }
</style>