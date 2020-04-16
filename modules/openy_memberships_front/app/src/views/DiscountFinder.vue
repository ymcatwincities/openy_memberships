<template>
  <section class="app-container">
    <loading :active.sync="isLoading"></loading>
    <div class="container">
      <div class>
        <div class>
          <h1 class="title">Adjustments</h1>
        </div>
      </div>
      <div class="adjustments">
        <div class="discounts">
          <h2>Discounts</h2>
          <h3>Income</h3>
          <p>You may be eligible for a Scholarship discount depending on your income level.</p>
          <div v-if="discounts && discounts.income" class="annual-income">
            <div class="income-wrapper">
              <div class="eligible">Eligible</div>
              <div class="price">
                - ${{-discounts.income.amount | numFormat('0.00')}} / mo
              </div>
              <div class="btn-remove"><button class="remove-income" @click="removeIncome">×</button></div>
            </div>
          </div>
          <div v-else class="annual-income">
            <label>Annual Income (Household)</label>
            <input v-model="income" />
            <button @click="checkDiscounts(true)">Check</button>
          </div>
          <div :key="index" v-for="(member, index) in members">
            <h3>{{family_members[index] && family_members[index].attributes.field_first_name}}</h3>
            <div class="discount">
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
            <div class="discount">
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
                      <h3>{{addon.attributes.title}} (+ {{addon.attributes.price.formatted}} / mo )</h3>
                      <p v-html="addon.attributes.field_om_addon_description && addon.attributes.field_om_addon_description.processed"></p>
                    </div>
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
      this.removeIncome();
      this.$store.commit('setItem', { 
        key: 'members',
        value: {},
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
      members: this.$store.state.members || {},
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
      return window.jQuery.ajax({
        url: "/jsonapi/profile/family_members",
        contentType: "application/vnd.api+json",
        type: "POST",
        dataType: "json",
        headers: {
          "X-CSRF-Token": this.token
        },
        data: JSON.stringify({
          data: {
            type: "profile--family_members",
            attributes: {
              field_first_name: member.name
            }
          }
        })
      });
    },
    createOrder() {
      let variant = this.$store.state.product.variant;
      let variant_entity = this.$store.state.product.variations[variant];
      return window.jQuery
        .ajax({
          url: "/cart/add?_format=json",
          contentType: "application/json",
          dataType: "json",
          type: "POST",
          headers: {
            "X-CSRF-Token": this.token
          },
          data: JSON.stringify([
            {
              purchased_entity_type: "commerce_product_variation",
              purchased_entity_id: variant_entity.id,
              quantity: "1"
            }
          ])
        })
        .then(() => {
          return this.getCart();
        })
        .then(async json => {
          let order_uuid = json[0].uuid;
          //let order_id = json[0].id;
          let family = this.$store.state.family;
          let adults = family.adults;
          let youth = family.youth;
          let seniors = family.seniors;
          let members = 0;
          let family_members = [];
          for (let i = 1; i <= adults; i++) {
            members++;
            await this.createMember({
              name: "Member " + members,
              type: "adults"
            }).then(member => {
              family_members.push({
                type: member.data.type,
                id: member.data.id
              });
              this.family_members.push(member.data);
            });
          }
          for (let i = 1; i <= youth; i++) {
            members++;
            await this.createMember({
              name: "Member " + members,
              type: "youth"
            }).then(member => {
              family_members.push({
                type: member.data.type,
                id: member.data.id
              });
              this.family_members.push(member.data);
            });
          }
          for (let i = 1; i <= seniors; i++) {
            members++;
            await this.createMember({
              name: "Member " + members,
              type: "seniors"
            }).then(member => {
              family_members.push({
                type: member.data.type,
                id: member.data.id
              });
              this.family_members.push(member.data);
            });
          }

          return window.jQuery.ajax({
            url: "/jsonapi/commerce_order/membership_order/" + order_uuid,
            contentType: "application/vnd.api+json",
            type: "PATCH",
            dataType: "json",
            headers: {
              "X-CSRF-Token": this.token
            },
            data: JSON.stringify({
              data: {
                type: "commerce_order--membership_order",
                id: order_uuid,
                relationships: {
                  field_family: {
                    data: family_members
                  }
                }
              }
            })
          });
        });
    },
    restoreMembers() {
      return this.getCart().then(json => {
        let order_uuid = json[0].uuid;
        return window.jQuery.ajax({
          url: "/jsonapi/commerce_order/membership_order/" + order_uuid + "?include=field_family",
          contentType: "application/vnd.api+json",
          type: "GET",
          dataType: "json",
          headers: {
            "X-CSRF-Token": this.token
          }
        });
      }).then(json => {
        this.family_members = [];
        json.included.forEach((member) => {
          this.family_members.push(member);
        });
        return this.family_members;
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
        this.member_promotions = data.member_promotions;
        
        this.isLoading = false;
      }).catch(() => {
        this.isLoading = false;
      });
    },
    getToken() {
      return window.jQuery
        .ajax({
          url: "/session/token"
        })
        .then(token => {
          this.token = token;
        });
    },
    getUserInfo() {
      return this.getToken().then(() => {
        return window.jQuery.ajax({
          url: "/jsonapi",
          dataType: "json",
          headers: {
            "X-CSRF-Token": this.token
          }
        });
      });
    },
    getOrder(uuid) {
      return window.jQuery.ajax({
        url: "/jsonapi/commerce_order/membership_order/" + uuid,
        dataType: "json",
        headers: {
          "X-CSRF-Token": this.token
        }
      });
    },
    getOrders() {
      return window.jQuery.ajax({
        url:
          "/cart?_format=json",
        dataType: "json"
      }).then((json) => {
        this.total_price = json[0].total_price.number;
        return json
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
                break;
            }
          })
        });
        return json;
      });
    },
    removeMembers(members) {
      let membersPromise = members.map(member => {
        return window.jQuery.ajax({
          url: "/jsonapi/profile/family_members/" + member.id,
          dataType: "json",
          type: "DELETE",
          headers: {
            "X-CSRF-Token": this.token
          }
        });
      });
      return Promise.all(membersPromise);
    },
    removeOrder(order) {
      let id = order.attributes.drupal_internal__order_id;
      return window.jQuery.ajax({
        url: "/cart/" + id + "/items",
        dataType: "json",
        type: "DELETE",
        headers: {
          "X-CSRF-Token": this.token
        }
      });
    },
    getCart() {
      return window.jQuery.ajax({
        url: "/cart?_format=json",
        dataType: "json",
        type: "GET",
        headers: {
          "X-CSRF-Token": this.token
        }
      });
    },
    getAddons() {
      return window.jQuery.ajax({
        url: "/jsonapi/commerce_product/membership/" + this.$store.state.product.uuid + "?include=field_om_addons",
        dataType: "json",
        type: "GET",
        headers: {
          "X-CSRF-Token": this.token
        }
      }).then((json) => {
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
        return this.addons;
      });
    },
    addAddon(addon) {
      this.isLoading = true;
      return window.jQuery.ajax({
        url: "/cart/add?_format=json",
        contentType: "application/json",
        dataType: "json",
        type: "POST",
        headers: {
          "X-CSRF-Token": this.token
        },
        data: JSON.stringify([
          {
            purchased_entity_type: "commerce_addon",
            purchased_entity_id: addon.attributes.drupal_internal__addon_id,
            quantity: "1",
            combine: false
          }
        ])
      }).then(() => {
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
        return window.jQuery.ajax({
          url: "/cart/" + addon.order_id + "/items/" + addon.order_item_id + "?_format=json",
          dataType: "json",
          type: "DELETE",
          headers: {
            "X-CSRF-Token": this.token
          }
        }).then(() => {
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
