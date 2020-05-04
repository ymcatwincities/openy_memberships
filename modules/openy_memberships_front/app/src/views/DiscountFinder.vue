<template>
  <section class="app-container">
    <loading :active.sync="isLoading"></loading>
    <h1 class="title">Adjustments</h1>
    <div class="adjustments">
        <div class="discounts">
          <h2>Discounts</h2>
          <h3>Income</h3>
          <p>You may be eligible for a Scholarship discount depending on your income level.</p>
          <div v-if="discounts && discounts.income" class="annual-income">
            <label>
              <strong>Annual Income (Household)</strong>
            </label>
            <div class="income-wrapper">
              <div class="eligible">Eligible</div>
              <div class="price">
                - ${{-discounts.income.amount | numFormat('0.00')}} / mo
              </div>
              <div class="btn-remove"><button class="remove-income" @click="removeIncome">×</button></div>
            </div>
          </div>
          <div v-else class="annual-income">
            <label>
              <strong>Annual Income (Household)</strong>
            </label>
            <input v-model="income" placeholder="$" />
            <button @click="checkDiscounts(true)">Check</button>
          </div>
          <div :key="index" v-for="(member, index) in members" class="discount-options">
            <h3>{{family_members[index] && family_members[index].attributes.field_first_name}}</h3>
            <div v-if="member_promotions.health_insurance" class="discount">
              <div class="checkbox">
                <label class="container-checkbox">
                  <input @change="checkDiscounts(true)" type="checkbox" v-model="members[index][0]" />
                  <span class="checkmark"></span>
                </label>
              </div>
              <div class="description">
                <h3>{{member_promotions.health_insurance && member_promotions.health_insurance.label}} (- ${{member_promotions.health_insurance && member_promotions.health_insurance.amount}} / mo.)*</h3>
                <p v-html="member_promotions.health_insurance && member_promotions.health_insurance.description"></p>
              </div>
            </div>
            <div v-if="member_promotions.military_service" class="discount">
              <div class="checkbox">
                <label class="container-checkbox">
                  <input @change="checkDiscounts(true)" type="checkbox" v-model="members[index][1]" />
                  <span class="checkmark"></span>
                </label>
              </div>
              <div class="description">
                <h3>{{member_promotions.military_service && member_promotions.military_service.label}} (- ${{member_promotions.military_service && member_promotions.military_service.amount}} / mo.)*</h3>
                <p v-html="member_promotions.military_service && member_promotions.military_service.description"></p>
              </div>
            </div>
          </div>
        </div>
        <div class="addons">
          <h2>Add-Ons</h2>
          <div v-if="addons.members" class="members">
            <h3>Members</h3>
            <p>One Adult (80-54 yrs.) and all Youth (0-17yrs) are covered by the base Household membership:</p>
            <div class="addons-members">
              <div :key="addon.addon_id" v-for="addon in member_addons_in_cart">
                <div class="addon-wrapper">
                  <div class="title">+1 {{addon.title}}</div>
                  <div class="price">
                    +{{addon.price.formatted}} / mo
                  </div>
                  <div class="btn-remove"><button class="remove-income" @click="removeAddon(addon)">×</button></div>
                </div>
              </div>
            </div>
            <button class="add-addon" @click="addAddon(addon)" :key="addon.id" v-for="addon in addons.members">Add {{addon.attributes.title}} ({{addon.attributes.price.formatted}} / {{addon.attributes.field_om_frequency}}.)</button>
          </div>
          <div v-if="addons.benefits_packages" class="benefits">
            <h3>Benefits Packages</h3>
            <p></p>
            <div class="addons-benefits">
              <div :key="addon.addon_id" v-for="addon in addons.benefits_packages">
                <div class="addon-wrapper">
                  <div class="checkbox">
                    <label class="container-checkbox">
                      <input @click="updateAddon(addon, $event)" type="checkbox" :checked="inArray('benefits_packages_addons_in_cart', addon.attributes.drupal_internal__addon_id)" />
                      <span class="checkmark"></span>
                    </label>
                  </div>
                  <div class="description">
                    <h3>{{addon.attributes.title}} (+ {{addon.attributes.price.formatted}} / mo )</h3>
                    <p v-html="addon.attributes.field_om_addon_description && addon.attributes.field_om_addon_description.processed"></p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div v-if="addons.benefits" class="benefits">
            <h3>Benefits</h3>
              <p></p>
              <div class="addons-benefits">
                <div :key="addon.addon_id" v-for="addon in addons.benefits">
                  <div class="addon-wrapper">
                    <div class="checkbox">
                      <label class="container-checkbox">
                        <input @click="updateAddon(addon, $event)" type="checkbox" :checked="inArray('benefits_addons_in_cart', addon.attributes.drupal_internal__addon_id)" />
                        <span class="checkmark"></span>
                      </label>
                    </div>
                    <div class="description">
                      <h3 class="regular">{{addon.attributes.title}} (+ {{addon.attributes.price.formatted}} / mo )</h3>
                      <p v-html="addon.attributes.field_om_addon_description && addon.attributes.field_om_addon_description.processed"></p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
      </div>
    <div class="navigation">
      <div class="container">
        Discounts & Add-Ons: <b>{{discount_addons}} /mo.</b> <button class="btn btn-next" @click="$emit('go-next')">Next</button>
      </div>
    </div>
  </section>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import User from '../helpers/User';
import Cart from '../helpers/Cart';

export default {
  computed: {
    discount_addons() {
      let total_price = parseFloat(this.total_price);
      let product = this.$store.state.product.variations[this.$store.state.product.variant];

      let subtotal_price = parseFloat(product.price);
      let count = total_price - subtotal_price;
      if (count > 0) {
        return '+ $' + count;
      }
      if (count == 0) {
        return '$' + count;
      }
      return '- $' + -count;
    }
  },
  mounted() {
    this.isLoading = true;
    if (this.$store.state.keepCart == false) {
      this.income = 0;
      this.$store.commit('setItem', {key: 'income', value: 0});
      this.members = [];
      this.$store.commit('setItem', {
        key: 'members',
        value: [],
      });
    }
    this.getUserInfo()
      .then(() => {
        // let user_id = json.meta ? json.meta.links.me.meta.id : null;
        this.getAddons();
        return this.getOrders();
      })
      .then(json => {
        if (json.length) {
          let orders = json.map(order => {
            return this.getOrder(order.uuid);
          })
          return Promise.all(orders)
        }
        return json;
      })
      .then(json => {
        if (json.length && this.$store.state.keepCart == false) {
          let removes = json.map(json => {
            let order = json.data;
            let members = order.relationships.field_family.data;
            if (members && members.length) {
              return this.removeMembers(members)
                .then(() => {
                  return this.removeOrder(order);
                })
                .catch(() => {
                  return this.removeOrder(order);
                });
            }
            return this.removeOrder(order);
          });
          return Promise.all(removes);
        }
        return json;
      })
      .then(() => {
        if (this.$store.state.keepCart == false) {
          return this.createOrder().then(json => {
            this.getOrders();
            return this.setMembers(json.data);
          }).then(() => {
            return this.checkDiscounts();
          });
        }
        else {
          return this.restoreMembers().then(() => {
            return this.checkDiscounts();
          });
        }
      }).then(() => {
        this.isLoading = false;
        this.$store.commit('setItem', {
          key: 'keepCart',
          value: true,
        })
      }).catch(() => {
        this.isLoading = false;
        this.$store.commit('setItem', {
          key: 'keepCart',
          value: true,
        })
      });
  },
  data() {
    return {
      isLoading: false,
      discounts: null,
      addons: {},
      token: null,
      income: this.$store.state.income || 0,
      members: this.$store.state.members || [],
      family_members: [],
      total_price: 0,
      subtotal_price: 0,
      member_promotions: {},
      addons_in_cart: [],
      benefits_addons_in_cart: [],
      benefits_packages_addons_in_cart: [],
      member_addons_in_cart: [],
      cart_id: null
    };
  },
  methods: {
    removeIncome() {
      this.income = 0;
      this.$store.commit('setItem', {key: 'income', value: this.income});
      this.checkDiscounts(true);
    },
    createMember(member) {
      return Cart.createMember(member);
    },
    createOrder() {
      let variant = this.$store.state.product.variant;
      let variant_entity = this.$store.state.product.variations[variant];
      return Cart.createOrder(variant_entity.id)
        .then(() => {
          return Cart.getCart();
        })
        .then(async json => {
          let order_uuid = json[0].uuid;
          //let order_id = json[0].id;
          let family = this.$store.state.family;
          let members = 0;
          let family_members = [];

          for (let type_uuid in family) {
            let count = family[type_uuid].count;
            for (let i = 1; i <= count; i++) {
              members++;
              await this.createMember({
                name: "Member " + members,
                type_uuid: type_uuid
              }).then(member => {
                family_members.push({
                  type: member.data.type,
                  id: member.data.id
                });
                this.family_members.push(member.data);
              });
            }
          }

          return Cart.setMembers(order_uuid, family_members);
        });
    },
    restoreMembers() {
      return Cart.getCart().then(json => {
        let order_uuid = json[0].uuid;
        return Cart.getMembers(order_uuid).then(json => {
          this.family_members = [];
          if(this.$store.state.members.length == 0) {
            this.setMembers(json.data);
          }
          json.included.forEach((member) => {
            this.family_members.push(member);
          });
          return this.family_members;
        })
      });
    },
    setMembers(data) {
      //this.family_members = data.relationships.field_family.data;
      this.members = data.relationships.field_family.data.map(() => {
        return [0, 0]
      });
      this.$store.commit('setItem', {key: 'members', value: this.members});
    },
    checkDiscounts(show_loader) {
      let checkboxes = this.members.reduce((total, currentValue) => {
        if (total != '') {
          total += '_';
        }
        return  total + currentValue.map(el => +el).join(',');
      }, '');
      let income = parseInt(this.income);
      this.$store.commit('setItem', {key: 'income', value: income});
      this.$store.commit('setItem', {key: 'members', value: this.members});
      if (show_loader) {
        this.isLoading = true;
      }
      return window.jQuery.ajax({
        url: "/memberships/check/discounts/" + income + '/' + checkboxes,
        type: "GET",
        dataType: "json",
        headers: {
          "X-CSRF-Token": this.token
        },
        data: {}
      }).then((data) => {
        this.discounts = data.discounts;
        this.total_price = data.total_price;
        this.subtotal_price = data.subtotal_price;
        this.member_promotions = data.member_promotions ? data.member_promotions : {};

        this.isLoading = false;
      }).catch(() => {
        this.isLoading = false;
      });
    },
    getToken() {
      return User.getToken()
        .then(token => {
          this.token = token;
        });
    },
    getUserInfo() {
      return User.getUserInfo();
    },
    getOrder(uuid) {
      return Cart.getOrder(uuid);
    },
    getOrders() {
      return Cart.getCart().then((json) => {
        this.total_price = json[0].total_price.number;
        return json
      }).then(json => {
        this.updateItemsInCart(json);
        return json;
      }).catch(() => {
        return [];
      });
    },
    updateItemsInCart(json) {
      this.member_addons_in_cart = [];
      this.benefits_packages_addons_in_cart = [];
      this.benefits_addons_in_cart = [];
      json.forEach(order => {
        order.order_items.forEach(item => {
          if(!item || !item.purchased_entity) {
            return 
          }
          switch(item.purchased_entity.type) {
            case "membership_addon":
              switch (item.purchased_entity.field_om_addon_type) {
                case "benefits":
                  this.benefits_addons_in_cart.push({
                    ...item.purchased_entity,
                    order_item_id: item.order_item_id,
                    uuid: item.uuid,
                    order_id: item.order_id
                  })
                  break;
                case "benefits_packages":
                  this.benefits_packages_addons_in_cart.push({
                    ...item.purchased_entity,
                    order_item_id: item.order_item_id,
                    uuid: item.uuid,
                    order_id: item.order_id
                  })
                  break;
                case "members":
                  this.member_addons_in_cart.push({
                    ...item.purchased_entity,
                    order_item_id: item.order_item_id,
                    uuid: item.uuid,
                    order_id: item.order_id
                  })
                  break;
              }
              break;
          }
        })
      });
    },
    removeMembers(members) {
      return Cart.removeMembers(members);
    },
    removeOrder(order) {
      return Cart.removeOrder(order);
    },
    getCart() {
      return Cart.getCart()
    },
    getAddons() {
      return Cart.getAddons(this.$store.state.product.uuid).then((json) => {
        if (json.included) {
          json.included.forEach((addon) => {
            if (addon.type != "commerce_addon--membership_addon") {
              return;
            }
            let type = addon.attributes.field_om_addon_type;
            if (!this.addons[type]) {
              this.addons[type] = [];
            }
            this.addons[type].push(addon);
          })
        }
        return this.addons;
      });
    },
    addAddon(addon) {
      this.isLoading = true;
      return Cart.addAddon(addon).then(() => {
        return this.getOrders();
      }).then(() => {
        this.isLoading = false;
      }).catch(() => {
        this.isLoading = false;
      })
    },
    removeAddon(addon) {
      if (addon.order_item_id) {
        this.isLoading = true;
        return Cart.removeAddon(addon).then(() => {
          return this.getOrders();
        }).then(json => {
          this.member_addons_in_cart = [];
          this.benefits_packages_addons_in_cart = [];
          this.benefits_addons_in_cart = [];
          json.forEach(order => {
            order.order_items.forEach(item => {
              switch(item.purchased_entity.type) {
                case "membership_addon":

                  switch (item.purchased_entity.field_om_addon_type) {
                    case "benefits":
                    this.benefits_addons_in_cart.push({
                      ...item.purchased_entity,
                      order_item_id: item.order_item_id,
                      uuid: item.uuid,
                      order_id: item.order_id
                    })
                    break;
                  case "benefits_packages":
                    this.benefits_packages_addons_in_cart.push({
                      ...item.purchased_entity,
                      order_item_id: item.order_item_id,
                      uuid: item.uuid,
                      order_id: item.order_id
                    })
                    break;
                  default:
                    this.member_addons_in_cart.push({
                      ...item.purchased_entity,
                      order_item_id: item.order_item_id,
                      uuid: item.uuid,
                      order_id: item.order_id
                    })
                    break;
                  }
              }
            })
          })
          this.isLoading = false;
        }).catch(() => {
          this.isLoading = false;
        })
      }
      return addon
    },
    inArray(addons, id) {
      let filtered = this[addons].filter((el) => {
        return el.addon_id == id
      });
      return filtered.length;
    },
    updateAddon(addon, event) {
      event.preventDefault();
      let filtered = this[addon.attributes.field_om_addon_type + '_addons_in_cart'].filter((el) => {
        return el.addon_id == addon.attributes.drupal_internal__addon_id
      });
      if (filtered.length) {
        filtered.forEach((el => {
          return this.removeAddon(el)
        }));
      }
      else {
        return this.addAddon(addon);
      }
    }
  },
  components: {
    Loading,
  }
};
</script>
