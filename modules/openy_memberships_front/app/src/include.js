import Vue from 'vue'
import App from './App.vue'
import Vuex from 'vuex';
import VueRouter from 'vue-router'
import VuexPersist from 'vuex-persist'
import numeral from 'numeral';
import numFormat from 'vue-filter-number-format';
import Version from './helpers/VersionControl';
Vue.filter('numFormat', numFormat(numeral));
Vue.use(VueRouter)
Vue.use(Vuex);

export default {
  init(storeData, routes) {
    Version.init();
    const vuexPersist = new VuexPersist({
      key: 'memberships',
      storage: window.localStorage
    })
    storeData = {
      ...storeData,
      plugins: [vuexPersist.plugin]
    }
    const router = new VueRouter({
      mode: 'hash',
      base: process.env.BASE_URL,
      routes
    })
    const store = new Vuex.Store(storeData)

    new Vue({
      store,
      router,
      render: h => h(App)
    }).$mount('#app')
  }
}
