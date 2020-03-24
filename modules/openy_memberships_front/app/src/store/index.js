import mutations from '../mutations'
const defaultSteps = ['BranchSelectorHome', 'Family', 'Results', 'DiscountFinder', 'Summary']
export default {
    state: {
      step: 0,
      steps: window.drupalSettings.openy_memberships_front && window.drupalSettings.openy_memberships_front.steps ? window.drupalSettings.openy_memberships_front.steps : defaultSteps,
      location: null,
      family: {
        adults: 0,
        youth: 0,
        seniors: 0
      },
      product: null,
    },
    mutations
}