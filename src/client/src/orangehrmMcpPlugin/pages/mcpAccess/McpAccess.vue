<!--
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with OrangeHRM.
 * If not, see <https://www.gnu.org/licenses/>.
 */
-->

<template>
  <div class="orangehrm-background-container">
    <div class="orangehrm-card-container">
      <oxd-text tag="h6" class="orangehrm-main-title">
        MCP Access (AI Assistant)
      </oxd-text>

      <oxd-divider />

      <oxd-alert
        type="warn"
        :show="true"
        message="Connecting an AI assistant gives it the same access to OrangeHRM
          data that the person signing in already has. There are no per-tool
          consent prompts. Start with read only, allow a small group, and
          review the audit log."
      ></oxd-alert>

      <oxd-form :loading="isLoading" @submit-valid="onSave">
        <!-- Master switch -->
        <oxd-form-row>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="config.enabled"
                type="switch"
                label="Enable MCP access"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="config.mode"
                type="radio"
                label="Access mode"
                :options="modeOptions"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <!-- The endpoint an admin hands to the AI client -->
        <oxd-form-row>
          <oxd-input-field
            :model-value="mcpEndpoint"
            label="MCP server URL (give this to the AI client)"
            readonly
          />
        </oxd-form-row>

        <oxd-divider />

        <!-- Who may connect -->
        <oxd-text tag="p" class="orangehrm-sub-title">Who can connect</oxd-text>
        <oxd-form-row>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item v-for="role in availableRoles" :key="role.id">
              <oxd-input-field
                v-model="selectedRoles[role.id]"
                type="checkbox"
                :option-label="role.displayName + ' users'"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <!-- Per-user exceptions on top of the role default -->
        <oxd-form-row v-if="config.userOverrides.length">
          <oxd-text tag="p" class="orangehrm-sub-title">
            Individual exceptions
          </oxd-text>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item
              v-for="(ovr, i) in config.userOverrides"
              :key="ovr.userId"
            >
              <oxd-input-field
                v-model="config.userOverrides[i].allowed"
                type="checkbox"
                :option-label="
                  ovr.userName + (ovr.allowed ? ' (allowed)' : ' (blocked)')
                "
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <oxd-divider />

        <!-- Tool surface -->
        <oxd-text tag="p" class="orangehrm-sub-title">
          What the assistant can do
        </oxd-text>
        <oxd-form-row>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item v-for="tool in availableTools" :key="tool.name">
              <oxd-input-field
                v-model="selectedTools[tool.name]"
                type="checkbox"
                :option-label="tool.description"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <oxd-divider />

        <!-- Approved applications: this is the audience control -->
        <oxd-text tag="p" class="orangehrm-sub-title">
          Approved AI applications
        </oxd-text>
        <oxd-alert
          v-if="!anyClientApproved"
          type="warn"
          :show="true"
          message="No application is approved yet, so any application holding a
            valid OrangeHRM token can reach MCP. Approve only the AI clients you
            intend to use."
        ></oxd-alert>
        <oxd-form-row>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item
              v-for="client in availableClients"
              :key="client.clientId"
            >
              <oxd-input-field
                v-model="selectedClients[client.clientId]"
                type="checkbox"
                :option-label="client.name"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <oxd-divider />

        <oxd-form-actions>
          <oxd-button
            display-type="ghost"
            label="Cancel"
            @click="onCancel"
          />
          <oxd-button
            class="orangehrm-left-space"
            display-type="secondary"
            label="Save"
            type="submit"
          />
        </oxd-form-actions>
      </oxd-form>
    </div>
  </div>
</template>

<script>
import {APIService} from '@/core/util/services/api.service';

export default {
  name: 'McpAccess',

  props: {
    mcpEndpoint: {
      type: String,
      default: '',
    },
  },

  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      'api/v2/admin/mcp/access-config',
    );
    return {http};
  },

  data() {
    return {
      isLoading: false,
      config: {
        enabled: false,
        mode: 'read_only',
        userOverrides: [],
      },
      availableRoles: [],
      availableTools: [],
      availableClients: [],
      // Checkbox state is kept as maps keyed by id/name so the template binds
      // directly; they are flattened back to arrays on save.
      selectedRoles: {},
      selectedTools: {},
      selectedClients: {},
      modeOptions: [
        {id: 'read_only', label: 'Read only (recommended)'},
        {id: 'read_write', label: 'Read and write'},
      ],
    };
  },

  computed: {
    anyClientApproved() {
      return Object.values(this.selectedClients).some(Boolean);
    },
  },

  beforeMount() {
    this.load();
  },

  methods: {
    load() {
      this.isLoading = true;
      this.http
        .request({method: 'GET', url: 'api/v2/admin/mcp/access-config'})
        .then(({data}) => {
          const d = data.data;
          this.config.enabled = d.enabled;
          this.config.mode = d.mode;
          this.config.userOverrides = d.userOverrides || [];
          this.availableRoles = d.availableRoles || [];
          this.availableTools = d.availableTools || [];
          this.availableClients = d.availableClients || [];

          this.selectedRoles = {};
          this.availableRoles.forEach((r) => {
            this.selectedRoles[r.id] = (d.allowedRoleIds || []).includes(r.id);
          });
          this.selectedTools = {};
          this.availableTools.forEach((t) => {
            this.selectedTools[t.name] = (d.enabledTools || []).includes(t.name);
          });
          this.selectedClients = {};
          this.availableClients.forEach((c) => {
            this.selectedClients[c.clientId] = (
              d.approvedClientIds || []
            ).includes(c.clientId);
          });
        })
        .finally(() => {
          this.isLoading = false;
        });
    },

    onSave() {
      this.isLoading = true;
      this.http
        .request({
          method: 'PUT',
          url: 'api/v2/admin/mcp/access-config',
          data: {
            enabled: this.config.enabled,
            mode: this.config.mode,
            allowedRoleIds: Object.keys(this.selectedRoles)
              .filter((id) => this.selectedRoles[id])
              .map((id) => parseInt(id, 10)),
            enabledTools: Object.keys(this.selectedTools).filter(
              (name) => this.selectedTools[name],
            ),
            approvedClientIds: Object.keys(this.selectedClients).filter(
              (id) => this.selectedClients[id],
            ),
            userOverrides: this.config.userOverrides,
          },
        })
        .then(() => {
          return this.$toast.updateSuccess();
        })
        .then(() => this.load())
        .finally(() => {
          this.isLoading = false;
        });
    },

    onCancel() {
      this.load();
    },
  },
};
</script>

<style src="./mcp-access.scss" lang="scss" scoped></style>
