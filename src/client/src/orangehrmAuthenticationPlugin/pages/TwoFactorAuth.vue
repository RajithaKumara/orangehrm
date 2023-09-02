<template>
  <div class="orangehrm-2fa-container">
    <div class="orangehrm-card-container">
      <oxd-form :loading="isLoading" @submit-valid="onSave">
        <oxd-text tag="h6" class="orangehrm-2fa-title">
          <!--          todo:: localization-->
          Two-factor Authentication
        </oxd-text>

        <br />

        <div>
          <oxd-text type="toast-message">
            <!--          todo:: localization-->
            Enter the code from your two-factor authenticator app. If you've
            lost your device, please contact your system administrator and
            reset.
          </oxd-text>
        </div>

        <br />

        <oxd-form-row>
          <oxd-input-field
            v-model="otp"
            name="otp"
            label="Enter verification code"
            label-icon="person-lock"
          />
        </oxd-form-row>
        <oxd-form-actions>
          <oxd-button
            class="orangehrm-left-space"
            display-type="secondary"
            label="Verify"
            type="submit"
          />
        </oxd-form-actions>
      </oxd-form>
    </div>
    <br />
    <slot name="footer"></slot>
  </div>
</template>

<script>
import {APIService} from '@/core/util/services/api.service';
import {reloadPage} from '@/core/util/helper/navigation';

export default {
  name: 'TwoFactorAuth',

  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/auth/two-factor-auth/verification',
    );
    return {
      http,
    };
  },

  data() {
    return {
      isLoading: false,
      otp: '',
    };
  },

  methods: {
    onSave() {
      this.isLoading = true;
      this.http
        .create({
          otp: this.otp,
        })
        .then(() => {
          return this.$toast.saveSuccess(); // TODO
        })
        .then(() => {
          reloadPage();
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.orangehrm-card-container {
  box-shadow: 3px 3px 10px $oxd-interface-gray-color;
}

.orangehrm-2fa {
  &-container {
    display: flex;
    width: inherit;
    height: inherit;
    padding: 1rem 2rem;
    align-items: center;
    flex-direction: column;
    justify-content: center;
    @include oxd-respond-to('md') {
      margin: 0 auto;
      max-width: 450px;
    }
  }

  &-title {
    font-weight: 700;
  }

  &-note-container {
    padding-bottom: 1.2rem;
  }

  &-button {
    flex: 1;

    &:nth-child(2) {
      margin-top: 0.5rem;
      @include oxd-respond-to('md') {
        margin: 0;
        margin-left: 0.5rem;
      }
    }

    &-container {
      display: flex;
      flex-direction: column;
      justify-content: center;
      @include oxd-respond-to('md') {
        flex-direction: row;
      }
    }
  }
}
</style>
