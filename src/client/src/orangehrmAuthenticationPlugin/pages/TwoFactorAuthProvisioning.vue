<template>
  <div class="orangehrm-2fa-container">
    <div class="orangehrm-card-container">
      <oxd-form :loading="isLoading" @submit-valid="onSave">
        <oxd-text tag="h6" class="orangehrm-2fa-title">
          <!--          todo:: localization-->
          Setup Authenticator App
        </oxd-text>

        <br />

        <div>
          <oxd-text type="toast-message">
            <!--          todo:: localization-->
            When prompted during sign-in, you can use an authenticator app for
            generating one-time passwords.
          </oxd-text>
        </div>

        <oxd-form-row>
          <qr-code class="orangehrm-2fa-qr" :value="provisioningUri"></qr-code>
        </oxd-form-row>
        <oxd-form-row>
          <oxd-icon-button
            name="files"
            @click.stop="copySecret"
          ></oxd-icon-button>
        </oxd-form-row>
        <oxd-form-row>
          <oxd-input-field
            v-model="otp"
            name="otp"
            label="Verify the code from the app"
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
import QRCode from '@/orangehrmCorporateDirectoryPlugin/components/QRCode';
import {APIService} from '@/core/util/services/api.service';
import {reloadPage} from '@ohrm/core/util/helper/navigation';
// import {OxdDivider} from '@ohrm/oxd';

export default {
  name: 'TwoFactorAuthProvisioning',
  components: {
    'qr-code': QRCode,
    // 'oxd-divider': OxdDivider,
  },
  props: {
    secret: {
      type: String,
      required: true,
    },
    provisioningUri: {
      type: String,
      required: true,
    },
    enrollmentId: {
      type: Number,
      required: true,
    },
  },

  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/auth/two-factor-auth/enrollments',
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
    copySecret() {
      navigator.clipboard?.writeText(this.secret);
    },
    onSave() {
      this.isLoading = true;
      this.http
        .create({
          enrollmentId: this.enrollmentId,
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

  &-qr {
    padding: 10px;
    border-radius: 5px;
    width: fit-content;
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
