import BranchSelector from '../views/BranchSelector.vue'
import Summary from '../views/Summary.vue'
import DiscountFinder from '../views/DiscountFinder.vue'
import Family from '../views/Family.vue'
import Results from '../views/Results.vue'

export default [
  {
    path: '/branch-selector',
    name: 'BranchSelectorHome',
    component: BranchSelector
  },
  {
    path: '/discount-finder',
    name: 'DiscountFinder',
    component: DiscountFinder
  },
  {
    path: '/family',
    name: 'Family',
    component: Family
  },
  {
    path: '/summary',
    name: 'Summary',
    component: Summary
  },
  {
    path: '/results',
    name: 'Results',
    component: Results
  }
]
