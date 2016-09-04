package com.grappbox.grappbox.data;

import android.content.Context;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;
import android.nfc.Tag;

import com.grappbox.grappbox.data.GrappboxContract.*;

/**
 * Created by marcw on 29/08/2016.
 */
public class GrappboxDBHelper extends SQLiteOpenHelper {
    private static final int DATABASE_VERSION  = 1;
    public static final String DATABASE_NAME = "grappbox.db";

    public GrappboxDBHelper(Context context)
    {
        super(context, DATABASE_NAME, null, DATABASE_VERSION);
    }

    @Override
    public void onCreate(SQLiteDatabase db) {
        final String SQL_CREATE_USER_TABLE = "CREATE TABLE " + UserEntry.TABLE_NAME + " (" +
                UserEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                UserEntry.COLUMN_GRAPPBOX_ID + " TEXT NOT NULL, " +
                UserEntry.COLUMN_FIRSTNAME + " TEXT NOT NULL, " +
                UserEntry.COLUMN_LASTNAME + " TEXT NOT NULL, " +
                UserEntry.COLUMN_CONTACT_EMAIL + " TEXT, " +
                UserEntry.COLUMN_CONTACT_PHONE + " TEXT, " +
                UserEntry.COLUMN_SOCIAL_LINKEDIN + " TEXT, " +
                UserEntry.COLUMN_SOCIAL_TWITTER + " TEXT, " +
                UserEntry.COLUMN_DATE_BIRTHDAY_UTC + " INTEGER, " +
                UserEntry.COLUMN_COUNTRY + " TEXT, " +
                UserEntry.COLUMN_URI_AVATAR + " TEXT, " +
                UserEntry.COLUMN_DATE_AVATAR_LAST_EDITED_UTC + " INTEGER, " +
                UserEntry.COLUMN_PASSWORD + " TEXT, " +
                UserEntry.COLUMN_TOKEN + " TEXT, " +
                UserEntry.COLUMN_TOKEN_EXPIRATION + " TEXT, " +
                " UNIQUE (" + UserEntry.COLUMN_GRAPPBOX_ID + ", "+ UserEntry.COLUMN_CONTACT_EMAIL +") ON CONFLICT REPLACE);";

        final String SQL_CREATE_PROJECT_TABLE = "CREATE TABLE " + ProjectEntry.TABLE_NAME + " (" +
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
                ProjectEntry.COLUMN_DATE_DELETED_UTC + " INTEGER, " +
                ProjectEntry.COLUMN_COUNT_BUG + " INTEGER, " +
                ProjectEntry.COLUMN_COUNT_TASK + " INTEGER, " +
                ProjectEntry.COLUMN_URI_LOGO + " TEXT, " +
                ProjectEntry.COLUMN_DATE_LOGO_LAST_EDITED_UTC + " INTEGER, " +
                ProjectEntry.COLUMN_COLOR + " TEXT, " +
                " FOREIGN KEY (" + ProjectEntry.COLUMN_LOCAL_CREATOR_ID + ") REFERENCES " + UserEntry.TABLE_NAME + " (" + UserEntry._ID + "), " +
                " UNIQUE (" + ProjectEntry.COLUMN_GRAPPBOX_ID + ") ON CONFLICT REPLACE);";

        final String SQL_CREATE_OCCUPATION_TABLE = "CREATE TABLE " + OccupationEntry.TABLE_NAME + " (" +
                OccupationEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                OccupationEntry.COLUMN_LOCAL_PROJECT_ID + " INTEGER NOT NULL, " +
                OccupationEntry.COLUMN_LOCAL_USER_ID + " INTEGER NOT NULL, " +
                OccupationEntry.COLUMN_IS_BUSY + " INTEGER NOT NULL, " +
                OccupationEntry.COLUMN_COUNT_TASK_BEGUN + " INTEGER, " +
                OccupationEntry.COLUMN_COUNT_TASK_ONGOING + " INTEGER, " +
                " FOREIGN KEY (" + OccupationEntry.COLUMN_LOCAL_USER_ID + ") REFERENCES " + UserEntry.TABLE_NAME + " (" + UserEntry._ID + "), " +
                " FOREIGN KEY (" + OccupationEntry.COLUMN_LOCAL_PROJECT_ID + ") REFERENCES " + ProjectEntry.TABLE_NAME + " (" + ProjectEntry._ID + "));";

        final String SQL_CREATE_EVENT_TYPE_TABLE = "CREATE TABLE " +  EventTypeEntry.TABLE_NAME + " (" +
                EventTypeEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                EventTypeEntry.COLUMN_GRAPPBOX_ID + " TEXT NOT NULL, " +
                EventTypeEntry.COLUMN_NAME + " TEXT NOT NULL, " +
                " UNIQUE (" + EventTypeEntry.COLUMN_GRAPPBOX_ID + ") ON CONFLICT REPLACE);";

        final String SQL_CREATE_EVENT_TABLE = "CREATE TABLE " + EventEntry.TABLE_NAME + " (" +
                EventEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                EventEntry.COLUMN_GRAPPBOX_ID + " TEXT NOT NULL, " +
                EventEntry.COLUMN_EVENT_TITLE + " TEXT NOT NULL, " +
                EventEntry.COLUMN_EVENT_DESCRIPTION + " TEXT, " +
                EventEntry.COLUMN_DATE_BEGIN_UTC + " INTEGER NOT NULL, " +
                EventEntry.COLUMN_DATE_END_UTC + " INTEGER NOT NULL, " +
                EventEntry.COLUMN_LOCAL_EVENT_TYPE_ID + " INTEGER NOT NULL, " +
                EventEntry.COLUMN_LOCAL_PROJECT_ID + " INTEGER NOT NULL, " +
                EventEntry.COLUMN_LOCAL_CREATOR_ID + " INTEGER NOT NULL, " +
                " FOREIGN KEY (" + EventEntry.COLUMN_LOCAL_CREATOR_ID + ") REFERENCES " + UserEntry.TABLE_NAME + " (" + UserEntry._ID + "), " +
                " FOREIGN KEY (" + EventEntry.COLUMN_LOCAL_PROJECT_ID + ") REFERENCES " + ProjectEntry.TABLE_NAME + " (" + ProjectEntry._ID + "), " +
                " FOREIGN KEY (" + EventEntry.COLUMN_LOCAL_EVENT_TYPE_ID + ") REFERENCES " + EventTypeEntry.TABLE_NAME + " (" + EventTypeEntry._ID + "), " +
                " UNIQUE (" + EventEntry.COLUMN_GRAPPBOX_ID + ") ON CONFLICT REPLACE);";

        final String SQL_CREATE_EVENT_PARTICIPANT_TABLE = "CREATE TABLE " + EventParticipantEntry.TABLE_NAME + " (" +
                EventParticipantEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                EventParticipantEntry.COLUMN_LOCAL_EVENT_ID + " INTEGER NOT NULL, " +
                EventParticipantEntry.COLUMN_LOCAL_USER_ID + " INTEGER NOT NULL, " +
                " FOREIGN KEY (" + EventParticipantEntry.COLUMN_LOCAL_EVENT_ID + ") REFERENCES " + EventEntry.TABLE_NAME + " (" + EventEntry._ID + "), " +
                " FOREIGN KEY (" + EventParticipantEntry.COLUMN_LOCAL_USER_ID + ") REFERENCES " + UserEntry.TABLE_NAME + " (" + UserEntry._ID + "));";

        final String SQL_CREATE_TIMELINE_TABLE = "CREATE TABLE " + TimelineEntry.TABLE_NAME + " (" +
                TimelineEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                TimelineEntry.COLUMN_GRAPPBOX_ID + " TEXT NOT NULL, " +
                TimelineEntry.COLUMN_LOCAL_PROJECT_ID + " INTEGER NOT NULL, " +
                TimelineEntry.COLUMN_NAME + " TEXT, " +
                TimelineEntry.COLUMN_TYPE_ID + " TEXT, " +
                TimelineEntry.COLUMN_TYPE_NAME + " TEXT, " +
                "FOREIGN KEY (" + TimelineEntry.COLUMN_LOCAL_PROJECT_ID + ") REFERENCES " + ProjectEntry.TABLE_NAME + " (" + ProjectEntry._ID + "), " +
                " UNIQUE (" + TimelineEntry.COLUMN_GRAPPBOX_ID + ") ON CONFLICT REPLACE);";

        final String SQL_CREATE_TIMELINE_MESSAGE_TABLE = "CREATE TABLE " + TimelineMessageEntry.TABLE_NAME + " (" +
                TimelineMessageEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                TimelineMessageEntry.COLUMN_GRAPPBOX_ID + " TEXT NOT NULL, " +
                TimelineMessageEntry.COLUMN_LOCAL_TIMELINE_ID + " INTEGER NOT NULL, " +
                TimelineMessageEntry.COLUMN_LOCAL_CREATOR_ID + " INTEGER NOT NULL, " +
                TimelineMessageEntry.COLUMN_PARENT_ID + " TEXT NOT NULL, " +
                TimelineMessageEntry.COLUMN_TITLE + " TEXT NOT NULL, " +
                TimelineMessageEntry.COLUMN_MESSAGE + " TEXT, " +
                TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC + " INTEGER NOT NULL, " +
                TimelineMessageEntry.COLUMN_COUNT_ANSWER + " INTEGER NOT NULL, " +
                " FOREIGN KEY (" + TimelineMessageEntry.COLUMN_LOCAL_CREATOR_ID + ") REFERENCES " + UserEntry.TABLE_NAME + " (" + UserEntry._ID + "), " +
                " FOREIGN KEY (" + TimelineMessageEntry.COLUMN_LOCAL_TIMELINE_ID + ") REFERENCES " + TimelineEntry.TABLE_NAME + " (" + TimelineEntry._ID + "), " +
                " UNIQUE (" + TimelineMessageEntry.COLUMN_GRAPPBOX_ID + ") ON CONFLICT REPLACE);";

        final String SQL_CREATE_ROLES_TABLE = "CREATE TABLE " + RolesEntry.TABLE_NAME + " (" +
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

        final String SQL_CREATE_ROLES_ASSIGNATIONS_TABLE = "CREATE TABLE " + RolesAssignationEntry.TABLE_NAME + " (" +
                RolesAssignationEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                RolesAssignationEntry.COLUMN_LOCAL_ROLE_ID + " INTEGER NOT NULL, " +
                RolesAssignationEntry.COLUMN_LOCAL_USER_ID + " INTEGER NOT NULL, " +
                " FOREIGN KEY (" + RolesAssignationEntry.COLUMN_LOCAL_USER_ID + ") REFERENCES " + UserEntry.TABLE_NAME + " (" + UserEntry._ID + "), " +
                " FOREIGN KEY (" + RolesAssignationEntry.COLUMN_LOCAL_ROLE_ID + ") REFERENCES " + RolesEntry.TABLE_NAME + " (" + RolesEntry._ID + "));";

        final String SQL_CREATE_TAG_TABLE = "CREATE TABLE " + TagEntry.TABLE_NAME + " (" +
                TagEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                TagEntry.COLUMN_GRAPPBOX_ID + " TEXT NOT NULL, " +
                TagEntry.COLUMN_LOCAL_PROJECT_ID + " INTEGER NOT NULL, " +
                TagEntry.COLUMN_NAME + " TEXT NOT NULL, " +
                " FOREIGN KEY (" + TagEntry.COLUMN_LOCAL_PROJECT_ID + ") REFERENCES " + ProjectEntry.TABLE_NAME + " (" + ProjectEntry._ID + "), " +
                " UNIQUE (" + TagEntry.COLUMN_GRAPPBOX_ID + ") ON CONFLICT REPLACE);";

        final String SQL_CREATE_BUG_TABLE = "CREATE TABLE " + BugEntry.TABLE_NAME + " (" +
                BugEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                BugEntry.COLUMN_GRAPPBOX_ID + " TEXT NOT NULL, " +
                BugEntry.COLUMN_LOCAL_PROJECT_ID + " INTEGER NOT NULL, " +
                BugEntry.COLUMN_LOCAL_CREATOR_ID + " INTEGER NOT NULL, " +
                BugEntry.COLUMN_DATE_LAST_EDITED_UTC + " INTEGER NOT NULL, " +
                BugEntry.COLUMN_DATE_DELETED_UTC + " INTEGER, " +
                BugEntry.COLUMN_TITLE + " TEXT NOT NULL, " +
                BugEntry.COLUMN_DESCRIPTION + " TEXT, " +
                BugEntry.COLUMN_LOCAL_PARENT_ID + " INTEGER, " +
                " FOREIGN KEY (" + BugEntry.COLUMN_LOCAL_PROJECT_ID + ") REFERENCES " + ProjectEntry.TABLE_NAME + " (" + ProjectEntry._ID + "), " +
                " FOREIGN KEY (" + BugEntry.COLUMN_LOCAL_CREATOR_ID + ") REFERENCES " + UserEntry.TABLE_NAME + " (" + UserEntry._ID + "), " +
                " UNIQUE (" + BugEntry.COLUMN_GRAPPBOX_ID + ") ON CONFLICT REPLACE);";

        final String SQL_CREATE_BUG_TAG_TABLE = "CREATE TABLE " + BugTagEntry.TABLE_NAME + " (" +
                BugTagEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                BugTagEntry.COLUMN_LOCAL_BUG_ID + " INTEGER NOT NULL, " +
                BugTagEntry.COLUMN_LOCAL_TAG_ID + " INTEGER NOT NULL, " +
                " FOREIGN KEY (" + BugTagEntry.COLUMN_LOCAL_BUG_ID + ") REFERENCES " + BugEntry.TABLE_NAME + " (" + BugEntry._ID + "), " +
                " FOREIGN KEY (" + BugTagEntry.COLUMN_LOCAL_TAG_ID + ") REFERENCES " + TagEntry.TABLE_NAME + " (" + TagEntry._ID + "));";

        final String SQL_CREATE_BUG_ASSIGNATION_TABLE = "CREATE TABLE " + BugTagEntry.TABLE_NAME + " (" +
                BugAssignationEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                BugAssignationEntry.COLUMN_LOCAL_BUG_ID + " INTEGER NOT NULL, " +
                BugAssignationEntry.COLUMN_LOCAL_USER_ID + " INTEGER NOT NULL, " +
                " FOREIGN KEY (" + BugAssignationEntry.COLUMN_LOCAL_BUG_ID + ") REFERENCES " + BugEntry.TABLE_NAME + " (" + BugEntry._ID + "), " +
                " FOREIGN KEY (" + BugAssignationEntry.COLUMN_LOCAL_USER_ID + ") REFERENCES " + UserEntry.TABLE_NAME + " (" + UserEntry._ID + "));";

        final String SQL_CREATE_CLOUD_TABLE = "CREATE TABLE " + CloudEntry.TABLE_NAME + " (" +
                CloudEntry._ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                CloudEntry.COLUMN_FILENAME + " TEXT NOT NULL, " +
                CloudEntry.COLUMN_PATH + " TEXT NOT NULL, " +
                CloudEntry.COLUMN_SIZE + " INTEGER NOT NULL, " +
                CloudEntry.COLUMN_MIMETYPE + " TEXT, " +
                CloudEntry.COLUMN_IS_SECURED + " INTEGER NOT NULL, " +
                CloudEntry.COLUMN_DATE_LAST_EDITED_UTC + " INTEGER); ";

        db.execSQL(SQL_CREATE_USER_TABLE);
        db.execSQL(SQL_CREATE_PROJECT_TABLE);
        db.execSQL(SQL_CREATE_OCCUPATION_TABLE);
        db.execSQL(SQL_CREATE_EVENT_TYPE_TABLE);
        db.execSQL(SQL_CREATE_EVENT_TABLE);
        db.execSQL(SQL_CREATE_EVENT_PARTICIPANT_TABLE);
        db.execSQL(SQL_CREATE_TIMELINE_TABLE);
        db.execSQL(SQL_CREATE_TIMELINE_MESSAGE_TABLE);
        db.execSQL(SQL_CREATE_ROLES_TABLE);
        db.execSQL(SQL_CREATE_ROLES_ASSIGNATIONS_TABLE);
        db.execSQL(SQL_CREATE_TAG_TABLE);
        db.execSQL(SQL_CREATE_BUG_TABLE);
        db.execSQL(SQL_CREATE_BUG_TAG_TABLE);
        db.execSQL(SQL_CREATE_BUG_ASSIGNATION_TABLE);
        db.execSQL(SQL_CREATE_CLOUD_TABLE);
    }

    @Override
    public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {
        db.execSQL("DROP TABLE IF EXISTS " + ProjectEntry.TABLE_NAME);
        db.execSQL("DROP TABLE IF EXISTS " + UserEntry.TABLE_NAME);
        db.execSQL("DROP TABLE IF EXISTS " + OccupationEntry.TABLE_NAME);
        db.execSQL("DROP TABLE IF EXISTS " + EventTypeEntry.TABLE_NAME);
        db.execSQL("DROP TABLE IF EXISTS " + EventEntry.TABLE_NAME);
        db.execSQL("DROP TABLE IF EXISTS " + EventParticipantEntry.TABLE_NAME);
        db.execSQL("DROP TABLE IF EXISTS " + TimelineEntry.TABLE_NAME);
        db.execSQL("DROP TABLE IF EXISTS " + TimelineMessageEntry.TABLE_NAME);
        db.execSQL("DROP TABLE IF EXISTS " + RolesEntry.TABLE_NAME);
        db.execSQL("DROP TABLE IF EXISTS " + RolesAssignationEntry.TABLE_NAME);
        db.execSQL("DROP TABLE IF EXISTS " + TagEntry.TABLE_NAME);
        db.execSQL("DROP TABLE IF EXISTS " + BugEntry.TABLE_NAME);
        db.execSQL("DROP TABLE IF EXISTS " + BugTagEntry.TABLE_NAME);
        db.execSQL("DROP TABLE IF EXISTS " + BugAssignationEntry.TABLE_NAME);
        db.execSQL("DROP TABLE IF EXISTS " + CloudEntry.TABLE_NAME);
        onCreate(db);
    }
}
