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
            {{discounts.income.amount}} {{discounts.income.currency}}
          </div>
          <div v-else class="annual-income">
            <label>Annual Income (Household)</label>
            <input v-model="income" />
            <button @click="checkDiscounts">Check</button>
          </div>
          <div :key="index" v-for="(member, index) in members">
            <h3>{{family_members[index].attributes.field_first_name}}</h3>
            <div class="discount">
              <div class="checkbox">
                <label class="container-checkbox">
                  <input @change="checkDiscounts" type="checkbox" v-model="members[index][0]" />
                  <span class="checkmark"></span>
                </label>
              </div>
              <div class="description">
                <h3>Health Insurance (- $10 / mo.)*</h3>
                <p>Has health insurance with one of the following providers:</p>
              </div>
            </div>
            <div class="discount">
              <div class="checkbox">
                <label class="container-checkbox">
                  <input @change="checkDiscounts" type="checkbox" v-model="members[index][1]" />
                  <span class="checkmark"></span>
                </label>
              </div>
              <div class="description">
                <h3>Military Service (- $10 / mo.)*</h3>
                <p>Has health insurance with one of the following providers:</p>
              </div>
            </div>
          </div>
        </div>
        <div class="addons">
          <h2>Add-Ons</h2>
          <h3>Members</h3>
          <p>One Adult (80-54 yrs.) and all Youth (0-17yrs) are covered by the base Household membership:</p>
          <a>Add Adult ($10 /mo.)</a>
          <a>Add Senior ($5 /mo.)</a>
        </div>
      </div>
    </div>
    <div class="navigation" v-if="parseFloat(this.total_price) != parseFloat(this.subtotal_price)">
      <div class="container">
        Discounts & Add-Ons: {{ parseFloat(this.total_price) > parseFloat(this.subtotal_price) ? '+' : '-'}}{{ parseFloat(this.subtotal_price) - parseFloat(this.total_price) }} USD<button class="btn btn-next" @click="$emit('go-next')">Next</button>
      </div>
    </div>
  </section>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
  mounted() {
    this.isLoading = true;
    this.getUserInfo()
      .then(json => {
        let user_id = json.meta ? json.meta.links.me.meta.id : null;
        return this.getOrders(user_id);
      })
      .then(json => {
        if (json.data.length) {
          let removes = json.data.map(order => {
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
          return Promise.all(removes)
        }
      })
      .then(() => {
        return this.createOrder().then(json => {
          return this.setMembers(json.data);
        }).then(() => {
          return this.checkDiscounts();
        });
        
      }).then(() => {
        this.isLoading = false;
      }).catch(() => {
        this.isLoading = false;
      });
  },
  data() {
    return {
      isLoading: false,
      discounts: null,
      addons: [],
      token: null,
      income: 0,
      members: {},
      family_members: [],
      total_price: 0,
      subtotal_price: 0,
    };
  },
  methods: {
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
    setMembers(data) {
      //this.family_members = data.relationships.field_family.data;
      this.members = data.relationships.field_family.data.map(() => {
        return [0, 0]
      })
    },
    checkDiscounts() {
      let checkboxes = this.members.reduce((total, currentValue) => {
        if (total != '') {
          total += '_';
        }
        return  total + currentValue.map(el => +el).join(',');
      }, '');
      let income = parseInt(this.income);
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
        this.total_price = data.total_price;
        this.subtotal_price = data.subtotal_price;
      });
    },
    buildDiscounts() {

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
    getOrders(id) {
      return window.jQuery.ajax({
        url:
          "/jsonapi/commerce_order/membership_order" +
          (id ? "?filter[uid.id]=" + id : ""),
        dataType: "json"
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
    }
  },
  components: {
    Loading,
  }
};
</script>
<style lang="scss">
.container-checkbox {
  display: block;
  position: relative;
  padding-left: 35px;
  margin-bottom: 12px;
  cursor: pointer;
  font-size: 22px;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
  }
  &:hover input ~ .checkmark {
    background-color: #eee;
  }
  input:checked ~ .checkmark {
    background-color: #006600;
    border: 2px solid #006600;
  }
  .checkmark::after {
    left: 4px;
    top: -1px;
    width: 7px;
    height: 13px;
    border: solid white;
    border-width: 0 2px 2px 0;
    -webkit-transform: rotate(45deg);
    -ms-transform: rotate(45deg);
    transform: rotate(45deg);
  }
  input:checked ~ .checkmark:after {
    display: block;
  }
}
.checkmark {
  position: absolute;
  top: 0;
  left: 0;
  height: 18px;
  width: 18px;
  border-radius: 2px;
  background-color: #fff;
  border: 2px solid #636466;
  &::after {
    content: "";
    position: absolute;
    display: none;
  }
}
</style>
<style lang="scss" scoped>
.discount {
  display: flex;
  .checkbox {
    margin-top: 20px;
    width: 50px;
  }
}
.adjustments {
  display: flex;
  flex-wrap: wrap;
  .discounts {
    width: 50%;
    padding: 10px;
    .annual-income {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      padding: 15px;
      border: 1px solid #f2f2f2;
      label {
        width: 100%;
      }
      input {
        padding: 10px;
        margin-right: 10px;
      }
      button {
        border: none;
        border-radius: 5px;
        background-color: #0060af;
        color: #fff;
        text-transform: uppercase;
        padding: 10px;
      }
    }
  }
  .addons {
    padding: 10px;
    width: 50%;
  }
}
</style>

