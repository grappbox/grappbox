package com.grappbox.grappbox.data;

import android.content.ContentProvider;
import android.content.ContentResolver;
import android.content.ContentUris;
import android.content.ContentValues;
import android.net.Uri;
import android.provider.BaseColumns;

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
    public static final String PATH_EVENT_TYPE = "event_types";
    public static final String PATH_EVENT_PARTICIPANT = "event_participant";
    public static final String PATH_TIMELINE = "timeline";
    public static final String PATH_TIMELINE_MESSAGES = "timline_messages";
    public static final String PATH_ROLE = "roles";
    public static final String PATH_ROLE_ASSIGNATION = "role_assignations";
    public static final String PATH_TAG  = "tag";
    public static final String PATH_BUG = "bug";
    public static final String PATH_BUG_TAG = "bug_tag";
    public static final String PATH_BUG_ASSIGNATION = "bug_assignations";
    public static final String PATH_CLOUD = "cloud";
    public static final String PATH_CUSTOMER_ACCESS = "customer_access";

    public static final String GENERAL_GRAPPBOX_ID = "grappbox_id";

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
        public static final String COLUMN_PARENT_ID = "parent_id"; //This is set only when message is an answer to another message
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
}
