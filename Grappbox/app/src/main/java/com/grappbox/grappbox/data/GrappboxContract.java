package com.grappbox.grappbox.data;

import android.content.ContentProvider;
import android.content.ContentResolver;
import android.content.ContentUris;
import android.content.ContentValues;
import android.net.Uri;
import android.provider.BaseColumns;
import android.provider.SyncStateContract;

import java.net.URI;
import java.util.HashMap;

/**
 * Created by marcw on 28/08/2016.
 */
public class GrappboxContract {
    public static final String CONTENT_AUTHORITY = "com.grappbox.grappbox";
    public static final Uri BASE_CONTENT_URI = Uri.parse("content://" + CONTENT_AUTHORITY);

    public static final String PATH_PROJECT = "project";
    public static final String PATH_PROJECT_ACCOUNT = "project_account";
    public static final String PATH_USER = "user";
    public static final String PATH_OCCUPATION = "occupation";
    public static final String PATH_EVENT = "event";
    public static final String PATH_EVENT_PARTICIPANT = "event_participant";
    public static final String PATH_TIMELINE = "timeline";
    public static final String PATH_TIMELINE_MESSAGES = "timeline_messages";
    public static final String PATH_TIMELINE_COMMENTS = "timeline_comments";
    public static final String PATH_ROLE = "roles";
    public static final String PATH_ROLE_ASSIGNATION = "role_assignations";
    public static final String PATH_TAG  = "tag";
    public static final String PATH_BUG = "bug";
    public static final String PATH_BUG_TAG = "bug_tag";
    public static final String PATH_BUG_ASSIGNATION = "bug_assignations";
    public static final String PATH_CLOUD = "cloud";

    public static final String PATH_CUSTOMER_ACCESS = "customer_access";
    public static final String PATH_TASK = "task";
    public static final String PATH_DEPENDENCIES = "task_dependencies";
    public static final String PATH_TASK_USER_ASSIGNATION = "task_users";
    public static final String PATH_TASK_TAGS = "task_tags";
    public static final String PATH_TASK_TAGS_ASSIGNATION = "task_users";

    public static final String PATH_STATS = "stats";
    public static final String PATH_ADVANCEMENT = "advancement";
    public static final String PATH_USER_ADVANCEMENT_TASK = "user_advancement_task";
    public static final String PATH_LATE_TASK = "late_task";
    public static final String PATH_TASK_REPARTITION = "task_repartition";
    public static final String PATH_USER_WORKING_CHARGE = "user_working_charge";
    public static final String PATH_BUG_USER_REPARTITION = "bug_user_repartition";
    public static final String PATH_BUG_TAGS_REPARTITION = "bug_tags_repartition";
    public static final String PATH_BUG_EVOLUTION = "bug_evolution";

    public static final String GENERAL_GRAPPBOX_ID = "grappbox_id";

    public static final class TaskEntry implements BaseColumns{
        public static final String TABLE_NAME = "tasks";

        public static final String COLUMN_GRAPPBOX_ID = GENERAL_GRAPPBOX_ID;
        public static final String COLUMN_TITLE = "title";
        public static final String COLUMN_DESCRIPTION = "desc";
        public static final String COLUMN_LOCAL_PROJECT = "project_id";
        public static final String COLUMN_DUE_DATE_UTC = "due_date";
        public static final String COLUMN_START_DATE_UTC = "start_date";
        public static final String COLUMN_FINISHED_DATE_UTC = "finished_date";
        public static final String COLUMN_IS_MILESTONE = "is_milestone";
        public static final String COLUMN_IS_CONTAINER = "is_container";
        public static final String COLUMN_PARENT_ID = "parent_id";
        public static final String COLUMN_ADVANCE = "advance";
        public static final String COLUMN_LOCAL_CREATOR = "creator_id";
        public static final String COLUMN_CREATED_AT_UTC = "created_at_date";

        public static Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_TASK).build();
        public static Uri WITH_USERS = CONTENT_URI.buildUpon().appendPath("users").build();
        public static Uri WITH_TAGS = CONTENT_URI.buildUpon().appendPath("tags").build();

        public static Uri buildProjectWithId(long id){
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }
    }

    public static final class TaskDependenciesEntry implements BaseColumns {
        public static final String TABLE_NAME = "task_dependencies";

        public static final String COLUMN_GRAPPBOX_ID = GENERAL_GRAPPBOX_ID;
        public static final String COLUMN_TYPE = "type";
        public static final String COLUMN_LOCAL_TASK_TO = "task_id_to";
        public static final String COLUMN_LOCAL_TASK_FROM = "task_id_from";

        public static Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_DEPENDENCIES).build();
    }

    public static final class TaskAssignationEntry implements BaseColumns {
        public static final String TABLE_NAME = "task_assignation";

        public static final String COLUMN_LOCAL_USER_ID  = "user_id";
        public static final String COLUMN_PERCENTAGE = "percentage";
        public static final String COLUMN_LOCAL_TASK = "task_id";

        public static Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_TASK_USER_ASSIGNATION).build();

        public static Uri buildProjectWithId(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }
    }

    public static final class TaskTagEntry implements BaseColumns {
        public static final String TABLE_NAME = "task_tags";

        public static final String COLUMN_PROJECT_ID = "project_id";
        public static final String COLUMN_GRAPPBOX_ID = GENERAL_GRAPPBOX_ID;
        public static final String COLUMN_NAME = "name";
        public static final String COLUMN_COLOR = "color";

        public static Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_TASK_TAGS).build();

        public static Uri buildProjectWithId(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }
    }

    public static final class TaskTagAssignationEntry implements BaseColumns {
        public static final String TABLE_NAME = "task_tag_assignation";

        public static final String COLUMN_LOCAL_TAG = "tag_id";
        public static final String COLUMN_LOCAL_TASK = "task_id";

        public static Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_TASK_TAGS_ASSIGNATION).build();

        public static Uri buildProjectWithId(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }
    }

    public static final class ProjectEntry implements BaseColumns {
        public static final String TABLE_NAME = "projects";

        public static final String COLUMN_GRAPPBOX_ID = GENERAL_GRAPPBOX_ID;
        public static final String COLUMN_NAME = "name";
        public static final String COLUMN_DESCRIPTION = "description";
        public static final String COLUMN_LOCAL_CREATOR_ID = "creator_id";
        public static final String COLUMN_CONTACT_PHONE = "contact_phone";
        public static final String COLUMN_CONTACT_EMAIL = "contact_email";
        public static final String COLUMN_COMPANY_NAME = "company_name";
        public static final String COLUMN_SOCIAL_FACEBOOK = "social_facebook";
        public static final String COLUMN_SOCIAL_TWITTER = "social_twitter";
        public static final String COLUMN_DATE_DELETED_UTC = "deleted_the";
        public static final String COLUMN_COUNT_BUG = "bug_count";
        public static final String COLUMN_COUNT_TASK = "task_count";
        public static final String COLUMN_URI_LOGO = "logo_local_uri";
        public static final String COLUMN_DATE_LOGO_LAST_EDITED_UTC = "date_avatar_last_edited";
        public static final String COLUMN_COLOR = "default_color";

        public static final Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_PROJECT).build();
        public static final String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_PROJECT;
        public static final String CONTENT_ITEM_TYPE = ContentResolver.CURSOR_ITEM_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_PROJECT;

        public static Uri buildProjectWithLocalIdUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }

        public static Uri buildWithoint(){
            return CONTENT_URI.buildUpon().appendPath("user").appendPath("0").build();
        }

        public static Uri buildProjectWithArgs(HashMap<String, String> args)
        {
            Uri.Builder projectUriBuilder = CONTENT_URI.buildUpon();

            for (String key : args.keySet())
                projectUriBuilder.appendQueryParameter(key, args.get(key));
            return projectUriBuilder.build();
        }
    }
    public static final class ProjectAccountEntry implements BaseColumns {
        public static final String TABLE_NAME = "project_account";
        public static final Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_PROJECT_ACCOUNT).build();
        public static final String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_PROJECT;
        public static final String CONTENT_ITEM_TYPE = ContentResolver.CURSOR_ITEM_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_PROJECT;

        public static final String COLUMN_PROJECT_LOCAL_ID = "project_id";
        public static final String COLUMN_ACCOUNT_NAME = "account_name";

        public static Uri buildProjectAccountWithLocalIdUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }
    }

    public static final class CustomerAccessEntry implements BaseColumns{
        public static final String TABLE_NAME = "customer_access";

        public static final String COLUMN_NAME = "name";
        public static final String COLUMN_PROJECT_ID = "project_id";
        public static final String COLUMN_GRAPPBOX_ID = "grappbox_id";
        public static final String COLUMN_TOKEN = "token";

        public static final Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_CUSTOMER_ACCESS).build();
        public static final String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_CUSTOMER_ACCESS;
        public static final String CONTENT_ITEM_TYPE = ContentResolver.CURSOR_ITEM_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_CUSTOMER_ACCESS;

        public static Uri buildCustomerAccessWithProject = CONTENT_URI.buildUpon().appendPath("project").build();

        public static Uri buildTimelineMessageWithLocalIdUri(long id){
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }
    }

    public static final class UserEntry implements BaseColumns {
        public static final String TABLE_NAME = "users";

        public static final String COLUMN_GRAPPBOX_ID = GENERAL_GRAPPBOX_ID;
        public static final String COLUMN_FIRSTNAME = "firstname";
        public static final String COLUMN_LASTNAME = "lastname";
        public static final String COLUMN_CONTACT_EMAIL = "contact_email";
        public static final String COLUMN_CONTACT_PHONE = "contact_phone";
        public static final String COLUMN_SOCIAL_LINKEDIN = "social_linkedin";
        public static final String COLUMN_SOCIAL_TWITTER = "social_twitter";
        public static final String COLUMN_DATE_BIRTHDAY_UTC = "date_birthday";
        public static final String COLUMN_COUNTRY = "country";
        public static final String COLUMN_URI_AVATAR = "avatar_local_uri";
        public static final String COLUMN_DATE_AVATAR_LAST_EDITED_UTC = "date_avatar_last_edited";
        public static final String COLUMN_PASSWORD = "password";
        public static final String COLUMN_TOKEN = "token";
        public static final String COLUMN_TOKEN_EXPIRATION = "token_expiration";

        public static final Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_USER).build();
        public static final String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_USER;
        public static final String CONTENT_ITEM_TYPE = ContentResolver.CURSOR_ITEM_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_USER;

        public static Uri buildUserWithLocalIdUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }

        public static Uri buildUserWithGrappboxIdUri(String id) {
            return CONTENT_URI.buildUpon().appendPath(id).build();
        }

        public static Uri buildUserWithProject(){
            return CONTENT_URI.buildUpon().appendPath("project").build();
        }

        public static Uri buildUserWithArgs(HashMap<String, String> args)
        {
            Uri.Builder projectUriBuilder = CONTENT_URI.buildUpon();

            for (String key : args.keySet())
                projectUriBuilder.appendQueryParameter(key, args.get(key));
            return projectUriBuilder.build();
        }
    }

    public static final class OccupationEntry implements BaseColumns {
        public static final String TABLE_NAME = "occupations";

        public static final String COLUMN_LOCAL_PROJECT_ID = "local_project_id";
        public static final String COLUMN_LOCAL_USER_ID = "local_user_id";
        public static final String COLUMN_IS_BUSY = "is_busy";
        public static final String COLUMN_COUNT_TASK_BEGUN = "count_task_begun";
        public static final String COLUMN_COUNT_TASK_ONGOING = "count_task_ongoing";

        public static final Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_OCCUPATION).build();
        public static final String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_OCCUPATION;
        public static final String CONTENT_ITEM_TYPE = ContentResolver.CURSOR_ITEM_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_OCCUPATION;

        public static Uri buildOccupationWithLocalIdUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }

        public static Uri buildOccupationWithArgs(HashMap<String, String> args)
        {
            Uri.Builder projectUriBuilder = CONTENT_URI.buildUpon();

            for (String key : args.keySet())
                projectUriBuilder.appendQueryParameter(key, args.get(key));
            return projectUriBuilder.build();
        }

        public static Uri buildOccupationWithUserAndProjectUri(long project_id, long user_id){
            return buildOccupationWithLocalIdUri(project_id).buildUpon().appendEncodedPath(Long.toString(user_id)).build();
        }

    }

    public static final class EventEntry implements BaseColumns {
        public static final String TABLE_NAME = "events";

        public static final String COLUMN_GRAPPBOX_ID = GENERAL_GRAPPBOX_ID;

        public static final String COLUMN_EVENT_TITLE = "title";
        public static final String COLUMN_EVENT_DESCRIPTION = "description";
        public static final String COLUMN_DATE_BEGIN_UTC = "begin_date";
        public static final String COLUMN_DATE_END_UTC = "end_date";
        public static final String COLUMN_LOCAL_PROJECT_ID = "project_id";
        public static final String COLUMN_LOCAL_CREATOR_ID = "creator_id";

        public static final Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_EVENT).build();
        public static final String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_EVENT;
        public static final String CONTENT_ITEM_TYPE = ContentResolver.CURSOR_ITEM_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_EVENT;

        public static Uri buildEventWithLocalIdUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }

        public static Uri buildEventWithArgs(HashMap<String, String> args)
        {
            Uri.Builder projectUriBuilder = CONTENT_URI.buildUpon();

            for (String key : args.keySet())
                projectUriBuilder.appendQueryParameter(key, args.get(key));
            return projectUriBuilder.build();
        }
    }

    public static final class EventParticipantEntry implements BaseColumns{
        public static final String TABLE_NAME = "event_participants";

        public static final String COLUMN_LOCAL_EVENT_ID = "event_id";
        public static final String COLUMN_LOCAL_USER_ID = "user_id";

        public static final Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_EVENT_PARTICIPANT).build();
        public static final String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_EVENT_PARTICIPANT;
        public static final String CONTENT_ITEM_TYPE = ContentResolver.CURSOR_ITEM_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_EVENT_PARTICIPANT;

        public static Uri buildEventParticipantWithLocalIdUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }
    }

    public static final class TimelineEntry implements BaseColumns {
        public static final String TABLE_NAME = "timeline";

        public static final String COLUMN_GRAPPBOX_ID = GENERAL_GRAPPBOX_ID;
        public static final String COLUMN_LOCAL_PROJECT_ID = "project_id";
        public static final String COLUMN_NAME = "name";
        public static final String COLUMN_TYPE_ID = "type_id";
        public static final String COLUMN_TYPE_NAME = "type_name";

        public static final Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_TIMELINE).build();
        public static final String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_TIMELINE;
        public static final String CONTENT_ITEM_TYPE = ContentResolver.CURSOR_ITEM_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_TIMELINE;

        public static Uri buildTimelineWithLocalIdUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }

        public static Uri buildTimelineWithArgs(HashMap<String, String> args)
        {
            Uri.Builder projectUriBuilder = CONTENT_URI.buildUpon();

            for (String key : args.keySet())
                projectUriBuilder.appendQueryParameter(key, args.get(key));
            return projectUriBuilder.build();
        }
    }

    public static final class TimelineMessageEntry implements BaseColumns {
        public static final String TABLE_NAME = "timeline_messages";

        public static final String COLUMN_GRAPPBOX_ID = GENERAL_GRAPPBOX_ID;
        public static final String COLUMN_LOCAL_TIMELINE_ID = "timeline_id";
        public static final String COLUMN_LOCAL_CREATOR_ID = "creator_id";
        public static final String COLUMN_PARENT_ID = "parent_id";
        public static final String COLUMN_TITLE = "title";
        public static final String COLUMN_MESSAGE = "message";
        public static final String COLUMN_DATE_LAST_EDITED_AT_UTC = "last_edited_at";
        public static final String COLUMN_COUNT_ANSWER = "nb_answer";

        public static final Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_TIMELINE_MESSAGES).build();
        public static final String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_TIMELINE_MESSAGES;
        public static final String CONTENT_ITEM_TYPE = ContentResolver.CURSOR_ITEM_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_TIMELINE_MESSAGES;

        public static Uri buildTimelineMessageWithLocalIdUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }

        public static Uri buildTimelineMessageWithArgs(HashMap<String, String> args)
        {
            Uri.Builder projectUriBuilder = CONTENT_URI.buildUpon();

            for (String key : args.keySet())
                projectUriBuilder.appendQueryParameter(key, args.get(key));
            return projectUriBuilder.build();
        }
    }

    public static final class TimelineCommentEntry implements BaseColumns {
        public static final String TABLE_NAME = "timeline_comments";

        public static final String COLUMN_GRAPPBOX_ID = GENERAL_GRAPPBOX_ID;
        public static final String COLUMN_LOCAL_TIMELINE_ID = "timeline_id";
        public static final String COLUMN_LOCAL_CREATOR_ID = "creator_id";
        public static final String COLUMN_PARENT_ID = "parent_id";
        public static final String COLUMN_MESSAGE = "message";
        public static final String COLUMN_DATE_LAST_EDITED_AT_UTC = "last_edited_at";

        public static final Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_TIMELINE_COMMENTS).build();
        public static final String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_TIMELINE_COMMENTS;
        public static final String CONTENT_ITEM_TYPE = ContentResolver.CURSOR_ITEM_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_TIMELINE_COMMENTS;

        public static Uri buildTimelineCommentWithLocalIdUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }

        public static Uri buildTimelineCommentWithArgs(HashMap<String, String> args)
        {
            Uri.Builder projectUriBuilder = CONTENT_URI.buildUpon();

            for (String key : args.keySet())
                projectUriBuilder.appendQueryParameter(key, args.get(key));
            return projectUriBuilder.build();
        }
    }

    public static final class RolesEntry implements BaseColumns {
        public static final String TABLE_NAME = "roles";

        public static final String COLUMN_GRAPPBOX_ID = GENERAL_GRAPPBOX_ID;
        public static final String COLUMN_NAME = "name";
        public static final String COLUMN_ACCESS_TEAM_TIMELINE = "team_timeline";
        public static final String COLUMN_ACCESS_CUSTOMER_TIMELINE = "customer_timeline";
        public static final String COLUMN_ACCESS_GANTT = "gantt";
        public static final String COLUMN_ACCESS_WHITEBOARD = "whiteboard";
        public static final String COLUMN_ACCESS_BUGTRACKER = "bugtracker";
        public static final String COLUMN_ACCESS_EVENT = "event";
        public static final String COLUMN_ACCESS_TASK = "task";
        public static final String COLUMN_ACCESS_PROJECT_SETTINGS = "project_settings";
        public static final String COLUMN_ACCESS_CLOUD = "cloud";
        public static final String COLUMN_LOCAL_PROJECT_ID = "project_id";

        public static final Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_ROLE).build();
        public static final String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_ROLE;
        public static final String CONTENT_ITEM_TYPE = ContentResolver.CURSOR_ITEM_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_ROLE;

        public static Uri buildRoleWithLocalIdUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }

        public static Uri buildRoleWithGrappboxProjectId(String projectId){
            return CONTENT_URI.buildUpon().appendPath("project").appendPath(projectId).build();
        }

        public static Uri buildRoleWithArgs(HashMap<String, String> args)
        {
            Uri.Builder projectUriBuilder = CONTENT_URI.buildUpon();

            for (String key : args.keySet())
                projectUriBuilder.appendQueryParameter(key, args.get(key));
            return projectUriBuilder.build();
        }
    }

    public static final class RolesAssignationEntry implements BaseColumns {
        public static final String TABLE_NAME = "role_assignations";

        public static final String COLUMN_LOCAL_ROLE_ID = "role_id";
        public static final String COLUMN_LOCAL_USER_ID = "user_id";

        public static final Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_ROLE_ASSIGNATION).build();
        public static final String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_ROLE_ASSIGNATION;
        public static final String CONTENT_ITEM_TYPE = ContentResolver.CURSOR_ITEM_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_ROLE_ASSIGNATION;

        public static Uri buildRoleAssignationWithLocalIdUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }

        public static Uri buildRoleAssignationWithUIDAndPID() {
            return CONTENT_URI.buildUpon().appendPath("projectuser").build();
        }
    }

    public static final class BugtrackerTagEntry implements BaseColumns {
        public static final String TABLE_NAME = "bugtracker_tags";

        public static final String COLUMN_GRAPPBOX_ID = GENERAL_GRAPPBOX_ID;
        public static final String COLUMN_LOCAL_PROJECT_ID = "project_id";
        public static final String COLUMN_NAME = "name";
        public static final String COLUMN_COLOR = "color";

        public static final Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_TAG).build();
        public static final String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_TAG;
        public static final String CONTENT_ITEM_TYPE = ContentResolver.CURSOR_ITEM_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_TAG;

        public static Uri buildTagWithLocalIdUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }
    }

    public static final class BugEntry implements BaseColumns {
        public static final String TABLE_NAME = "bug";

        public static final String COLUMN_GRAPPBOX_ID = GENERAL_GRAPPBOX_ID;
        public static final String COLUMN_LOCAL_PROJECT_ID = "project_id";
        public static final String COLUMN_LOCAL_CREATOR_ID = "creator_id";
        public static final String COLUMN_DATE_LAST_EDITED_UTC = "date_last_edited";
        public static final String COLUMN_DATE_DELETED_UTC = "date_deleted";
        public static final String COLUMN_TITLE = "title";
        public static final String COLUMN_DESCRIPTION = "description";
        public static final String COLUMN_LOCAL_PARENT_ID = "parent_id";
        public static final String COLUMN_IS_CLIENT_ORIGIN = "client_origin";

        public static final Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_BUG).build();
        public static final String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_BUG;
        public static final String CONTENT_ITEM_TYPE = ContentResolver.CURSOR_ITEM_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_BUG;

        public static Uri buildBugWithLocalIdUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }

        public static Uri buildBugWithAllJoin(){
            return CONTENT_URI.buildUpon().appendPath("tag_user").build();
        }

        public static  Uri buildBugWithAssignation(){
            return CONTENT_URI.buildUpon().appendPath("user_assignation").build();
        }

        public static Uri buildBugWithTag(){
            return CONTENT_URI.buildUpon().appendPath("tags").build();
        }

        public static Uri buildBugWithCreator(){
            return CONTENT_URI.buildUpon().appendEncodedPath("creator").build();
        }
    }

    public static final class BugTagEntry implements BaseColumns {
        public static final String TABLE_NAME = "bug_tag";

        public static final String COLUMN_LOCAL_BUG_ID = "bug_id";
        public static final String COLUMN_LOCAL_TAG_ID = "tag_id";

        public static final Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_BUG_TAG).build();
        public static final String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_BUG_TAG;
        public static final String CONTENT_ITEM_TYPE = ContentResolver.CURSOR_ITEM_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_BUG_TAG;

        public static Uri buildBugTagWithLocalIdUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }
    }

    public static final class BugAssignationEntry implements BaseColumns {
        public static final String TABLE_NAME = "bug_assignation";

        public static final String COLUMN_LOCAL_BUG_ID = "bug_id";
        public static final String COLUMN_LOCAL_USER_ID = "user_id";

        public static final Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_BUG_ASSIGNATION).build();
        public static final String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_BUG_ASSIGNATION;
        public static final String CONTENT_ITEM_TYPE = ContentResolver.CURSOR_ITEM_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_BUG_ASSIGNATION;

        public static Uri buildBugAssignationWithLocalIdUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }

        public static Uri buildBugWithUserId(long uid){
            return CONTENT_URI.buildUpon().appendPath("user").appendPath(String.valueOf(uid)).build();
        }
    }

    public static final class CloudEntry implements BaseColumns {
        public static final String TABLE_NAME = "cloud";

        public static final String COLUMN_FILENAME = "filename";
        public static final String COLUMN_TYPE = "type";
        public static final String COLUMN_SIZE = "size";
        public static final String COLUMN_MIMETYPE = "mimetype";
        public static final String COLUMN_PATH = "path";
        public static final String COLUMN_IS_SECURED = "is_secured";
        public static final String COLUMN_DATE_LAST_EDITED_UTC = "date_last_edited";
        public static final String COLUMN_LOCAL_PROJECT_ID = "project_id";

        public static final Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_CLOUD).build();
        public static final String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_CLOUD;
        public static final String CONTENT_ITEM_TYPE = ContentResolver.CURSOR_ITEM_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_CLOUD;

        public static Uri buildCloudWithLocalIdUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }

        public static Uri buildWithProjectJoin(){
            return CONTENT_URI.buildUpon().appendPath("withproject").build();
        }
    }

    public static final class StatEntry implements BaseColumns {
        public static final String TABLE_NAME = "stats";

        public static final String COLUMN_GRAPPBOX_ID = GENERAL_GRAPPBOX_ID;

        public static final String COLUMN_TIMELINE_TEAM_MESSAGE = "timeline_team_message_number";
        public static final String COLUMN_TIMELINE_CUSTOMER_MESSAGE = "timeline_customer_message_number";

        public static final String COLUMN_CUSTOMER_ACCESS_ACTUAL = "customer_access_actual_number";
        public static final String COLUMN_CUSTOMER_ACCESS_MAX = "customer_access_max_number";

        public static final String COLUMN_BUG_OPEN = "bug_open";
        public static final String COLUMN_BUG_CLOSE = "bug_close";

        public static final String COLUMN_TASK_DONE = "task_done";
        public static final String COLUMN_TASK_DOING = "task_doing";
        public static final String COLUMN_TASK_TODO = "task_todo";
        public static final String COLUMN_TASK_LATE = "task_late";
        public static final String COLUMN_TASK_TOTAL = "task_total";

        public static final String COLUMN_CLIENT_BUGTRACKER = "client_bugtracker";
        public static final String COLUMN_BUGTRACKER_ASSIGN = "bugtracker_assign";
        public static final String COLUMN_BUGTRACKER_UNASSIGN = "bugtracker_unassign";

        public static final String COLUMN_STORAGE_OCCUPIED = "storage_occupied";
        public static final String COLUMN_STORAGE_TOTAL = "storage_total";

        public static final String COLUMN_PROJECT_ID = "project_id";
        public static final String COLUMN_LOCAL_PROJECT_ID = "local_project_id";

        public static Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_STATS).build();
        public static String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_STATS;
        public static String CONTENT_ITEM_TYPE = ContentResolver.CURSOR_ITEM_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_STATS;

        public static Uri buildStatWithLocalIdUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }
    }

    public static final class AdvancementEntry implements BaseColumns {
        public static final String TABLE_NAME = "advancement";

        public static final String COLUMN_ADVANCEMENT_DATE = "advancement_date";
        public static final String COLUMN_PERCENTAGE = "percentage";
        public static final String COLUMN_PROGRESS = "progress";
        public static final String COLUMN_TOTAL_TASK = "total_task";
        public static final String COLUMN_FINISHED_TASk = "finished_task";

        public static final String COLUMN_LOCAL_STATS_ID = "local_stats_id";

        public static Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_ADVANCEMENT).build();
        public static String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_ADVANCEMENT;
        public static String CONTENT_ITEM_TYPE = ContentResolver.CURSOR_ITEM_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_ADVANCEMENT;

        public static Uri buildAdvancementWithLocalIdUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }
    }

    public static final class UserAdvancementTaskEntry implements BaseColumns {
        public static final String TABLE_NAME = "user_advancement_task";

        public static final String COLUMN_LOCAL_USER_ID = "user_id";
        public static final String COLUMN_TASK_TODO = "task_todo";
        public static final String COLUMN_TASK_DOING = "task_doing";
        public static final String COLUMN_TASK_DONE = "task_done";
        public static final String COLUMN_TASK_LATE = "task_late";
        public static final String COLUMN_LOCAL_STAT_ID = "local_stat_id";

        public static Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_USER_ADVANCEMENT_TASK).build();
        public static String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_USER_ADVANCEMENT_TASK;

        public static Uri builAdvancementTaskWithLocalUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }
    }

    public static final class LateTaskEntry implements BaseColumns {
        public static final String TABLE_NAME = "late_task";

        public static final String COLUMN_LOCAL_USER_ID = "user_id";
        public static final String COLUMN_LOCAL_STAT_ID = "stat_id";
        public static final String COLUMN_ROLE = "role";
        public static final String COLUMN_DATE = "date";
        public static final String COLUMN_LATE_TASK = "late_task";
        public static final String COLUMN_ON_TIME_TASK = "on_time_task";


        public static Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_LATE_TASK).build();
        public static String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_LATE_TASK;

        public static Uri buildLateTaskWithLocalUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }
    }

    public static final class UserWorkingChargeEntry implements BaseColumns {
        public static final String TABLE_NAME = "user_working_charge";

        public static final String COLUMN_LOCAL_USER_ID = "user_id";
        public static final String COLUMN_LOCAL_STAT_ID = "stat_id";
        public static final String COLUMN_CHARGE = "charge";

        public static Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_USER_WORKING_CHARGE).build();
        public static String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" +PATH_USER_WORKING_CHARGE;

        public static Uri buildUserWorkingChargeLocalUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }
    }

    public static final class TaskRepartitionEntry implements BaseColumns {
        public static final String TABLE_NAME = "task_repartition";

        public static final String COLUMN_LOCAL_USER_ID = "user_id";
        public static final String COLUMN_LOCAL_STAT_ID = "stat_id";
        public static final String COLUMN_VALUE = "value";
        public static final String COLUMN_PERCENTAGE = "percentage";

        public static Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_TASK_REPARTITION).build();
        public static String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_TASK_REPARTITION;

        public static Uri buildTaskRepartitionLocalUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }
    }

    public static final class BugUserRepartitionEntry implements BaseColumns {
        public static final String TABLE_NAME = "bug_user_repartition";

        public static final String COLUMN_LOCAL_USER_ID = "user_id";
        public static final String COLUMN_LOCAL_STAT_ID = "stat_id";
        public static final String COLUMN_VALUE = "value";
        public static final String COLUMN_PERCENTAGE = "percentage";

        public static Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_BUG_USER_REPARTITION).build();
        public static String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_BUG_USER_REPARTITION;

        public static Uri buildBugUserRepartitionLocalUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }
    }

    public static final class BugTagsRepartitionEntry implements BaseColumns {
        public static final String TABLE_NAME = "bug_tags_repartition";

        public static final String COLUMN_LOCAL_STAT_ID = "stat_id";
        public static final String COLUMN_NAME = "name";
        public static final String COLUMN_VALUE = "value";
        public static final String COLUMN_PERCENTAGE = "percentage";

        public static Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_BUG_TAGS_REPARTITION).build();
        public static String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_BUG_TAGS_REPARTITION;

        public static Uri buildBugTagsRepartitionlocalUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }
    }

    public static final class BugEvolutionEntry implements BaseColumns {
        public static final String TABLE_NAME = "bug_evolution";

        public static final String COLUMN_LOCAL_STAT_ID = "stat_id";
        public static final String COLUMN_DATE = "date";
        public static final String COLUMN_CREATED_BUG = "created_bug";
        public static final String COLUMN_CLOSED_BUG = "closed_bug";

        public static Uri CONTENT_URI = BASE_CONTENT_URI.buildUpon().appendPath(PATH_BUG_EVOLUTION).build();
        public static String CONTENT_TYPE = ContentResolver.CURSOR_DIR_BASE_TYPE + "/" + CONTENT_AUTHORITY + "/" + PATH_BUG_EVOLUTION;

        public static Uri buildBugEvolutionLocalUri(long id) {
            return ContentUris.withAppendedId(CONTENT_URI, id);
        }
    }
}
