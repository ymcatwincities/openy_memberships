 <template>
  <span>
    <integer-plusminus @input="$emit('input', $event)" :min="ips_min"
      :max="ips_max"
      :step="ips_step"
      :vertical="ips_vertical"
      :disabled="ips_disabled"
      v-model="ips_value">
    </integer-plusminus>
  </span>
</template>

<script>
import { IntegerPlusminus } from 'vue-integer-plusminus'

export default {
  props: ['min', 'max', 'step', 'vertical', 'disabled', 'value'],
  data() {
    return {
      'ips_min': this.min || 0,
      'ips_max': this.max || 10,
      'ips_step': this.step || 1,
      'ips_vertical': this.vertical || false,
      'ips_disabled': this.disable || false,
      'ips_value': this.value || 0
    }
  },
  components: {
    IntegerPlusminus
  }
}
</script>

