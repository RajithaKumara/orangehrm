INSERT INTO ohrm_data_group (`name`, `description`, `can_read`, `can_create`, `can_update`, `can_delete`)
VALUES ('apiv2_free_trial_subscribe', 'API-v2 Free trial subscribe', 0, 1, 0, 0);

SET
@core_module_id := (SELECT `id` FROM ohrm_module WHERE name = 'core' LIMIT 1);

SET
@apiv2_free_trial_subscribe_data_group_id := (SELECT `id` FROM ohrm_data_group WHERE name =
 'apiv2_free_trial_subscribe' LIMIT 1);

INSERT INTO ohrm_api_permission (`api_name`, `module_id`, `data_group_id`)
VALUES ('OrangeHRM\\FreeTrial\\Api\\SubscribeFreeTrialAPI', @core_module_id, @apiv2_free_trial_subscribe_data_group_id);

SET
@admin_role_id := (SELECT `id` FROM ohrm_user_role WHERE `name` = 'Admin' LIMIT 1);

INSERT INTO ohrm_user_role_data_group (`can_read`, `can_create`, `can_update`, `can_delete`, `self`, `data_group_id`,
                                       `user_role_id`)
VALUES (0, 1, 0, 0, 0, @apiv2_free_trial_subscribe_data_group_id, @admin_role_id);
