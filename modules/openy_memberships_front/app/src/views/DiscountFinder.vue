<template>
  <section class="app-container">
    <div class="container">
      <div class="">
        <div class="">
          <h1 class="title">
            Adjustments
          </h1>
        </div>
      </div>
      <div class="adjustments">  
        <div class="discounts">
          <h2>Discounts</h2>
          <h3>Income</h3>
          <p>You may be eligible for a Scholarship discount depending on your income level.</p>
          <div class="annual-income">
            <label>Annual Income (Household)</label>
            <input  /> <button>Check</button>
          </div>
          <div class="discount">
            <div class="checkbox">
              <label class="container-checkbox">
                <input type="checkbox" checked="checked">
                <span class="checkmark"></span>
              </label>
            </div>
            <div class="description">
              <h3>Health Insurance (- $10 / mo.)*</h3>
              <p>Has health insurance with one of the following providers:</p>
            </div>
          </div>

        </div>
        <div class="addons">
          <h2>Add-Ons</h2>
          <h3>Members</h3>
          <p>One Adult (80-54 yrs.) and all Youth (0-17yrs) are covered by the base Household membership:</p>
          <a>Add Adult ($10 /mo.)</a> <a>Add Senior ($5 /mo.)</a>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  mounted() {
    
  },
  data () {
    return {
      isLoading: false,
      discounts: [],
      addons: []
    }
  }
}
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
      border: 1px solid #F2F2F2;
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
        background-color: #0060AF;
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

