<template>
  <div>
    <div :class="'location ' + (val == value ? 'active' : '')" @click="updateValue">
      <div class="radio-wrap">
        <div :class="'radio ' + (val == value ? 'active' : '')"></div>
      </div>
      <div class="content">
        <div class="name" v-text="name" />
        <div class="address" v-text="address" />
      </div>
    </div>
  </div>
</template>
<script>
export default {
  props: ['name', 'address', 'val', 'value'],
  methods: {
    updateValue() {
      this.$emit('input', this.val)
    }
  }
}
</script>

