export default {
  setSteps(state, steps) {
    state.steps = steps
  },
  setStep(state, step) {
    state.step = step
  },
  setLocation(state, location) {
    state.location = location
  },
  setFamily(state, family) {
    state.family = family
  },
  setProduct(state, product) {
    state.product = product
  },
  setItem(state, item) {
    state[item.key] = item.value
  },
  clearStorage(state) {
    state.family = {
      adults: 0,
      youth: 0,
      seniors: 0
    }
    state.location = null
    // state.product = null
    state.keepCart = false
    state.members = []
    state.income = 0
    state.billing = {}
  }
}