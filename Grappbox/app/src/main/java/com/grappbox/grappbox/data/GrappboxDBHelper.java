package com.grappbox.grappbox.data;

import android.content.Context;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;
import android.util.Log;

import com.grappbox.grappbox.data.GrappboxContract.*;

/**
 * Created by Marc Wieser on 29/08/2016.
 * If you have any problem or question about this work
 * please contact the author at marc.wieser33@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */
public class GrappboxDBHelper extends SQLiteOpenHelper {
    private static final int DATABASE_VERSION  = 4;
    public static final String DATABASE_NAME = "grappbox.db";
    private Context mContext;

    public GrappboxDBHelper(Context context)
    {
        super(context, DATABASE_NAME, null, DATABASE_VERSION);
        mContext = context;
    }

    @Override
    public void onCreate(SQLiteDatabase db) {
        final String SQL_CREATE_USER_TABLE = "CREATE TABLE IF NOT EXISTS " + UserEntry.TABLE_NAME + " (" +
                UserEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                UserEntry.COLUMN_GRAPPBOX_ID + " TEXT NOT NULL, " +
                UserEntry.COLUMN_FIRSTNAME + " TEXT NOT NULL, " +
                UserEntry.COLUMN_LASTNAME + " TEXT NOT NULL, " +
                UserEntry.COLUMN_CONTACT_EMAIL + " TEXT, " +
                UserEntry.COLUMN_CONTACT_PHONE + " TEXT, " +
                UserEntry.COLUMN_SOCIAL_LINKEDIN + " TEXT, " +
                UserEntry.COLUMN_SOCIAL_TWITTER + " TEXT, " +
                UserEntry.COLUMN_DATE_BIRTHDAY_UTC + " TEXT, " +
                UserEntry.COLUMN_COUNTRY + " TEXT, " +
                UserEntry.COLUMN_URI_AVATAR + " TEXT, " +
                UserEntry.COLUMN_DATE_AVATAR_LAST_EDITED_UTC + " TEXT, " +
                UserEntry.COLUMN_PASSWORD + " TEXT, " +
                UserEntry.COLUMN_TOKEN + " TEXT, " +
                UserEntry.COLUMN_TOKEN_EXPIRATION + " TEXT, " +
                " UNIQUE (" + UserEntry.COLUMN_GRAPPBOX_ID + ", "+ UserEntry.COLUMN_CONTACT_EMAIL +") ON CONFLICT REPLACE);";

        final String SQL_CREATE_PROJECT_TABLE = "CREATE TABLE IF NOT EXISTS " + ProjectEntry.TABLE_NAME + " (" +
                ProjectEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                ProjectEntry.COLUMN_GRAPPBOX_ID + " TEXT NOT NULL, " +
                ProjectEntry.COLUMN_NAME + " TEXT NOT NULL, " +
                ProjectEntry.COLUMN_DESCRIPTION + " TEXT, " +
                ProjectEntry.COLUMN_LOCAL_CREATOR_ID + " INTEGER NOT NULL, " +
                ProjectEntry.COLUMN_CONTACT_PHONE + " TEXT, " +
                ProjectEntry.COLUMN_CONTACT_EMAIL + " TEXT, " +
                ProjectEntry.COLUMN_COMPANY_NAME + " TEXT, " +
                ProjectEntry.COLUMN_SOCIAL_FACEBOOK + " TEXT, " +
                ProjectEntry.COLUMN_SOCIAL_TWITTER + " TEXT, " +
                ProjectEntry.COLUMN_DATE_DELETED_UTC + " TEXT, " +
                ProjectEntry.COLUMN_COUNT_BUG + " INTEGER, " +
                ProjectEntry.COLUMN_COUNT_TASK + " INTEGER, " +
                ProjectEntry.COLUMN_URI_LOGO + " TEXT, " +
                ProjectEntry.COLUMN_DATE_LOGO_LAST_EDITED_UTC + " TEXT, " +
                ProjectEntry.COLUMN_COLOR + " TEXT, " +
                " FOREIGN KEY (" + ProjectEntry.COLUMN_LOCAL_CREATOR_ID + ") REFERENCES " + UserEntry.TABLE_NAME + " (" + UserEntry._ID + "), " +
                " UNIQUE (" + ProjectEntry.COLUMN_GRAPPBOX_ID + ") ON CONFLICT REPLACE);";

        final String SQL_CREATE_PROJECT_ACCOUNT_TABLE = "CREATE TABLE IF NOT EXISTS " + ProjectAccountEntry.TABLE_NAME + " (" +
                ProjectAccountEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                ProjectAccountEntry.COLUMN_ACCOUNT_NAME + " TEXT NOT NULL, " +
                ProjectAccountEntry.COLUMN_PROJECT_LOCAL_ID + " INTEGER NOT NULL, " +
                " FOREIGN KEY (" + ProjectAccountEntry.COLUMN_PROJECT_LOCAL_ID + ") REFERENCES " + ProjectEntry.TABLE_NAME + " (" + ProjectEntry._ID + "));";

        final String SQL_CREATE_OCCUPATION_TABLE = "CREATE TABLE IF NOT EXISTS " + OccupationEntry.TABLE_NAME + " (" +
                OccupationEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                OccupationEntry.COLUMN_LOCAL_PROJECT_ID + " INTEGER NOT NULL, " +
                OccupationEntry.COLUMN_LOCAL_USER_ID + " INTEGER NOT NULL, " +
                OccupationEntry.COLUMN_IS_BUSY + " INTEGER NOT NULL, " +
                OccupationEntry.COLUMN_COUNT_TASK_BEGUN + " INTEGER, " +
                OccupationEntry.COLUMN_COUNT_TASK_ONGOING + " INTEGER, " +
                " FOREIGN KEY (" + OccupationEntry.COLUMN_LOCAL_USER_ID + ") REFERENCES " + UserEntry.TABLE_NAME + " (" + UserEntry._ID + "), " +
                " FOREIGN KEY (" + OccupationEntry.COLUMN_LOCAL_PROJECT_ID + ") REFERENCES " + ProjectEntry.TABLE_NAME + " (" + ProjectEntry._ID + "));";

        final String SQL_CREATE_EVENT_TABLE = "CREATE TABLE IF NOT EXISTS " + EventEntry.TABLE_NAME + " (" +
                EventEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                EventEntry.COLUMN_GRAPPBOX_ID + " TEXT NOT NULL, " +
                EventEntry.COLUMN_EVENT_TITLE + " TEXT NOT NULL, " +
                EventEntry.COLUMN_EVENT_DESCRIPTION + " TEXT, " +
                EventEntry.COLUMN_DATE_BEGIN_UTC + " TEXT NOT NULL, " +
                EventEntry.COLUMN_DATE_END_UTC + " TEXT NOT NULL, " +
                EventEntry.COLUMN_LOCAL_PROJECT_ID + " INTEGER, " +
                EventEntry.COLUMN_LOCAL_CREATOR_ID + " INTEGER NOT NULL, " +
                " FOREIGN KEY (" + EventEntry.COLUMN_LOCAL_CREATOR_ID + ") REFERENCES " + UserEntry.TABLE_NAME + " (" + UserEntry._ID + "), " +
                " FOREIGN KEY (" + EventEntry.COLUMN_LOCAL_PROJECT_ID + ") REFERENCES " + ProjectEntry.TABLE_NAME + " (" + ProjectEntry._ID + "), " +
                " UNIQUE (" + EventEntry.COLUMN_GRAPPBOX_ID + ") ON CONFLICT REPLACE);";

        final String SQL_CREATE_EVENT_PARTICIPANT_TABLE = "CREATE TABLE IF NOT EXISTS " + EventParticipantEntry.TABLE_NAME + " (" +
                EventParticipantEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                EventParticipantEntry.COLUMN_LOCAL_EVENT_ID + " INTEGER NOT NULL, " +
                EventParticipantEntry.COLUMN_LOCAL_USER_ID + " INTEGER NOT NULL, " +
                " FOREIGN KEY (" + EventParticipantEntry.COLUMN_LOCAL_EVENT_ID + ") REFERENCES " + EventEntry.TABLE_NAME + " (" + EventEntry._ID + "), " +
                " FOREIGN KEY (" + EventParticipantEntry.COLUMN_LOCAL_USER_ID + ") REFERENCES " + UserEntry.TABLE_NAME + " (" + UserEntry._ID + "));";

        final String SQL_CREATE_TIMELINE_TABLE = "CREATE TABLE IF NOT EXISTS " + TimelineEntry.TABLE_NAME + " (" +
                TimelineEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                TimelineEntry.COLUMN_GRAPPBOX_ID + " TEXT NOT NULL, " +
                TimelineEntry.COLUMN_LOCAL_PROJECT_ID + " INTEGER NOT NULL, " +
                TimelineEntry.COLUMN_NAME + " TEXT, " +
                TimelineEntry.COLUMN_TYPE_ID + " TEXT, " +
                TimelineEntry.COLUMN_TYPE_NAME + " TEXT, " +
                "FOREIGN KEY (" + TimelineEntry.COLUMN_LOCAL_PROJECT_ID + ") REFERENCES " + ProjectEntry.TABLE_NAME + " (" + ProjectEntry._ID + "), " +
                " UNIQUE (" + TimelineEntry.COLUMN_GRAPPBOX_ID + ") ON CONFLICT REPLACE);";

        final String SQL_CREATE_TIMELINE_MESSAGE_TABLE = "CREATE TABLE IF NOT EXISTS " + TimelineMessageEntry.TABLE_NAME + " (" +
                TimelineMessageEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                TimelineMessageEntry.COLUMN_GRAPPBOX_ID + " TEXT NOT NULL, " +
                TimelineMessageEntry.COLUMN_LOCAL_TIMELINE_ID + " INTEGER NOT NULL, " +
                TimelineMessageEntry.COLUMN_LOCAL_CREATOR_ID + " INTEGER NOT NULL, " +
                TimelineMessageEntry.COLUMN_TITLE + " TEXT, " +
                TimelineMessageEntry.COLUMN_MESSAGE + " TEXT, " +
                TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC + " TEXT NOT NULL, " +
                TimelineMessageEntry.COLUMN_COUNT_ANSWER + " INTEGER NOT NULL, " +
                " FOREIGN KEY (" + TimelineMessageEntry.COLUMN_LOCAL_CREATOR_ID + ") REFERENCES " + UserEntry.TABLE_NAME + " (" + UserEntry._ID + "), " +
                " FOREIGN KEY (" + TimelineMessageEntry.COLUMN_LOCAL_TIMELINE_ID + ") REFERENCES " + TimelineEntry.TABLE_NAME + " (" + TimelineEntry._ID + "), " +
                " UNIQUE (" + TimelineMessageEntry.COLUMN_GRAPPBOX_ID + ") ON CONFLICT REPLACE);";

        final String SQL_CREATE_TIMELINE_COMMENTS_TABLE = "CREATE TABLE IF NOT EXISTS " + TimelineCommentEntry.TABLE_NAME + " (" +
                TimelineCommentEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                TimelineCommentEntry.COLUMN_GRAPPBOX_ID + " TEXT NOT NULL, " +
                TimelineCommentEntry.COLUMN_LOCAL_TIMELINE_ID + " INTEGER NOT NULL, " +
                TimelineCommentEntry.COLUMN_LOCAL_CREATOR_ID + " INTEGER NOT NULL, " +
                TimelineCommentEntry.COLUMN_PARENT_ID + " INTEGER NOT NULL, " +
                TimelineCommentEntry.COLUMN_MESSAGE + " TEXT, " +
                TimelineCommentEntry.COLUMN_DATE_LAST_EDITED_AT_UTC + " TEXT NOT NULL, " +
                " FOREIGN KEY (" + TimelineCommentEntry.COLUMN_LOCAL_CREATOR_ID + ") REFERENCES " + UserEntry.TABLE_NAME + " (" + UserEntry._ID + "), " +
                " FOREIGN KEY (" + TimelineCommentEntry.COLUMN_LOCAL_TIMELINE_ID + ") REFERENCES " + TimelineEntry.TABLE_NAME + " (" + TimelineEntry._ID + "), " +
                " UNIQUE (" + TimelineCommentEntry.COLUMN_GRAPPBOX_ID + ") ON CONFLICT REPLACE);";

        final String SQL_CREATE_ROLES_TABLE = "CREATE TABLE IF NOT EXISTS " + RolesEntry.TABLE_NAME + " (" +
                RolesEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                RolesEntry.COLUMN_GRAPPBOX_ID + " TEXT NOT NULL, " +
                RolesEntry.COLUMN_NAME + " TEXT NOT NULL, " +
                RolesEntry.COLUMN_ACCESS_TEAM_TIMELINE + " INTEGER NOT NULL, " +
                RolesEntry.COLUMN_ACCESS_CUSTOMER_TIMELINE + " INTEGER NOT NULL, " +
                RolesEntry.COLUMN_ACCESS_GANTT + " INTEGER NOT NULL, " +
                RolesEntry.COLUMN_ACCESS_WHITEBOARD + " INTEGER NOT NULL, " +
                RolesEntry.COLUMN_ACCESS_BUGTRACKER + " INTEGER NOT NULL, " +
                RolesEntry.COLUMN_ACCESS_EVENT + " INTEGER NOT NULL, " +
                RolesEntry.COLUMN_ACCESS_TASK + " INTEGER NOT NULL, " +
                RolesEntry.COLUMN_ACCESS_PROJECT_SETTINGS + " INTEGER NOT NULL, " +
                RolesEntry.COLUMN_ACCESS_CLOUD + " INTEGER NOT NULL, " +
                RolesEntry.COLUMN_LOCAL_PROJECT_ID + " INTEGER NOT NULL, " +
                " FOREIGN KEY (" + RolesEntry.COLUMN_LOCAL_PROJECT_ID + ") REFERENCES " + ProjectEntry.TABLE_NAME + " (" + ProjectEntry._ID + "), " +
                " UNIQUE (" + RolesEntry.COLUMN_GRAPPBOX_ID + ") ON CONFLICT REPLACE);";

        final String SQL_CREATE_ROLES_ASSIGNATIONS_TABLE = "CREATE TABLE IF NOT EXISTS " + RolesAssignationEntry.TABLE_NAME + " (" +
                RolesAssignationEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                RolesAssignationEntry.COLUMN_LOCAL_ROLE_ID + " INTEGER NOT NULL, " +
                RolesAssignationEntry.COLUMN_LOCAL_USER_ID + " INTEGER NOT NULL, " +
                " FOREIGN KEY (" + RolesAssignationEntry.COLUMN_LOCAL_USER_ID + ") REFERENCES " + UserEntry.TABLE_NAME + " (" + UserEntry._ID + "), " +
                " FOREIGN KEY (" + RolesAssignationEntry.COLUMN_LOCAL_ROLE_ID + ") REFERENCES " + RolesEntry.TABLE_NAME + " (" + RolesEntry._ID + "));";

        final String SQL_CREATE_BUGTRACKER_TAG_TABLE = "CREATE TABLE IF NOT EXISTS " + BugtrackerTagEntry.TABLE_NAME + " (" +
                BugtrackerTagEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                BugtrackerTagEntry.COLUMN_GRAPPBOX_ID + " TEXT NOT NULL, " +
                BugtrackerTagEntry.COLUMN_LOCAL_PROJECT_ID + " INTEGER NOT NULL, " +
                BugtrackerTagEntry.COLUMN_NAME + " TEXT NOT NULL, " +
                BugtrackerTagEntry.COLUMN_COLOR + " TEXT NOT NULL, " +
                " FOREIGN KEY (" + BugtrackerTagEntry.COLUMN_LOCAL_PROJECT_ID + ") REFERENCES " + ProjectEntry.TABLE_NAME + " (" + ProjectEntry._ID + "), " +
                " UNIQUE (" + BugtrackerTagEntry.COLUMN_GRAPPBOX_ID + ") ON CONFLICT REPLACE);";

        final String SQL_CREATE_BUG_TABLE = "CREATE TABLE IF NOT EXISTS " + BugEntry.TABLE_NAME + " (" +
                BugEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                BugEntry.COLUMN_GRAPPBOX_ID + " TEXT NOT NULL, " +
                BugEntry.COLUMN_LOCAL_PROJECT_ID + " INTEGER NOT NULL, " +
                BugEntry.COLUMN_LOCAL_CREATOR_ID + " INTEGER NOT NULL, " +
                BugEntry.COLUMN_DATE_LAST_EDITED_UTC + " TEXT NOT NULL, " +
                BugEntry.COLUMN_DATE_DELETED_UTC + " TEXT, " +
                BugEntry.COLUMN_TITLE + " TEXT NOT NULL, " +
                BugEntry.COLUMN_DESCRIPTION + " TEXT, " +
                BugEntry.COLUMN_LOCAL_PARENT_ID + " INTEGER, " +
                " FOREIGN KEY (" + BugEntry.COLUMN_LOCAL_PROJECT_ID + ") REFERENCES " + ProjectEntry.TABLE_NAME + " (" + ProjectEntry._ID + "), " +
                " FOREIGN KEY (" + BugEntry.COLUMN_LOCAL_CREATOR_ID + ") REFERENCES " + UserEntry.TABLE_NAME + " (" + UserEntry._ID + "), " +
                " UNIQUE (" + BugEntry.COLUMN_GRAPPBOX_ID + ") ON CONFLICT REPLACE);";

        final String SQL_CREATE_BUG_TAG_TABLE = "CREATE TABLE IF NOT EXISTS " + BugTagEntry.TABLE_NAME + " (" +
                BugTagEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                BugTagEntry.COLUMN_LOCAL_BUG_ID + " INTEGER NOT NULL, " +
                BugTagEntry.COLUMN_LOCAL_TAG_ID + " INTEGER NOT NULL, " +
                " FOREIGN KEY (" + BugTagEntry.COLUMN_LOCAL_BUG_ID + ") REFERENCES " + BugEntry.TABLE_NAME + " (" + BugEntry._ID + "), " +
                " FOREIGN KEY (" + BugTagEntry.COLUMN_LOCAL_TAG_ID + ") REFERENCES " + BugtrackerTagEntry.TABLE_NAME + " (" + BugtrackerTagEntry._ID + "));";

        final String SQL_CREATE_BUG_ASSIGNATION_TABLE = "CREATE TABLE IF NOT EXISTS " + BugAssignationEntry.TABLE_NAME + " (" +
                BugAssignationEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                BugAssignationEntry.COLUMN_LOCAL_BUG_ID + " INTEGER NOT NULL, " +
                BugAssignationEntry.COLUMN_LOCAL_USER_ID + " INTEGER NOT NULL, " +
                " FOREIGN KEY (" + BugAssignationEntry.COLUMN_LOCAL_BUG_ID + ") REFERENCES " + BugEntry.TABLE_NAME + " (" + BugEntry._ID + "), " +
                " FOREIGN KEY (" + BugAssignationEntry.COLUMN_LOCAL_USER_ID + ") REFERENCES " + UserEntry.TABLE_NAME + " (" + UserEntry._ID + "));";

        final String SQL_CREATE_CLOUD_TABLE = "CREATE TABLE IF NOT EXISTS " + CloudEntry.TABLE_NAME + " (" +
                CloudEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                CloudEntry.COLUMN_FILENAME + " TEXT NOT NULL, " +
                CloudEntry.COLUMN_TYPE + " INTEGER NOT NULL, " + //0 = file ; 1 = dir ; 2 = safe
                CloudEntry.COLUMN_PATH + " TEXT NOT NULL, " +
                CloudEntry.COLUMN_SIZE + " INTEGER, " +
                CloudEntry.COLUMN_MIMETYPE + " TEXT, " +
                CloudEntry.COLUMN_IS_SECURED + " INTEGER, " +
                CloudEntry.COLUMN_DATE_LAST_EDITED_UTC + " TEXT, "+
                CloudEntry.COLUMN_LOCAL_PROJECT_ID + " INTEGER NOT NULL, " +
                " FOREIGN KEY ("+CloudEntry.COLUMN_LOCAL_PROJECT_ID+") REFERENCES "+ProjectEntry.TABLE_NAME+" ("+ProjectEntry._ID+")); ";

        final String SQL_CREATE_STATS_TABLE = "CREATE TABLE IF NOT EXISTS " + StatEntry.TABLE_NAME + " (" +
                StatEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                StatEntry.COLUMN_GRAPPBOX_ID + " TEXT NOT NULL, " +
                StatEntry.COLUMN_TIMELINE_TEAM_MESSAGE + " INTEGER NOT NULL, " +
                StatEntry.COLUMN_TIMELINE_CUSTOMER_MESSAGE + " INTEGER NOT NULL, " +
                StatEntry.COLUMN_CUSTOMER_ACCESS_ACTUAL + " INTEGER NOT NULL, " +
                StatEntry.COLUMN_CUSTOMER_ACCESS_MAX + " INTEGER NOT NULL, " +
                StatEntry.COLUMN_BUG_OPEN + " INTEGER NOT NULL, " +
                StatEntry.COLUMN_BUG_CLOSE + " INTEGER NOT NULL, " +
                StatEntry.COLUMN_TASK_DONE + " INTEGER NOT NULL, " +
                StatEntry.COLUMN_TASK_DOING + " INTEGER NOT NULL, " +
                StatEntry.COLUMN_TASK_TODO + " INTEGER NOT NULL, " +
                StatEntry.COLUMN_TASK_LATE + " INTEGER NOT NULL, " +
                StatEntry.COLUMN_TASK_TOTAL + " INTEGER NOT NULL, " +
                StatEntry.COLUMN_CLIENT_BUGTRACKER + " INTEGER NOT NULL, " +
                StatEntry.COLUMN_BUGTRACKER_ASSIGN + " INTEGER NOT NULL, " +
                StatEntry.COLUMN_BUGTRACKER_UNASSIGN + " INTEGER NOT NULL, " +
                StatEntry.COLUMN_STORAGE_OCCUPIED + " INTEGER NOT NULL, " +
                StatEntry.COLUMN_STORAGE_TOTAL + " INTEGER NOT NULL, " +
                StatEntry.COLUMN_LOCAL_PROJECT_ID + " INTEGER NOT NULL, " +
                " UNIQUE (" + StatEntry.COLUMN_GRAPPBOX_ID + ") ON CONFLICT REPLACE);";

        final String SQL_CREATE_ADVANCEMENT_TABLE = "CREATE TABLE IF NOT EXISTS " + AdvancementEntry.TABLE_NAME + " (" +
                AdvancementEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                AdvancementEntry.COLUMN_ADVANCEMENT_DATE + " TEXT NOT NULL, " +
                AdvancementEntry.COLUMN_PERCENTAGE + " INTEGER NOT NULL, " +
                AdvancementEntry.COLUMN_PROGRESS + " INTEGER NOT NULL, " +
                AdvancementEntry.COLUMN_TOTAL_TASK + " INTEGER NOT NULL, " +
                AdvancementEntry.COLUMN_FINISHED_TASk + " INTEGER NOT NULL, " +
                AdvancementEntry.COLUMN_LOCAL_STATS_ID + " INTEGER NOT NULL, " +
                " FOREIGN KEY ("+AdvancementEntry.COLUMN_LOCAL_STATS_ID+") REFERENCES "+StatEntry.TABLE_NAME+" ("+StatEntry._ID+")); ";

        final String SQL_CREATE_ADVANCEMENT_TASK_TABLE = "CREATE TABLE IF NOT EXISTS " + UserAdvancementTaskEntry.TABLE_NAME + " (" +
                UserAdvancementTaskEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                UserAdvancementTaskEntry.COLUMN_TASK_TODO + " INTEGER NOT NULL, " +
                UserAdvancementTaskEntry.COLUMN_TASK_DOING + " INTEGER NOT NULL, " +
                UserAdvancementTaskEntry.COLUMN_TASK_DONE + " INTEGER NOT NULL, " +
                UserAdvancementTaskEntry.COLUMN_TASK_LATE + " INTEGER NOT NULL, " +
                UserAdvancementTaskEntry.COLUMN_LOCAL_STAT_ID + " INTEGER NOT NULL, " +
                UserAdvancementTaskEntry.COLUMN_LOCAL_USER_ID + " INTEGER NOT NULL, " +
                " FOREIGN KEY (" + UserAdvancementTaskEntry.COLUMN_LOCAL_STAT_ID + ") REFERENCES " + StatEntry.TABLE_NAME + " (" + StatEntry._ID + "), " +
                " FOREIGN KEY (" + UserAdvancementTaskEntry.COLUMN_LOCAL_USER_ID + ") REFERENCES " + UserEntry.TABLE_NAME + " (" + UserEntry._ID + "));";

        final String SQL_CREATE_LATE_TASK_TABLE = "CREATE TABLE IF NOT EXISTS " + LateTaskEntry.TABLE_NAME + " (" +
                LateTaskEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                LateTaskEntry.COLUMN_LOCAL_STAT_ID + " INTEGER NOT NULL, " +
                LateTaskEntry.COLUMN_LOCAL_USER_ID + " INTEGER NOT NULL, " +
                LateTaskEntry.COLUMN_ROLE + " STRING NOT NULL, " +
                LateTaskEntry.COLUMN_DATE + " STRING NOT NULL, " +
                LateTaskEntry.COLUMN_LATE_TASK + " INTEGER NOT NULL, " +
                LateTaskEntry.COLUMN_ON_TIME_TASK + " INTEGER NOT NULL, " +
                " FOREIGN KEY (" + LateTaskEntry.COLUMN_LOCAL_STAT_ID + ") REFERENCES " + StatEntry.TABLE_NAME + " (" + StatEntry._ID + "), " +
                " FOREIGN KEY (" + LateTaskEntry.COLUMN_LOCAL_USER_ID + ") REFERENCES " + UserEntry.TABLE_NAME + " (" + UserEntry._ID + "));";

        final String SQL_CREATE_USER_WORKING_CHARGE_TASK_TABLE = "CREATE TABLE IF NOT EXISTS " + UserWorkingChargeEntry.TABLE_NAME + " (" +
                UserWorkingChargeEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                UserWorkingChargeEntry.COLUMN_LOCAL_STAT_ID + " INTEGER NOT NULL, " +
                UserWorkingChargeEntry.COLUMN_LOCAL_USER_ID + " INTEGER NOT NULL, " +
                UserWorkingChargeEntry.COLUMN_CHARGE + " INTEGER NOT NULL, " +
                " FOREIGN KEY (" + UserWorkingChargeEntry.COLUMN_LOCAL_STAT_ID + ") REFERENCES " + StatEntry.TABLE_NAME + " (" + StatEntry._ID + "), " +
                " FOREIGN KEY (" + UserWorkingChargeEntry.COLUMN_LOCAL_USER_ID + ") REFERENCES " + UserEntry.TABLE_NAME + " (" + UserEntry._ID + "));";

        final String SQL_CREATE_TASK_REPARTITION_TABLE = "CREATE TABLE IF NOT EXISTS " + TaskRepartitionEntry.TABLE_NAME + " (" +
                TaskRepartitionEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                TaskRepartitionEntry.COLUMN_LOCAL_STAT_ID + " INTEGER NOT NULL, " +
                TaskRepartitionEntry.COLUMN_LOCAL_USER_ID + " INTEGER NOT NULL, " +
                TaskRepartitionEntry.COLUMN_VALUE + " INTEGER NOT NULL, " +
                TaskRepartitionEntry.COLUMN_PERCENTAGE + " INTEGER NOT NULL, " +
                " FOREIGN KEY (" + TaskRepartitionEntry.COLUMN_LOCAL_STAT_ID + ") REFERENCES " + StatEntry.TABLE_NAME + " (" + StatEntry._ID + "), " +
                " FOREIGN KEY (" + TaskRepartitionEntry.COLUMN_LOCAL_USER_ID + ") REFERENCES " + UserEntry.TABLE_NAME + " (" + UserEntry._ID + "));";

        final String SQL_CREATE_BUG_USER_REPARTITION_TABLE = "CREATE TABLE IF NOT EXISTS " + BugUserRepartitionEntry.TABLE_NAME + " (" +
                BugUserRepartitionEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                BugUserRepartitionEntry.COLUMN_LOCAL_STAT_ID + " INTEGER NOT NULL, " +
                BugUserRepartitionEntry.COLUMN_LOCAL_USER_ID + " INTEGER NOT NULL, " +
                BugUserRepartitionEntry.COLUMN_VALUE + " INTEGER NOT NULL, " +
                BugUserRepartitionEntry.COLUMN_PERCENTAGE + " INTEGER NOT NULL, " +
                " FOREIGN KEY (" + BugUserRepartitionEntry.COLUMN_LOCAL_STAT_ID + ") REFERENCES " + StatEntry.TABLE_NAME + " (" + StatEntry._ID + "), " +
                " FOREIGN KEY (" + BugUserRepartitionEntry.COLUMN_LOCAL_USER_ID + ") REFERENCES " + UserEntry.TABLE_NAME + " (" + UserEntry._ID + "));";

        final String SQL_CREATE_BUG_TAGS_REPARTITION_TABLE = "CREATE TABLE IF NOT EXISTS " + BugTagsRepartitionEntry.TABLE_NAME + " (" +
                BugTagsRepartitionEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                BugTagsRepartitionEntry.COLUMN_LOCAL_STAT_ID + " INTEGER NOT NULL, " +
                BugTagsRepartitionEntry.COLUMN_NAME + " TEXT, " +
                BugTagsRepartitionEntry.COLUMN_VALUE + " INTEGER NOT NULL, " +
                BugTagsRepartitionEntry.COLUMN_PERCENTAGE + " INTEGER NOT NULL, " +
                " FOREIGN KEY (" + BugTagsRepartitionEntry.COLUMN_LOCAL_STAT_ID + ") REFERENCES " + StatEntry.TABLE_NAME + " (" + StatEntry._ID + "));";

        final String SQL_CREATE_BUG_EVOLUTION_TABLE = "CREATE TABLE IF NOT EXISTS " + BugEvolutionEntry.TABLE_NAME + " (" +
                BugEvolutionEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                BugEvolutionEntry.COLUMN_LOCAL_STAT_ID + " INTEGER NOT NULL, " +
                BugEvolutionEntry.COLUMN_DATE + " TEXT NOT NULL, " +
                BugEvolutionEntry.COLUMN_CREATED_BUG + " INTEGER NOT NULL, " +
                BugEvolutionEntry.COLUMN_CLOSED_BUG + " INTEGER NOT NULL, " +
                " FOREIGN KEY (" + BugEvolutionEntry.COLUMN_LOCAL_STAT_ID + ") REFERENCES " + StatEntry.TABLE_NAME + " (" + StatEntry._ID + "));";

        db.execSQL(SQL_CREATE_USER_TABLE);
        db.execSQL(SQL_CREATE_PROJECT_TABLE);
        db.execSQL(SQL_CREATE_PROJECT_ACCOUNT_TABLE);
        db.execSQL(SQL_CREATE_OCCUPATION_TABLE);
        db.execSQL(SQL_CREATE_EVENT_TABLE);
        db.execSQL(SQL_CREATE_EVENT_PARTICIPANT_TABLE);
        db.execSQL(SQL_CREATE_TIMELINE_TABLE);
        db.execSQL(SQL_CREATE_TIMELINE_MESSAGE_TABLE);
        db.execSQL(SQL_CREATE_TIMELINE_COMMENTS_TABLE);
        db.execSQL(SQL_CREATE_ROLES_TABLE);
        db.execSQL(SQL_CREATE_ROLES_ASSIGNATIONS_TABLE);
        db.execSQL(SQL_CREATE_BUGTRACKER_TAG_TABLE);
        db.execSQL(SQL_CREATE_BUG_TABLE);
        db.execSQL(SQL_CREATE_BUG_TAG_TABLE);
        db.execSQL(SQL_CREATE_BUG_ASSIGNATION_TABLE);
        db.execSQL(SQL_CREATE_CLOUD_TABLE);
        db.execSQL(SQL_CREATE_STATS_TABLE);
        db.execSQL(SQL_CREATE_ADVANCEMENT_TABLE);
        db.execSQL(SQL_CREATE_ADVANCEMENT_TASK_TABLE);
        db.execSQL(SQL_CREATE_LATE_TASK_TABLE);
        db.execSQL(SQL_CREATE_USER_WORKING_CHARGE_TASK_TABLE);
        db.execSQL(SQL_CREATE_TASK_REPARTITION_TABLE);
        db.execSQL(SQL_CREATE_BUG_USER_REPARTITION_TABLE);
        db.execSQL(SQL_CREATE_BUG_TAGS_REPARTITION_TABLE);
        db.execSQL(SQL_CREATE_BUG_EVOLUTION_TABLE);
    }

    @Override
    public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {
        Log.e("DB", "DB delete : " + String.valueOf(mContext.getApplicationContext().deleteDatabase(DATABASE_NAME)));

        onCreate(db);
    }
}
