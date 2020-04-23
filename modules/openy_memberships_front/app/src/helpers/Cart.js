import User from './User';
class Cart {
  getOrder = (uuid) => {
    return User.getToken().then(token => {
      return window.jQuery.ajax({
        url: "/jsonapi/commerce_order/membership_order/" + uuid,
        dataType: "json",
        headers: {
          "X-CSRF-Token": token
        }
      });
    });
  }
  getCart = () => {
    return User.getToken().then((token => {
      return window.jQuery.ajax({
        url: "/cart?_format=json",
        dataType: "json",
        type: "GET",
        headers: {
          "X-CSRF-Token": token
        }
      });
    }))
  }
  setBilling = (order_id, billing) => {
    return User.getToken().then(token => {
      return window.jQuery.ajax({
        url: "/memberships/api/billing/" + order_id,
        contentType: "application/vnd.api+json",
        type: "POST",
        dataType: "json",
        headers: {
          "X-CSRF-Token": token
        },
        data: JSON.stringify({
          field_email: billing.email, 
          field_phone: billing.phone,
          address: {
            country_code: 'US',
            address_line1: '',
            locality: '',
            administrative_area: '',
            postal_code: '',
            given_name: billing.firstName,
            family_name: billing.lastName,
          }
        })
      });
    })
  }
  createMember = (member) => {
    return User.getToken().then(token => {
      return window.jQuery.ajax({
        url: "/jsonapi/profile/family_members",
        contentType: "application/vnd.api+json",
        type: "POST",
        dataType: "json",
        headers: {
          "X-CSRF-Token": token
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
    });
  }
  createOrder = (variant_id) => {
    return User.getToken().then(token => {
      return window.jQuery
        .ajax({
          url: "/cart/add?_format=json",
          contentType: "application/json",
          dataType: "json",
          type: "POST",
          headers: {
            "X-CSRF-Token": token
          },
          data: JSON.stringify([
            {
              purchased_entity_type: "commerce_product_variation",
              purchased_entity_id: variant_id,
              quantity: "1"
            }
          ])
        })
    })
  }
  removeMembers = (members) => {
    return User.getToken().then(token => {
      let membersPromise = members.map(member => {
        return window.jQuery.ajax({
          url: "/jsonapi/profile/family_members/" + member.id,
          dataType: "json",
          type: "DELETE",
          headers: {
            "X-CSRF-Token": token
          }
        });
      });
      return Promise.all(membersPromise);
    });
  }
  removeOrder = (order) => {
    return User.getToken().then(token => {
      let id = order.attributes.drupal_internal__order_id;
      return window.jQuery.ajax({
        url: "/cart/" + id + "/items",
        dataType: "json",
        type: "DELETE",
        headers: {
          "X-CSRF-Token": token
        }
      });
    })
  }
  getAddons = (uuid) => {
    return User.getToken().then(token => {
      return window.jQuery.ajax({
        url: "/jsonapi/commerce_product/membership/" + uuid + "?include=field_om_addons",
        dataType: "json",
        type: "GET",
        headers: {
          "X-CSRF-Token": token
        }
      })
    });
  }
  addAddon = (addon) => {
    return User.getToken().then(token => {
      return window.jQuery.ajax({
        url: "/cart/add?_format=json",
        contentType: "application/json",
        dataType: "json",
        type: "POST",
        headers: {
          "X-CSRF-Token": token
        },
        data: JSON.stringify([
          {
            purchased_entity_type: "commerce_addon",
            purchased_entity_id: addon.attributes.drupal_internal__addon_id,
            quantity: "1",
            combine: false
          }
        ])
      })
    });
  }
  removeAddon = (addon) => {
    return User.getToken().then((token) => {
      return window.jQuery.ajax({
        url: "/cart/" + addon.order_id + "/items/" + addon.order_item_id + "?_format=json",
        dataType: "json",
        type: "DELETE",
        headers: {
          "X-CSRF-Token": token
        }
      })
    })
  }
  getMembers = (order_uuid) => {
    return User.getToken().then((token) => {
      return window.jQuery.ajax({
        url: "/jsonapi/commerce_order/membership_order/" + order_uuid + "?include=field_family",
        contentType: "application/vnd.api+json",
        type: "GET",
        dataType: "json",
        headers: {
          "X-CSRF-Token": token
        }
      })
    });
  }
  setMembers = (order_uuid, family_members) => {
    return User.getToken().then((token) => {
      return window.jQuery.ajax({
        url: "/jsonapi/commerce_order/membership_order/" + order_uuid,
        contentType: "application/vnd.api+json",
        type: "PATCH",
        dataType: "json",
        headers: {
          "X-CSRF-Token": token
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
  }
  updateOrderStatus = (order_uuid) => {
    return User.getToken().then((token) => {
      return window.jQuery.ajax({
        url: "/jsonapi/commerce_order/membership_order/" + order_uuid,
        contentType: "application/vnd.api+json",
        type: "PATCH",
        dataType: "json",
        headers: {
          "X-CSRF-Token": token
        },
        data: JSON.stringify({
          data: {
            type: "commerce_order--membership_order",
            id: order_uuid,
            attributes: {
              cart: false
            }
          }
        })
      });
    });
  }
}
export default new Cart;