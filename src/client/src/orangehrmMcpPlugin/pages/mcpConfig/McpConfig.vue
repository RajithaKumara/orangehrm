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
        MCP Configuration (AI Assistant)
      </oxd-text>

      <oxd-divider />

      <oxd-alert
        type="warn"
        :show="true"
        message="Connecting an AI assistant gives it the same access to OrangeHRM
          data that the person signing in already has. Every request is still
          checked against that person's own permissions, and every call is
          recorded in the MCP audit log. Start with a small group and only the
          data you need."
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

        <!-- Top-level verb switches. These sit above the grid below: turning one
             off refuses every tool of that kind whatever the grid says. -->
        <oxd-text tag="p" class="orangehrm-sub-title">
          What the assistant is allowed to do
        </oxd-text>
        <oxd-form-row>
          <oxd-grid :cols="4" class="orangehrm-full-width-grid">
            <oxd-grid-item v-for="verb in verbs" :key="verb.key">
              <oxd-input-field
                v-model="config.verbs[verb.key]"
                type="switch"
                :label="verb.label"
              />
              <oxd-text
                tag="p"
                class="orangehrm-tool-hint orangehrm-mcp-verb-hint"
              >
                {{ verb.hint }}
              </oxd-text>
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <oxd-form-row v-if="config.verbs.delete">
          <oxd-alert
            type="error"
            :show="true"
            message="Delete is on. An assistant that misreads a request can
              remove records permanently, and OrangeHRM has no undo. Leave this
              off unless a specific workflow needs it, and keep the enabled
              delete list as short as possible."
          ></oxd-alert>
        </oxd-form-row>
        <oxd-form-row v-else-if="config.verbs.create || config.verbs.update">
          <oxd-alert
            type="warn"
            :show="true"
            message="The assistant can change data. Written records look
              identical to ones a person entered, so make sure the audit log is
              being reviewed."
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
              Advanced: which parts of OrangeHRM
            </span>
            <span class="orangehrm-mcp-advanced__summary">
              {{ totalEnabledTools }} tools enabled across
              {{ enabledApiCount }} APIs
            </span>
            <span class="orangehrm-mcp-module__chevron">
              {{ showAdvanced ? '−' : '+' }}
            </span>
          </div>

          <div v-show="showAdvanced" class="orangehrm-mcp-advanced__body">
            <oxd-text tag="p" class="orangehrm-tool-hint orangehrm-mcp-flush">
              One row per API. A row decides what the assistant may do with that
              part of OrangeHRM; the switches above decide whether that kind of
              action is allowed at all. Anything a person's own role forbids
              stays forbidden either way.
            </oxd-text>

            <oxd-form-row>
              <div class="orangehrm-mcp-summary">
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
                <span class="orangehrm-mcp-summary__total">
                  {{ totalEnabledTools }} tools enabled
                </span>
              </div>
            </oxd-form-row>

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

            <oxd-form-row>
              <oxd-input-field
                v-model="search"
                label="Find an API"
                placeholder="employee, leave balance, salary..."
              />
            </oxd-form-row>

            <div
              v-for="group in filteredModules"
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

            <oxd-form-row v-if="search.trim() && !filteredModules.length">
              <oxd-text tag="p" class="orangehrm-tool-hint">
                Nothing matches "{{ search }}".
              </oxd-text>
            </oxd-form-row>
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
import {APIService} from '@/core/util/services/api.service';

const VERBS = [
  {
    key: 'read',
    label: 'Read',
    risk: 'none',
    hint: 'Look things up and answer questions. Changes nothing.',
  },
  {
    key: 'create',
    label: 'Create',
    risk: 'warn',
    hint: 'Add records: a leave request, a candidate, a timesheet entry.',
  },
  {
    key: 'update',
    label: 'Update',
    risk: 'warn',
    hint: 'Change existing records in place.',
  },
  {
    key: 'delete',
    label: 'Delete',
    risk: 'danger',
    hint: 'Remove records permanently. There is no undo.',
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
  elevated: {
    label: 'Personal',
    hint: 'Identifiable personal data about an employee or candidate.',
  },
  high: {
    label: 'Highly sensitive',
    hint:
      'Compensation, immigration status, termination, evaluations, uploaded ' +
      'documents or account control. Enable only with a specific reason.',
  },
};

export default {
  name: 'McpConfig',

  props: {
    mcpEndpoint: {
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
      search: '',
      showAdvanced: false,
      config: {
        enabled: false,
        verbs: {read: true, create: false, update: false, delete: false},
        userOverrides: [],
      },
      modules: [],
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
     * Modules and rows matching the search box. An empty search shows
     * everything; a search auto-expands what it matched, because a hit inside a
     * collapsed section is the same as no hit at all.
     */
    filteredModules() {
      const term = this.search.trim().toLowerCase();
      if (!term) return this.modules;

      return this.modules
        .map((group) => {
          const apis = group.apis.filter((api) => {
            return (
              api.label.toLowerCase().includes(term) ||
              api.apiName.toLowerCase().includes(term) ||
              group.label.toLowerCase().includes(term) ||
              api.paths.some((p) => p.toLowerCase().includes(term))
            );
          });
          return {...group, apis};
        })
        .filter((group) => group.apis.length > 0);
    },

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

    /** APIs with at least one verb enabled, for the collapsed summary. */
    enabledApiCount() {
      return Object.keys(this.permissions).filter((name) => {
        const row = this.permissions[name];
        return row.read || row.create || row.update || row.delete;
      }).length;
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

    availableFor(verb) {
      return (this.counts.tools[verb] || {}).available || 0;
    },

    hasVerb(api, verb) {
      return api.verbs.some((v) => v.verb === verb);
    },

    isExpanded(module) {
      // A search result is always shown expanded, whatever the saved state.
      return this.search.trim() ? true : !!this.expanded[module];
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
