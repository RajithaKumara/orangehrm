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
      <div class="orangehrm-header-container">
        <oxd-text tag="h6" class="orangehrm-main-title">
          MCP Configuration
        </oxd-text>
        <oxd-switch-input
          v-model="config.enabled"
          label-position="left"
          option-label="Enable"
        />
      </div>

      <oxd-divider />

      <oxd-alert
        type="warn"
        :show="true"
        message="Connecting an AI assistant gives it the same access to OrangeHRM
          data that the person signing in already has. Every request is still
          checked against that person's own permissions."
      ></oxd-alert>

      <oxd-form :loading="isLoading" @submit-valid="onSave">
        <div
          v-if="health.results.length"
          class="orangehrm-mcp-health"
          :class="health.ready ? '--ready' : '--blocked'"
        >
          <div class="orangehrm-mcp-health__summary">
            <oxd-icon
              :name="
                health.ready ? 'check-circle-fill' : 'exclamation-triangle-fill'
              "
              class="orangehrm-mcp-health__icon"
            />
            <span class="orangehrm-mcp-health__headline">
              {{
                health.ready
                  ? 'Ready to connect an AI assistant'
                  : failedCheckCount +
                    ' of these needs attention before a client can connect'
              }}
            </span>
          </div>

          <div
            v-for="check in health.results"
            :key="check.name"
            class="orangehrm-mcp-health__row"
            :class="'--' + check.status"
          >
            <oxd-icon
              :name="checkIcon(check.status)"
              class="orangehrm-mcp-health__icon"
            />
            <span class="orangehrm-mcp-health__name">{{ check.name }}</span>
            <span class="orangehrm-mcp-health__detail">{{
              check.message
            }}</span>
          </div>
        </div>

        <!-- The two URLs an admin hands to an AI client -->
        <oxd-form-row>
          <oxd-input-field
            :model-value="mcpEndpoint"
            label="MCP server URL"
            readonly
          />
          <oxd-text tag="p" class="orangehrm-input-hint">
            Give this to the AI client when adding OrangeHRM as an MCP server.
          </oxd-text>
        </oxd-form-row>

        <oxd-form-row>
          <oxd-input-field
            :model-value="oauthMetadataUrl"
            label="OAuth authorization server URL"
            readonly
          />
          <oxd-text tag="p" class="orangehrm-input-hint">
            Only needed by clients that cannot discover the endpoint themselves.
            Clients that can will look for it at the host root instead, which
            needs a webserver rewrite when OrangeHRM runs under a subpath.
          </oxd-text>
        </oxd-form-row>

        <oxd-divider />

        <!-- Who may connect -->
        <oxd-text tag="p" class="orangehrm-sub-title">
          User Role Permission
        </oxd-text>
        <oxd-form-row>
          <oxd-grid :cols="1" class="orangehrm-full-width-grid">
            <oxd-grid-item v-for="role in availableRoles" :key="role.id">
              <oxd-input-field
                v-model="selectedRoles[role.id]"
                type="checkbox"
                :option-label="role.displayName"
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

        <!-- Top-level verb switches. These sit above the grid below: turning one
             off refuses every tool of that kind whatever the grid says. -->
        <oxd-text tag="p" class="orangehrm-sub-title">
          Read/Write Permission
        </oxd-text>
        <oxd-form-row v-for="verb in verbs" :key="verb.key">
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item class="orangehrm-mcp-switch">
              <oxd-text tag="p" class="orangehrm-mcp-switch-text">
                {{ verb.label }}
              </oxd-text>
              <oxd-switch-input v-model="config.verbs[verb.key]" />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <oxd-form-row v-if="config.verbs.delete">
          <oxd-alert
            type="error"
            :show="true"
            message="Records the assistant removes cannot be recovered.
              Deletion is limited to the APIs you enable under Advanced
              Permission."
          ></oxd-alert>
        </oxd-form-row>
        <oxd-form-row v-else-if="config.verbs.create || config.verbs.update">
          <oxd-alert
            type="warn"
            :show="true"
            message="The assistant can add and change records as the person
              signed in. A leave request, timesheet or employee record it
              creates is indistinguishable from one they entered themselves,
              and goes through the same approvals and notifications."
          ></oxd-alert>
        </oxd-form-row>

        <!-- The per-API grid is the advanced half of the same decision: the
             switches above say which kinds of action are allowed at all, this
             says where. Folded away because most administrators will set the
             verbs, leave the shipped set alone, and never open it. -->
        <div class="orangehrm-mcp-advanced">
          <div
            class="orangehrm-mcp-advanced__header"
            @click="showAdvanced = !showAdvanced"
          >
            <span class="orangehrm-mcp-advanced__title">
              Advanced Permission
            </span>
            <span class="orangehrm-mcp-advanced__summary">
              <span
                v-for="verb in verbs"
                :key="verb.key"
                class="orangehrm-mcp-summary__item"
                :class="'orangehrm-mcp-summary__item--' + verb.risk"
              >
                {{ verb.label }}:
                <strong>{{ liveCounts[verb.key] }}</strong>
                of {{ availableFor(verb.key) }} tools
              </span>
            </span>
            <span class="orangehrm-mcp-module__chevron">
              {{ showAdvanced ? '−' : '+' }}
            </span>
          </div>

          <div v-show="showAdvanced" class="orangehrm-mcp-advanced__body">
            <oxd-form-row v-if="totalEnabledTools > toolCountCeiling">
              <oxd-alert
                type="warn"
                :show="true"
                :message="
                  totalEnabledTools +
                  ' tools are enabled. Past roughly ' +
                  toolCountCeiling +
                  ', assistants get measurably worse at picking the right one and ' +
                  'answers degrade. Turn off what nobody asks about.'
                "
              ></oxd-alert>
            </oxd-form-row>

            <div
              v-for="group in modules"
              :key="group.module"
              class="orangehrm-mcp-module"
            >
              <div
                class="orangehrm-mcp-module__header"
                @click="toggleModule(group.module)"
              >
                <span class="orangehrm-mcp-module__title">
                  {{ group.label }}
                  <span class="orangehrm-mcp-module__count">
                    {{ enabledInGroup(group) }} of
                    {{ group.apis.length }} enabled
                  </span>
                </span>
                <span class="orangehrm-mcp-module__actions">
                  <span class="orangehrm-mcp-module__chevron">
                    {{ isExpanded(group.module) ? '−' : '+' }}
                  </span>
                </span>
              </div>

              <div
                v-show="isExpanded(group.module)"
                class="orangehrm-mcp-table"
              >
                <div class="orangehrm-mcp-row orangehrm-mcp-row--head">
                  <span class="orangehrm-mcp-row__api">API</span>
                  <span
                    v-for="verb in verbs"
                    :key="verb.key"
                    class="orangehrm-mcp-row__verb"
                    :class="'orangehrm-mcp-row__verb--' + verb.risk"
                  >
                    {{ verb.label }}
                  </span>
                </div>

                <div
                  v-for="api in group.apis"
                  :key="api.apiName"
                  class="orangehrm-mcp-row"
                >
                  <span class="orangehrm-mcp-row__api">
                    {{ api.label }}
                    <span
                      v-if="api.sensitivity !== 'normal'"
                      class="orangehrm-mcp-badge"
                      :class="'orangehrm-mcp-badge--' + api.sensitivity"
                      :title="sensitivityHint(api.sensitivity)"
                    >
                      {{ sensitivityLabel(api.sensitivity) }}
                    </span>
                    <span class="orangehrm-mcp-row__paths">
                      {{ api.paths.join(', ') }}
                    </span>
                  </span>
                  <span
                    v-for="verb in verbs"
                    :key="verb.key"
                    class="orangehrm-mcp-row__verb"
                  >
                    <oxd-input-field
                      v-if="hasVerb(api, verb.key) && permissions[api.apiName]"
                      v-model="permissions[api.apiName][verb.key]"
                      type="checkbox"
                      :disabled="!config.verbs[verb.key]"
                    />
                    <span v-else class="orangehrm-mcp-row__na">—</span>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

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
import {OxdAlert, OxdIcon, OxdSwitchInput} from '@ohrm/oxd';
import {APIService} from '@/core/util/services/api.service';

const VERBS = [
  {
    key: 'read',
    label: 'Read',
    risk: 'none',
  },
  {
    key: 'create',
    label: 'Create',
    risk: 'warn',
  },
  {
    key: 'update',
    label: 'Update',
    risk: 'warn',
  },
  {
    key: 'delete',
    label: 'Delete',
    risk: 'danger',
  },
];

function emptyCounts() {
  const counts = {};
  VERBS.forEach((v) => {
    counts[v.key] = {available: 0, enabled: 0};
  });
  return counts;
}

const SENSITIVITY = {
  sensitive: {
    label: 'Sensitive',
    hint:
      'Compensation, immigration status, termination, evaluations, uploaded ' +
      'documents or account control. Enable only with a specific reason.',
  },
};

export default {
  name: 'McpConfig',

  components: {
    // Neither is registered globally. Without this the alerts render nothing
    // at all -- which is how the delete and write warnings on this screen were
    // invisible from the day they were written.
    'oxd-alert': OxdAlert,
    'oxd-icon': OxdIcon,
    'oxd-switch-input': OxdSwitchInput,
  },

  props: {
    mcpEndpoint: {
      type: String,
      default: '',
    },

    oauthMetadataUrl: {
      type: String,
      default: '',
    },
  },

  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      'api/v2/admin/mcp/config',
    );
    return {http};
  },

  data() {
    return {
      isLoading: false,
      verbs: VERBS,
      // Past this many tools, tool selection measurably degrades. Shown as a
      // warning rather than enforced: a large instance may still want them.
      toolCountCeiling: 120,
      showAdvanced: false,
      config: {
        enabled: false,
        verbs: {read: true, create: false, update: false, delete: false},
        userOverrides: [],
      },
      modules: [],
      health: {ready: true, results: []},
      // Shaped like the loaded payload, because the first render happens before
      // the GET resolves. An empty object here means the summary bar reads
      // `undefined.available` and takes the whole screen down.
      counts: {tools: emptyCounts(), classes: 0},
      availableRoles: [],
      // Checkbox state keyed by id/class name so the template binds directly;
      // flattened back to arrays on save.
      selectedRoles: {},
      permissions: {},
      expanded: {},
    };
  },

  computed: {
    /**
     * Tools enabled right now, per verb, counted from the unsaved state so the
     * numbers move as the administrator clicks.
     */
    liveCounts() {
      const totals = {read: 0, create: 0, update: 0, delete: 0};
      this.modules.forEach((group) => {
        group.apis.forEach((api) => {
          const row = this.permissions[api.apiName];
          if (!row) return;
          api.verbs.forEach((v) => {
            if (row[v.verb] && this.config.verbs[v.verb]) {
              totals[v.verb] += v.tools;
            }
          });
        });
      });
      return totals;
    },

    totalEnabledTools() {
      return VERBS.reduce((sum, v) => sum + this.liveCounts[v.key], 0);
    },

    failedCheckCount() {
      return this.health.results.filter((c) => c.status === 'fail').length;
    },
  },

  beforeMount() {
    this.load();
  },

  methods: {
    load() {
      this.isLoading = true;
      this.http
        .request({method: 'GET', url: 'api/v2/admin/mcp/config'})
        .then(({data}) => {
          const d = data.data;
          this.config.enabled = d.enabled;
          this.config.verbs = {...this.config.verbs, ...(d.verbs || {})};
          this.config.userOverrides = d.userOverrides || [];
          this.availableRoles = d.availableRoles || [];
          this.modules = d.modules || [];
          this.health = d.healthChecks || {ready: true, results: []};
          this.counts = {
            tools: {...emptyCounts(), ...((d.counts || {}).tools || {})},
            classes: (d.counts || {}).classes || 0,
          };

          this.selectedRoles = {};
          this.availableRoles.forEach((r) => {
            this.selectedRoles[r.id] = (d.allowedRoleIds || []).includes(r.id);
          });

          const permissions = {};
          this.modules.forEach((group) => {
            group.apis.forEach((api) => {
              const row = {
                read: false,
                create: false,
                update: false,
                delete: false,
              };
              api.verbs.forEach((v) => {
                row[v.verb] = v.enabled;
              });
              permissions[api.apiName] = row;
            });
          });
          this.permissions = permissions;

          // Open the modules that already have something enabled; leave the
          // rest closed so the page opens on what this instance actually uses.
          const expanded = {};
          this.modules.forEach((group) => {
            expanded[group.module] = this.enabledInGroup(group) > 0;
          });
          this.expanded = expanded;
        })
        .finally(() => {
          this.isLoading = false;
        });
    },

    checkIcon(status) {
      if (status === 'fail') return 'x-circle-fill';
      return status === 'skip' ? 'dash-circle-fill' : 'check-circle-fill';
    },

    availableFor(verb) {
      return (this.counts.tools[verb] || {}).available || 0;
    },

    hasVerb(api, verb) {
      return api.verbs.some((v) => v.verb === verb);
    },

    isExpanded(module) {
      return !!this.expanded[module];
    },

    toggleModule(module) {
      this.expanded[module] = !this.expanded[module];
    },

    enabledInGroup(group) {
      return group.apis.filter((api) => {
        const row = this.permissions[api.apiName];
        return row && (row.read || row.create || row.update || row.delete);
      }).length;
    },

    sensitivityLabel(level) {
      return (SENSITIVITY[level] || {}).label || level;
    },

    sensitivityHint(level) {
      return (SENSITIVITY[level] || {}).hint || '';
    },

    onSave() {
      this.isLoading = true;
      this.http
        .request({
          method: 'PUT',
          url: 'api/v2/admin/mcp/config',
          // `approvedClientIds` is deliberately omitted: OAuth clients are
          // managed on the OAuth screen, and the API preserves any setting it
          // is not sent rather than clearing it.
          data: {
            enabled: this.config.enabled,
            verbs: this.config.verbs,
            allowedRoleIds: Object.keys(this.selectedRoles)
              .filter((id) => this.selectedRoles[id])
              .map((id) => parseInt(id, 10)),
            userOverrides: this.config.userOverrides,
            apiPermissions: Object.keys(this.permissions).map((apiName) => ({
              apiName,
              ...this.permissions[apiName],
            })),
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

<style src="./mcp-config.scss" lang="scss" scoped></style>
