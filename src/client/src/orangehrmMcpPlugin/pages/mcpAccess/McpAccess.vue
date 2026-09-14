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
          data that the person signing in already has. Every request is still
          checked against that person's own permissions, and every call is
          recorded in the MCP audit log. Start with a small group and only the
          tools you need."
      ></oxd-alert>

      <oxd-form :loading="isLoading" @submit-valid="onSave">
        <!-- Master switch -->
        <oxd-form-row>
          <oxd-input-field
            v-model="config.enabled"
            type="switch"
            label="Enable MCP access"
          />
        </oxd-form-row>

        <!-- Global circuit breaker over write tools. OXD radios are one input
             per option carrying :option-label and :value, not an options array. -->
        <oxd-form-row>
          <oxd-input-group label="Access mode">
            <oxd-input-field
              v-model="config.mode"
              type="radio"
              option-label="Read only"
              value="read_only"
            />
            <oxd-input-field
              v-model="config.mode"
              type="radio"
              option-label="Read and write"
              value="read_write"
            />
          </oxd-input-group>
          <oxd-text tag="p" class="orangehrm-tool-hint">
            Read only refuses every tool that changes data, whatever the user's
            own permissions allow. No write tools exist yet, so this setting has
            no effect today; it takes effect as soon as any are added.
          </oxd-text>
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
                :option-label="tool.label"
              />
              <oxd-text tag="p" class="orangehrm-tool-hint">
                {{ tool.description }}
              </oxd-text>
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <oxd-divider />

        <oxd-form-actions>
          <oxd-button display-type="ghost" label="Cancel" @click="onCancel" />
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
      // Checkbox state is kept as maps keyed by id/name so the template binds
      // directly; they are flattened back to arrays on save.
      selectedRoles: {},
      selectedTools: {},
    };
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

          this.selectedRoles = {};
          this.availableRoles.forEach((r) => {
            this.selectedRoles[r.id] = (d.allowedRoleIds || []).includes(r.id);
          });
          this.selectedTools = {};
          this.availableTools.forEach((t) => {
            this.selectedTools[t.name] = (d.enabledTools || []).includes(
              t.name,
            );
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
          // `approvedClientIds` is deliberately omitted: OAuth clients are
          // managed on the OAuth screen, and the API preserves any setting it
          // is not sent rather than clearing it.
          data: {
            enabled: this.config.enabled,
            mode: this.config.mode,
            allowedRoleIds: Object.keys(this.selectedRoles)
              .filter((id) => this.selectedRoles[id])
              .map((id) => parseInt(id, 10)),
            enabledTools: Object.keys(this.selectedTools).filter(
              (name) => this.selectedTools[name],
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
