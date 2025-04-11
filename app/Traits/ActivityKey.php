<?php

namespace App\Traits;

class ActivityKey
{
    // Các hằng số liên quan đến Lead
    public const CREATE_LEAD = 'create_lead';
    public const UPDATE_LEAD = 'update_lead';
    public const DELETE_LEAD = 'delete_lead';
    public const CHANGE_STATUS_BY_LEAD = 'change_status_by_lead';
    public const CONVERT_CUSTOMER_BY_LEAD = 'convert_customer_by_lead';
    public const CREATE_PROPOSAL_BY_LEAD = 'create_proposal_by_lead';
    public const UPDATE_PROPOSAL_BY_LEAD = 'update_proposal_by_lead';
    public const DELETE_PROPOSAL_BY_LEAD = 'delete_proposal_by_lead';
    public const CREATE_TASK_BY_LEAD = 'create_task_by_lead';
    public const UPDATE_TASK_BY_LEAD = 'update_task_by_lead';
    public const DELETE_TASK_BY_LEAD = 'delete_task_by_lead';
    public const CHANGE_STATUS_BY_TASK_IN_LEAD = 'change_status_by_task_in_lead';
    public const CHANGE_PRIORITY_BY_TASK_IN_LEAD = 'change_priority_by_task_in_lead';
    public const CREATE_CHECKLIST_BY_TASK_IN_LEAD = 'create_checklist_by_task_in_lead';
    public const DELETE_CHECKLIST_BY_TASK_IN_LEAD = 'delete_checklist_by_task_in_lead';
    public const CREATE_COMMENT_BY_TASK_IN_LEAD = 'create_comment_by_task_in_lead';
    public const UPDATE_COMMENT_BY_TASK_IN_LEAD = 'update_comment_by_task_in_lead';
    public const DELETE_COMMENT_BY_TASK_IN_LEAD = 'delete_comment_by_task_in_lead';
    public const CREATE_ASSIGNED_BY_TASK_IN_LEAD = 'create_assigned_by_task_in_lead';
    public const DELETE_ASSIGNED_BY_TASK_IN_LEAD = 'delete_assigned_by_task_in_lead';
    public const CREATE_FOLLOWER_BY_TASK_IN_LEAD = 'create_follower_by_task_in_lead';
    public const DELETE_FOLLOWER_BY_TASK_IN_LEAD = 'delete_follower_by_task_in_lead';
    public const COPY_TASK_IN_LEAD = 'copy_task_in_lead';
    public const DELETE_TAG_BY_TASK_IN_LEAD = 'delete_tag_by_task_in_lead';
    public const UPLOAD_FILE_BY_LEAD = 'upload_file_by_lead';
    public const UPDATE_REMINDER_BY_LEAD = 'update_reminder_by_lead';
    public const UPLOAD_REMINDER_BY_TASK_IN_LEAD = 'upload_reminder_by_task_in_lead';
    public const DELETE_REMINDER_BY_LEAD = 'delete_reminder_by_lead';
    public const DELETE_REMINDER_BY_TASK_IN_LEAD = 'delete_reminder_by_task_in_lead';
    public const CREATE_REMINDER_BY_LEAD = 'create_reminder_by_lead';
    public const CREATE_REMINDER_BY_TASK_IN_LEAD = 'create_reminder_by_task_in_lead';
    public const UPDATE_NOTE_BY_LEAD = 'update_note_by_lead';
    public const DELETE_NOTE_BY_LEAD = 'delete_note_by_lead';
    public const CREATE_NOTE_BY_LEAD = 'create_note_by_lead';

    // Các hằng số liên quan đến Project
    public const CREATED_PROJECT = 'created_project';
    public const CREATED_PROJECT_BY_CUSTOMER = 'created_project_by_customer';
    public const UPDATE_PROJECT = 'update_project';
    public const DELETE_PROJECT = 'delete_project';
    public const COPY_PROJECT = 'copy_project';
    public const CREATE_MEMBER_BY_PROJECT = 'create_member_by_project';
    public const DELETE_MEMBER_BY_PROJECT = 'delete_member_by_project';
    public const CREATE_DISCUSSIONS_BY_PROJECT = 'create_discussions_by_project';
    public const UPDATE_DISCUSSIONS_BY_PROJECT = 'update_discussions_by_project';
    public const DELETE_DISCUSSIONS_BY_PROJECT = 'delete_discussions_by_project';
    public const CREATE_FILE_BY_PROJECT = 'create_file_by_project';
    public const UPDATE_FILE_BY_PROJECT = 'update_file_by_project';
    public const DELETE_FILE_BY_PROJECT = 'delete_file_by_project';
    public const CHANGE_VISIBLE_TO_CUSTOMER_BY_FILE_BY_PROJECT = 'change_visible_to_customer_by_file_by_project';
    public const CREATE_MILESTONE_BY_PROJECT = 'create_milestone_by_project';
    public const UPDATE_MILESTONE_BY_PROJECT = 'update_milestone_by_project';
    public const DELETE_MILESTONE_BY_PROJECT = 'delete_milestone_by_project';
    public const CREATE_NOTE_BY_PROJECT = 'create_note_by_project';
    public const UPDATE_NOTE_BY_PROJECT = 'update_note_by_project';
    public const DELETE_NOTE_BY_PROJECT = 'delete_note_by_project';
    public const CREATE_TASK_BY_PROJECT = 'create_task_by_project';
    public const UPDATE_TASK_BY_PROJECT = 'update_task_by_project';
    public const DELETE_TASK_BY_PROJECT = 'delete_task_by_project';
    public const CHANGE_STATUS_BY_TASK_IN_PROJECT = 'change_status_by_task_in_project';
    public const CHANGE_PRIORITY_BY_TASK_IN_PROJECT = 'change_priority_by_task_in_project';
    public const CREATE_CHECKLIST_BY_TASK_IN_PROJECT = 'create_checklist_by_task_in_project';
    public const DELETE_CHECKLIST_BY_TASK_IN_PROJECT = 'delete_checklist_by_task_in_project';
    public const CREATE_COMMENT_BY_TASK_IN_PROJECT = 'create_comment_by_task_in_project';
    public const UPDATE_COMMENT_BY_TASK_IN_PROJECT = 'update_comment_by_task_in_project';
    public const DELETE_COMMENT_BY_TASK_IN_PROJECT = 'delete_comment_by_task_in_project';
    public const CREATE_ASSIGNED_BY_TASK_IN_PROJECT = 'create_assigned_by_task_in_project';
    public const DELETE_ASSIGNED_BY_TASK_IN_PROJECT = 'delete_assigned_by_task_in_project';
    public const CREATE_FOLLOWER_BY_TASK_IN_PROJECT = 'create_follower_by_task_in_project';
    public const DELETE_FOLLOWER_BY_TASK_IN_PROJECT = 'delete_follower_by_task_in_project';
    public const COPY_TASK_IN_PROJECT = 'copy_task_in_project';
    public const DELETE_TAG_BY_TASK_IN_PROJECT = 'delete_tag_by_task_in_project';
    public const CREATE_TIMESHEETS_BY_TASK_IN_PROJECT = 'create_timesheets_by_task_in_project';
    public const UPDATE_TIMESHEETS_BY_TASK_IN_PROJECT = 'update_timesheets_by_task_in_project';
    public const DELETE_TIMESHEETS_BY_TASK_IN_PROJECT = 'delete_timesheets_by_task_in_project';
    public const UPDATE_REMINDER_BY_TASK_IN_PROJECT = 'update_reminder_by_task_in_project';
    public const DELETE_REMINDER_BY_TASK_IN_PROJECT = 'delete_reminder_by_task_in_project';
    public const CREATE_REMINDER_BY_TASK_IN_PROJECT = 'create_reminder_by_task_in_project';
    public const CREATE_EXPENSE_BY_PROJECT = 'create_expense_by_project';
    public const UPDATE_EXPENSE_BY_PROJECT = 'update_expense_by_project';
    public const DELETE_EXPENSE_BY_PROJECT = 'delete_expense_by_project';

    // Các hằng số liên quan đến Proposal (Sale Activity)
    public const CREATE_PROPOSAL = 'create_proposal';
    public const UPDATE_PROPOSAL = 'update_proposal';
    public const DELETE_PROPOSAL = 'delete_proposal';
    public const CHANGE_STATUS_BY_PROPOSAL = 'change_status_by_proposal';
    public const CREATE_COMMENT_BY_PROPOSAL = 'create_comment_by_proposal';
    public const UPDATE_COMMENT_BY_PROPOSAL = 'update_comment_by_proposal';
    public const DELETE_COMMENT_BY_PROPOSAL = 'delete_comment_by_proposal';
    public const COPY_PROPOSAL = 'copy_proposal';
    public const CREATE_PROPOSAL_BY_CUSTOMER = 'create_proposal_by_customer';

    // Các hằng số liên quan đến Estimate
    public const CREATE_ESTIMATE = 'create_estimate';
    public const CREATE_ESTIMATE_BY_CUSTOMER = 'create_estimate_by_customer';
    public const UPDATE_ESTIMATE = 'update_estimate';
    public const DELETE_ESTIMATE = 'delete_estimate';
    public const CHANGE_STATUS_BY_ESTIMATE = 'change_status_by_estimate';
    public const COPY_ESTIMATE = 'copy_estimate';

    // Các hằng số liên quan đến Invoice
    public const CREATE_INVOICE_BY_CUSTOMER = 'create_invoice_by_customer';
    public const UPDATE_INVOICE = 'update_invoice';
    public const DELETE_INVOICE = 'delete_invoice';
    public const CREATE_INVOICE = 'create_invoice';
    public const CREATE_PAYMENT_BY_INVOICE = 'create_payment_by_invoice';
    public const CONVERT_ESTIMATE_BY_INVOICE = 'convert_estimate_by_invoice';
    public const CONVERT_PROPOSAL_BY_INVOICE = 'convert_proposal_by_invoice';
    public const COPY_INVOICE = 'copy_invoice';

    // Các hằng số liên quan đến Payment
    public const CREATE_PAYMENT_BY_CUSTOMER = 'create_payment_by_customer';
    public const UPDATE_PAYMENT = 'update_payment';
    public const DELETE_PAYMENT = 'delete_payment';

    // Các hằng số liên quan đến Credit Note
    public const CREATE_CREDIT_NOTE = 'create_credit_note';
    public const UPDATE_CREDIT_NOTE = 'update_credit_note';
    public const DELETE_CREDIT_NOTE = 'delete_credit_note';
    public const CREATE_CREDIT_NOTE_BY_CUSTOMER = 'create_credit_note_by_customer';

    // Các hằng số liên quan đến Item
    public const CREATE_ITEM = 'create_item';
    public const UPDATE_ITEM = 'update_item';
    public const DELETE_ITEM = 'delete_item';
}

