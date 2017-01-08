package com.grappbox.grappbox.data;

import android.app.LoaderManager;
import android.content.ContentProvider;
import android.content.ContentValues;
import android.content.Context;
import android.content.UriMatcher;
import android.database.Cursor;
import android.net.Uri;
import android.os.Build;
import android.provider.BaseColumns;
import android.support.annotation.NonNull;
import android.support.annotation.Nullable;
import android.util.Log;

import com.grappbox.grappbox.R;

/**
 * Created by marcw on 30/08/2016.
 */
public class GrappboxProvider extends ContentProvider {
    private static final UriMatcher sUriMatcher = buildUriMatcher();



    private GrappboxDBHelper mOpenHelper;
    private Context mContext;

    public static final int PROJECT = 100;
    public static final int PROJECT_ALL_BY_USER = 101;
    public static final int PROJECT_ONE_BY_ID = 102;
    public static final int PROJECT_ONE_BY_GRAPPBOX_ID = 103;
    public static final int PROJECT_ACCOUNT = 104;

    public static final int USER = 110;
    public static final int USER_BY_ID = 111;
    public static final int USER_BY_GRAPPBOX_ID = 112;
    public static final int USER_BY_EMAIL = 113;
    public static final int USER_WITH_PROJECT = 114;

    public static final int OCCUPATION = 120;
    public static final int OCCUPATION_ALL_BY_PROJECT_ID = 121;
    public static final int OCCUPATION_ALL_BY_GRAPPBOX_PROJECT_ID = 122;

    public static final int EVENT = 130;
    public static final int EVENT_BY_ID = 132;
    public static final int EVENT_BY_GRAPPBOX_ID = 133;

    public static final int EVENT_PARTICIPANT = 150;
    public static final int EVENT_PARTICIPANT_BY_EVENT_ID = 151;
    public static final int EVENT_PARTICIPANT_BY_GRAPPBOX_EVENT_ID = 152;

    public static final int TIMELINE = 160;
    public static final int TIMELINE_BY_PROJECT_ID = 161;
    public static final int TIMELINE_BY_GRAPPBOX_PROJECT_ID = 162;
    public static final int TIMELINE_BY_GRAPPBOX_ID = 163;
    public static final int TIMELINE_BY_ID = 164;

    public static final int TIMELINE_MESSAGES = 170;
    public static final int TIMELINE_MESSAGES_BY_TIMELINE_ID = 171;
    public static final int TIMELINE_MESSAGES_BY_GRAPPBOX_TIMELINE_ID = 172;
    public static final int TIMELINE_MESSAGES_BY_ID = 173;
    public static final int TIMELINE_MESSAGES_BY_GRAPPBOX_ID = 174;

    public static final int ROLE = 180;
    public static final int ROLE_BY_GRAPPBOX_ID = 181;
    public static final int ROLE_BY_ID = 182;
    public static final int ROLE_BY_PROJECT_ID = 183;
    public static final int ROLE_BY_GRAPPBOX_PROJECT_ID = 184;

    public static final int ROLE_ASSIGNATION = 190;
    public static final int ROLE_ASSIGNATION_BY_ROLE_ID = 191;
    public static final int ROLE_ASSIGNATION_BY_GRAPPBOX_ROLE_ID = 192;
    public static final int ROLE_ASSIGNATION_BY_USER_ID = 193;
    public static final int ROLE_ASSIGNATION_BY_GRAPPBOX_USER_ID = 194;
    public static final int ROLE_ASSIGNATION_WITH_USER_ID_AND_PROJECT_ID = 195;

    public static final int TAG = 200;
    public static final int TAG_BY_ID = 201;
    public static final int TAG_BY_GRAPPBOX_ID = 202;
    public static final int TAG_BY_PROJECT_ID = 203;
    public static final int TAG_BY_GRAPPBOX_PROJECT_ID = 204;

    public static final int BUG = 210;
    public static final int BUG_BY_ID = 211;
    public static final int BUG_BY_GRAPPBOX_ID = 212;
    public static final int BUG_ALL_JOIN = 213;
    public static final int BUG_WITH_ASSIGNATION = 214;
    public static final int BUG_WITH_TAG = 215;
    private static final int BUG_WITH_CREATOR = 216;

    public static final int BUG_TAG = 220;
    public static final int BUG_TAG_BY_BUG_ID = 221;
    public static final int BUG_TAG_BY_GRAPPBOX_BUG_ID = 222;

    public static final int BUG_ASSIGNATION = 230;
    public static final int BUG_ASSIGNATION_BY_BUG_ID = 231;
    public static final int BUG_ASSIGNATION_BY_GRAPPBOX_BUG_ID = 232;
    public static final int BUG_ASSIGNATION_BY_USER_ID = 233;
    public static final int BUG_ASSIGNATION_BY_GRAPPBOX_USER_ID = 234;

    public static final int CLOUD = 240;
    public static final int CLOUD_BY_ID = 241;
    public static final int CLOUD_WITH_PROJECT = 242;

    public static final int CUSTOMER_ACCESS = 450;
    public static final int CUSTOMER_ACCESS_WITH_PROJECT = 451;

    public static final int TASK = 460;
    public static final int TASK_WITH_USER = 461;
    public static final int TASK_WITH_TAG = 462;

    public static final int TASK_TAG = 470;

    public static final int TASK_USER_ASSIGN = 480;
    public static final int TASK_TAG_ASSIGN = 490;

    public static final int TIMELINE_COMMENTS = 260;
    public static final int TIMELINE_COMMENTS_BY_TIMELINE_ID = 261;
    public static final int TIMELINE_COMMENTS_BY_GRAPPBOX_TIMELINE_ID = 262;
    public static final int TIMELINE_COMMENTS_BY_ID = 263;
    public static final int TIMELINE_COMMENTS_BY_GRAPPBOX_ID = 264;

    public static final int STATS = 270;
    public static final int STATS_BY_ID = 271;
    public static final int STATS_BY_GRAPPBOX_ID = 272;

    public static final int ADVANCEMENT = 280;
    public static final int ADVANCEMENT_BY_ID = 281;
    public static final int ADVANCEMENT_BY_STAT_ID = 282;

    public static final int USER_ADVANCEMENT_TASK = 290;
    public static final int USER_ADVANCEMENT_TASK_BY_ID = 291;
    public static final int USER_ADVANCEMENT_TASK_BY_STAT_ID = 292;
    public static final int USER_ADVANCEMENT_TASK_BY_USER_ID = 293;

    public static final int LATE_TASK = 300;

    public static final int TASK_REPARTITION = 310;
    public static final int TASK_REPARTITION_BY_ID = 311;
    public static final int TASK_REPARTITION_BY_STAT_ID = 312;
    public static final int TASK_REPARTITION_BY_USER_ID = 313;

    public static final int USER_WORKING_CHARGE = 320;
    public static final int USER_WORKING_CHARGE_BY_ID = 321;

    public static final int BUG_USER_REPARTITION = 330;
    public static final int BUG_USER_REPARTITION_BY_ID = 331;
    public static final int BUG_USER_REPARTITION_BY_STAT_ID = 332;
    public static final int BUG_USER_REPARTITION_BY_USER_ID = 333;

    public static final int BUG_TAGS_REPARTITION = 340;
    public static final int BUG_TAGS_REPARTITION_BY_ID = 341;
    public static final int BUG_TAGS_REPARTITION_BY_STAT_ID = 342;
    public static final int BUG_TAGS_REPARTITION_BY_TAG_ID = 343;

    public static final int BUG_EVOLUTION = 350;
    public static final int BUG_EVOLUTION_BY_ID = 351;
    public static final int BUG_EVOLUTION_BY_STAT_ID = 352;

    public static final int NEXT_MEETING = 360;
    public static final int NEXT_MEETING_BY_ID = 361;
    public static final int NEXT_MEETING_BY_PROJECT_ID = 362;

    public static UriMatcher buildUriMatcher() {
        UriMatcher matcher = new UriMatcher(UriMatcher.NO_MATCH);

        //Projects related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_PROJECT, PROJECT); //General URI to specific request or retreive all objects
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_PROJECT + "/user/0", PROJECT_ALL_BY_USER);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_PROJECT + "/#", PROJECT_ONE_BY_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_PROJECT + "/*", PROJECT_ONE_BY_GRAPPBOX_ID);

        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_PROJECT_ACCOUNT, PROJECT_ACCOUNT);


        //Users related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_USER, USER);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_USER + "/email/*", USER_BY_EMAIL);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_USER + "/project", USER_WITH_PROJECT);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_USER + "/#", USER_BY_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_USER + "/*", USER_BY_GRAPPBOX_ID);

        //Occupation related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_OCCUPATION, OCCUPATION);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_OCCUPATION + "/project/#", OCCUPATION_ALL_BY_PROJECT_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_OCCUPATION + "/project/*", OCCUPATION_ALL_BY_GRAPPBOX_PROJECT_ID);

        //Event related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_EVENT, EVENT);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_EVENT + "/#", EVENT_BY_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_EVENT + "/*", EVENT_BY_GRAPPBOX_ID);


        //Event participant related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_EVENT_PARTICIPANT, EVENT_PARTICIPANT);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_EVENT_PARTICIPANT + "/event/#", EVENT_PARTICIPANT_BY_EVENT_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_EVENT_PARTICIPANT + "/event/*", EVENT_PARTICIPANT_BY_GRAPPBOX_EVENT_ID);

        //Timeline related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TIMELINE, TIMELINE);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TIMELINE + "/project/#", TIMELINE_BY_PROJECT_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TIMELINE + "/project/*", TIMELINE_BY_GRAPPBOX_PROJECT_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TIMELINE + "/#", TIMELINE_BY_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TIMELINE + "/*", TIMELINE_BY_GRAPPBOX_ID);

        //Timeline messages related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TIMELINE_MESSAGES, TIMELINE_MESSAGES);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TIMELINE_MESSAGES + "/timeline/#", TIMELINE_MESSAGES_BY_TIMELINE_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TIMELINE_MESSAGES + "/timeline/*", TIMELINE_MESSAGES_BY_GRAPPBOX_TIMELINE_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TIMELINE_MESSAGES + "/#", TIMELINE_MESSAGES_BY_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TIMELINE_MESSAGES + "/*", TIMELINE_MESSAGES_BY_GRAPPBOX_ID);

        //Timeline comments related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TIMELINE_COMMENTS, TIMELINE_COMMENTS);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TIMELINE_COMMENTS + "/timeline/#", TIMELINE_COMMENTS_BY_TIMELINE_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TIMELINE_COMMENTS + "/timeline/*", TIMELINE_COMMENTS_BY_GRAPPBOX_TIMELINE_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TIMELINE_COMMENTS + "/#", TIMELINE_COMMENTS_BY_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TIMELINE_COMMENTS + "/*", TIMELINE_COMMENTS_BY_GRAPPBOX_ID);

        //Role related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_ROLE, ROLE);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_ROLE + "/project/#", ROLE_BY_PROJECT_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_ROLE + "/project/*", ROLE_BY_GRAPPBOX_PROJECT_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_ROLE + "/#", ROLE_BY_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_ROLE + "/*", ROLE_BY_GRAPPBOX_ID);

        //Role assignation related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_ROLE_ASSIGNATION, ROLE_ASSIGNATION);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_ROLE_ASSIGNATION + "/role/#", ROLE_ASSIGNATION_BY_ROLE_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_ROLE_ASSIGNATION + "/role/*", ROLE_ASSIGNATION_BY_GRAPPBOX_ROLE_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_ROLE_ASSIGNATION + "/user/#", ROLE_ASSIGNATION_BY_USER_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_ROLE_ASSIGNATION + "/user/*", ROLE_ASSIGNATION_BY_GRAPPBOX_USER_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_ROLE_ASSIGNATION + "/projectuser", ROLE_ASSIGNATION_WITH_USER_ID_AND_PROJECT_ID);

        //Tag related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TAG, TAG);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TAG + "/project/#", TAG_BY_PROJECT_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TAG + "/project/*", TAG_BY_GRAPPBOX_PROJECT_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TAG + "/#", TAG_BY_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TAG + "/*", TAG_BY_GRAPPBOX_ID);

        //Bug related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG, BUG);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG + "/tag_user", BUG_ALL_JOIN);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG + "/user_assignation", BUG_WITH_ASSIGNATION);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG + "/tags", BUG_WITH_TAG);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG + "/creator", BUG_WITH_CREATOR);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG + "/#", BUG_BY_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG + "/*", BUG_BY_GRAPPBOX_ID);

        //Bug tag related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG_TAG, BUG_TAG);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG_TAG + "/bug/#", BUG_TAG_BY_BUG_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG_TAG + "/bug/*", BUG_TAG_BY_GRAPPBOX_BUG_ID);

        //Bug assignation related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG_ASSIGNATION, BUG_ASSIGNATION);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG_ASSIGNATION + "/bug/#", BUG_ASSIGNATION_BY_BUG_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG_ASSIGNATION + "/bug/*", BUG_ASSIGNATION_BY_GRAPPBOX_BUG_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG_ASSIGNATION + "/user/#", BUG_ASSIGNATION_BY_USER_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG_ASSIGNATION + "/user/*", BUG_ASSIGNATION_BY_GRAPPBOX_USER_ID);

        //Cloud related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_CLOUD, CLOUD);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_CLOUD + "/#", CLOUD_BY_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_CLOUD + "/withproject", CLOUD_WITH_PROJECT);

        //Customer access related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_CUSTOMER_ACCESS, CUSTOMER_ACCESS);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_CUSTOMER_ACCESS + "/project" , CUSTOMER_ACCESS_WITH_PROJECT);

        //Task related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TASK, TASK);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TASK + "/users", TASK_WITH_USER);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TASK + "/tags", TASK_WITH_TAG);

        //Task user assignation related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TASK_USER_ASSIGNATION, TASK_USER_ASSIGN);

        //Task tag related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TASK_TAGS, TASK_TAG);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TASK_TAGS_ASSIGNATION, TASK_TAG_ASSIGN);

        //Stats related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_STATS, STATS);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_STATS + "/#", STATS_BY_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_STATS + "/*", STATS_BY_GRAPPBOX_ID);

        //Advancement related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_ADVANCEMENT, ADVANCEMENT);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_ADVANCEMENT + "/#", ADVANCEMENT_BY_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_ADVANCEMENT + "/stat/#", ADVANCEMENT_BY_STAT_ID);

        //User Advancement Task related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_USER_ADVANCEMENT_TASK, USER_ADVANCEMENT_TASK);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_USER_ADVANCEMENT_TASK + "/#",  USER_ADVANCEMENT_TASK_BY_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_USER_ADVANCEMENT_TASK + "/stat/#", USER_ADVANCEMENT_TASK_BY_STAT_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_USER_ADVANCEMENT_TASK + "/user/#", USER_ADVANCEMENT_TASK_BY_USER_ID);

        //Late Task related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_LATE_TASK, LATE_TASK);

        //Task Repartition related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TASK_REPARTITION, TASK_REPARTITION);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TASK_REPARTITION + "/#", TASK_REPARTITION_BY_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TASK_REPARTITION + "/stat/#", TASK_REPARTITION_BY_STAT_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TASK_REPARTITION + "/user/#", TASK_REPARTITION_BY_USER_ID);

        //User Working Charge related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_USER_WORKING_CHARGE, USER_WORKING_CHARGE);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_USER_WORKING_CHARGE + "/#", USER_WORKING_CHARGE_BY_ID);

        //Bug User Repartition related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG_USER_REPARTITION, BUG_USER_REPARTITION);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG_USER_REPARTITION + "/#", BUG_USER_REPARTITION_BY_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG_USER_REPARTITION + "/stat/#", BUG_USER_REPARTITION_BY_STAT_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG_USER_REPARTITION + "/user/#", BUG_USER_REPARTITION_BY_USER_ID);

        //Bug Tags Repartition related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG_TAGS_REPARTITION, BUG_TAGS_REPARTITION);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG_TAGS_REPARTITION + "/#", BUG_TAGS_REPARTITION_BY_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG_TAGS_REPARTITION + "/stat/#", BUG_TAGS_REPARTITION_BY_STAT_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG_TAGS_REPARTITION + "/tags/#", BUG_TAGS_REPARTITION_BY_TAG_ID);

        //Bugs evolution related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG_EVOLUTION, BUG_EVOLUTION);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG_EVOLUTION + "/#", BUG_EVOLUTION_BY_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG_EVOLUTION + "/stat/", BUG_EVOLUTION_BY_STAT_ID);

        //Next Meeting related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_NEXT_MEETING, NEXT_MEETING);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_NEXT_MEETING + "/#", NEXT_MEETING_BY_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_NEXT_MEETING + "/project/#", NEXT_MEETING_BY_PROJECT_ID);

        return matcher;
    }

    @Override
    public boolean onCreate() {
        mContext = getContext();
        mOpenHelper = new GrappboxDBHelper(mContext);
        return true;
    }

    @Override
    public String getType(@NonNull Uri uri) {
        switch (sUriMatcher.match(uri))
        {
            case PROJECT:
            case PROJECT_ALL_BY_USER:
                return GrappboxContract.ProjectEntry.CONTENT_TYPE;
            case PROJECT_ONE_BY_ID:
            case PROJECT_ONE_BY_GRAPPBOX_ID:
                return GrappboxContract.ProjectEntry.CONTENT_ITEM_TYPE;

            case PROJECT_ACCOUNT:
                return GrappboxContract.ProjectEntry.CONTENT_TYPE;

            case USER:
                return GrappboxContract.UserEntry.CONTENT_TYPE;
            case USER_BY_ID:
            case USER_BY_GRAPPBOX_ID:
            case USER_BY_EMAIL:
                return GrappboxContract.UserEntry.CONTENT_ITEM_TYPE;

            case OCCUPATION:
            case OCCUPATION_ALL_BY_GRAPPBOX_PROJECT_ID:
            case OCCUPATION_ALL_BY_PROJECT_ID:
                return GrappboxContract.OccupationEntry.CONTENT_TYPE;

            case EVENT:
                return GrappboxContract.EventEntry.CONTENT_TYPE;
            case EVENT_BY_ID:
            case EVENT_BY_GRAPPBOX_ID:
                return GrappboxContract.EventEntry.CONTENT_ITEM_TYPE;

            case TIMELINE:
            case TIMELINE_BY_PROJECT_ID:
            case TIMELINE_BY_GRAPPBOX_PROJECT_ID:
                return GrappboxContract.TimelineEntry.CONTENT_TYPE;
            case TIMELINE_BY_GRAPPBOX_ID:
            case TIMELINE_BY_ID:
                return GrappboxContract.TimelineEntry.CONTENT_ITEM_TYPE;

            case TIMELINE_MESSAGES:
            case TIMELINE_MESSAGES_BY_GRAPPBOX_TIMELINE_ID:
            case TIMELINE_MESSAGES_BY_TIMELINE_ID:
                return GrappboxContract.TimelineMessageEntry.CONTENT_TYPE;
            case TIMELINE_MESSAGES_BY_GRAPPBOX_ID:
            case TIMELINE_MESSAGES_BY_ID:
                return GrappboxContract.TimelineMessageEntry.CONTENT_ITEM_TYPE;

            case TIMELINE_COMMENTS:
            case TIMELINE_COMMENTS_BY_GRAPPBOX_TIMELINE_ID:
            case TIMELINE_COMMENTS_BY_TIMELINE_ID:
                return GrappboxContract.TimelineCommentEntry.CONTENT_TYPE;
            case TIMELINE_COMMENTS_BY_GRAPPBOX_ID:
            case TIMELINE_COMMENTS_BY_ID:
                return GrappboxContract.TimelineCommentEntry.CONTENT_ITEM_TYPE;

            case ROLE:
            case ROLE_BY_PROJECT_ID:
            case ROLE_BY_GRAPPBOX_PROJECT_ID:
                return GrappboxContract.RolesEntry.CONTENT_TYPE;
            case ROLE_BY_GRAPPBOX_ID:
            case ROLE_BY_ID:
                return GrappboxContract.RolesEntry.CONTENT_ITEM_TYPE;

            case ROLE_ASSIGNATION:
            case ROLE_ASSIGNATION_BY_ROLE_ID:
            case ROLE_ASSIGNATION_BY_GRAPPBOX_ROLE_ID:
            case ROLE_ASSIGNATION_BY_GRAPPBOX_USER_ID:
            case ROLE_ASSIGNATION_BY_USER_ID:
                return GrappboxContract.RolesAssignationEntry.CONTENT_TYPE;
            case ROLE_ASSIGNATION_WITH_USER_ID_AND_PROJECT_ID:
                return GrappboxContract.RolesAssignationEntry.CONTENT_ITEM_TYPE;

            case TAG:
            case TAG_BY_PROJECT_ID:
            case TAG_BY_GRAPPBOX_PROJECT_ID:
                return GrappboxContract.BugtrackerTagEntry.CONTENT_TYPE;
            case TAG_BY_ID:
            case TAG_BY_GRAPPBOX_ID:
                return GrappboxContract.BugtrackerTagEntry.CONTENT_ITEM_TYPE;

            case BUG:
                return GrappboxContract.BugEntry.CONTENT_TYPE;
            case BUG_BY_ID:
            case BUG_BY_GRAPPBOX_ID:
                return GrappboxContract.BugEntry.CONTENT_ITEM_TYPE;

            case BUG_TAG:
            case BUG_TAG_BY_BUG_ID:
            case BUG_TAG_BY_GRAPPBOX_BUG_ID:
                return GrappboxContract.BugTagEntry.CONTENT_TYPE;

            case BUG_ASSIGNATION:
            case BUG_ASSIGNATION_BY_BUG_ID:
            case BUG_ASSIGNATION_BY_GRAPPBOX_BUG_ID:
            case BUG_ASSIGNATION_BY_GRAPPBOX_USER_ID:
            case BUG_ASSIGNATION_BY_USER_ID:
                return GrappboxContract.BugAssignationEntry.CONTENT_TYPE;

            case CLOUD:
            case CLOUD_WITH_PROJECT:
                return GrappboxContract.CloudEntry.CONTENT_TYPE;
            case CLOUD_BY_ID:
                return GrappboxContract.CloudEntry.CONTENT_ITEM_TYPE;

            case STATS:
            case STATS_BY_ID:
            case STATS_BY_GRAPPBOX_ID:
                return GrappboxContract.StatEntry.CONTENT_TYPE;

            case ADVANCEMENT:
            case ADVANCEMENT_BY_ID:
            case ADVANCEMENT_BY_STAT_ID:
                return GrappboxContract.AdvancementEntry.CONTENT_TYPE;

            case USER_ADVANCEMENT_TASK:
            case USER_ADVANCEMENT_TASK_BY_ID:
            case USER_ADVANCEMENT_TASK_BY_STAT_ID:
                return GrappboxContract.UserAdvancementTaskEntry.CONTENT_TYPE;

            case LATE_TASK:
                return GrappboxContract.LateTaskEntry.CONTENT_TYPE;

            case TASK_REPARTITION:
            case TASK_REPARTITION_BY_ID:
            case TASK_REPARTITION_BY_STAT_ID:
            case TASK_REPARTITION_BY_USER_ID:
                return GrappboxContract.TaskRepartitionEntry.CONTENT_TYPE;

            case USER_WORKING_CHARGE:
            case USER_WORKING_CHARGE_BY_ID:
                return GrappboxContract.UserWorkingChargeEntry.CONTENT_TYPE;

            case BUG_USER_REPARTITION:
            case BUG_USER_REPARTITION_BY_ID:
            case BUG_USER_REPARTITION_BY_STAT_ID:
            case BUG_USER_REPARTITION_BY_USER_ID:
                return GrappboxContract.BugUserRepartitionEntry.CONTENT_TYPE;

            case BUG_TAGS_REPARTITION:
            case BUG_TAGS_REPARTITION_BY_ID:
            case BUG_TAGS_REPARTITION_BY_STAT_ID:
            case BUG_TAGS_REPARTITION_BY_TAG_ID:
                return GrappboxContract.BugTagsRepartitionEntry.CONTENT_TYPE;

            case BUG_EVOLUTION:
            case BUG_EVOLUTION_BY_ID:
            case BUG_EVOLUTION_BY_STAT_ID:
                return GrappboxContract.BugEvolutionEntry.CONTENT_TYPE;

            case NEXT_MEETING:
            case NEXT_MEETING_BY_ID:
            case NEXT_MEETING_BY_PROJECT_ID:
                return GrappboxContract.NextMeetingEntry.CONTENT_TYPE;

            default:
                throw new UnsupportedOperationException(mContext.getString(R.string.error_unsupported_uri, uri.toString()));
        }
    }

    private String getTableName(@NonNull Uri uri){
        switch (sUriMatcher.match(uri)){
            case USER:
                return GrappboxContract.UserEntry.TABLE_NAME;
            case OCCUPATION:
                return GrappboxContract.OccupationEntry.TABLE_NAME;
            case PROJECT:
                return GrappboxContract.ProjectEntry.TABLE_NAME;
            case PROJECT_ACCOUNT:
                return GrappboxContract.ProjectAccountEntry.TABLE_NAME;
            case ROLE:
                return GrappboxContract.RolesEntry.TABLE_NAME;
            case ROLE_ASSIGNATION:
                return GrappboxContract.RolesAssignationEntry.TABLE_NAME;
            case BUG:
                return GrappboxContract.BugEntry.TABLE_NAME;
            case BUG_TAG:
                return GrappboxContract.BugTagEntry.TABLE_NAME;
            case BUG_ASSIGNATION:
                return GrappboxContract.BugAssignationEntry.TABLE_NAME;
            case TIMELINE:
                return GrappboxContract.TimelineEntry.TABLE_NAME;
            case TIMELINE_MESSAGES:
                return GrappboxContract.TimelineMessageEntry.TABLE_NAME;
            case TIMELINE_COMMENTS:
                return GrappboxContract.TimelineCommentEntry.TABLE_NAME;
            case CLOUD:
                return GrappboxContract.CloudEntry.TABLE_NAME;
            case EVENT:
                return GrappboxContract.EventEntry.TABLE_NAME;
            case CUSTOMER_ACCESS:
                return GrappboxContract.CustomerAccessEntry.TABLE_NAME;
            case TASK:
                return GrappboxContract.TaskEntry.TABLE_NAME;
            case TASK_TAG:
                return GrappboxContract.TaskTagEntry.TABLE_NAME;
            case TASK_USER_ASSIGN:
                return GrappboxContract.TaskAssignationEntry.TABLE_NAME;
            case TASK_TAG_ASSIGN:
                return GrappboxContract.TaskTagAssignationEntry.TABLE_NAME;
            case EVENT_PARTICIPANT:
                return GrappboxContract.EventParticipantEntry.TABLE_NAME;
            case TAG:
                return GrappboxContract.BugtrackerTagEntry.TABLE_NAME;
            case STATS:
                return GrappboxContract.StatEntry.TABLE_NAME;
            case ADVANCEMENT:
                return GrappboxContract.AdvancementEntry.TABLE_NAME;
            case USER_ADVANCEMENT_TASK:
                return GrappboxContract.UserAdvancementTaskEntry.TABLE_NAME;
            case LATE_TASK:
                return GrappboxContract.LateTaskEntry.TABLE_NAME;
            case TASK_REPARTITION:
                return GrappboxContract.TaskRepartitionEntry.TABLE_NAME;
            case USER_WORKING_CHARGE:
                return GrappboxContract.UserWorkingChargeEntry.TABLE_NAME;
            case BUG_USER_REPARTITION:
                return GrappboxContract.BugUserRepartitionEntry.TABLE_NAME;
            case BUG_TAGS_REPARTITION:
                return GrappboxContract.BugTagsRepartitionEntry.TABLE_NAME;
            case BUG_EVOLUTION:
                return GrappboxContract.BugEvolutionEntry.TABLE_NAME;
            case NEXT_MEETING:
                return GrappboxContract.NextMeetingEntry.TABLE_NAME;
            default:
                return "";
        }
    }

    @Nullable
    @Override
    public Cursor query(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder) {
        Cursor retCursor;
        switch (sUriMatcher.match(uri))
        {
            case PROJECT:
                retCursor = ProjectCursors.query_Project(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case PROJECT_ALL_BY_USER:
                retCursor = ProjectCursors.query_ProjectWithUser(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case PROJECT_ONE_BY_ID:
                retCursor = ProjectCursors.query_ProjectOneById(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case PROJECT_ONE_BY_GRAPPBOX_ID:
                retCursor = ProjectCursors.query_ProjectOneByGrappboxId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case PROJECT_ACCOUNT:
                retCursor = ProjectCursors.query_ProjectWithAccount(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case USER:
                retCursor = UserCursors.query_User(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case USER_BY_ID:
                retCursor = UserCursors.query_UserById(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case USER_BY_GRAPPBOX_ID:
                retCursor = UserCursors.query_UserByGrappboxId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case USER_BY_EMAIL:
                retCursor = UserCursors.query_UserByEmail(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case USER_WITH_PROJECT:
                retCursor = UserCursors.query_UserWithProject(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case OCCUPATION:
                retCursor = OccupationCursors.query_Occupation(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case OCCUPATION_ALL_BY_GRAPPBOX_PROJECT_ID:
                retCursor = OccupationCursors.query_OccupationAllByGrappboxProjectId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case OCCUPATION_ALL_BY_PROJECT_ID:
                retCursor = OccupationCursors.query_OccupationAllByProjectId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case EVENT:
                retCursor = EventCursors.query_Event(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case EVENT_BY_ID:
                retCursor = EventCursors.query_EventById(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case EVENT_BY_GRAPPBOX_ID:
                retCursor = EventCursors.query_EventByGrappboxId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case EVENT_PARTICIPANT:
                retCursor = EventParticipantCursors.query_EventParticipant(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case EVENT_PARTICIPANT_BY_EVENT_ID:
                retCursor = EventParticipantCursors.query_EventParticipantByEventId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case EVENT_PARTICIPANT_BY_GRAPPBOX_EVENT_ID:
                retCursor = EventParticipantCursors.query_EventParticipantByGrappboxEventId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TIMELINE:
                retCursor = TimelineCursors.query_Timeline(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TIMELINE_BY_PROJECT_ID:
                retCursor = TimelineCursors.query_TimelineByProjectId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TIMELINE_BY_GRAPPBOX_PROJECT_ID:
                retCursor = TimelineCursors.query_TimelineByGrappboxProjectId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TIMELINE_BY_GRAPPBOX_ID:
                retCursor = TimelineCursors.query_TimelineByGrappboxId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TIMELINE_BY_ID:
                retCursor = TimelineCursors.query_TimelineById(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TIMELINE_MESSAGES:
                retCursor = TimelineMessageCursors.query_TimelineMessage(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TIMELINE_MESSAGES_BY_TIMELINE_ID:
                retCursor = TimelineMessageCursors.query_TimelineMessageByTimelineId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TIMELINE_MESSAGES_BY_GRAPPBOX_TIMELINE_ID:
                retCursor = TimelineMessageCursors.query_TimelineMessageByGrappboxTimelineId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TIMELINE_MESSAGES_BY_ID:
                retCursor =  TimelineMessageCursors.query_TimelineMessageById(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TIMELINE_MESSAGES_BY_GRAPPBOX_ID:
                retCursor = TimelineMessageCursors.query_TimelineMessageByGrappboxId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TIMELINE_COMMENTS:
                retCursor = TimelineCommentCursors.query_TimelineComment(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TIMELINE_COMMENTS_BY_TIMELINE_ID:
                retCursor = TimelineCommentCursors.query_TimelineCommentByTimelineId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TIMELINE_COMMENTS_BY_GRAPPBOX_TIMELINE_ID:
                retCursor = TimelineCommentCursors.query_TimelineCommentByGrappboxTimelineId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TIMELINE_COMMENTS_BY_ID:
                retCursor = TimelineCommentCursors.query_TimelineCommentById(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TIMELINE_COMMENTS_BY_GRAPPBOX_ID:
                retCursor = TimelineCommentCursors.query_TimelineCommentByGrappboxId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case ROLE:
                retCursor = RoleCursors.query_Role(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case ROLE_BY_GRAPPBOX_ID:
                retCursor = RoleCursors.query_RoleByGrappboxId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case ROLE_BY_ID:
                retCursor = RoleCursors.query_RoleById(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case ROLE_BY_PROJECT_ID:
                retCursor = RoleCursors.query_RoleByProjectId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case ROLE_BY_GRAPPBOX_PROJECT_ID:
                retCursor = RoleCursors.query_RoleByGrappboxProjectId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case ROLE_ASSIGNATION:
                retCursor = RoleAssignationCursors.query_RoleAssignation(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case ROLE_ASSIGNATION_BY_ROLE_ID:
                retCursor = RoleAssignationCursors.query_RoleAssignationByRoleId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case ROLE_ASSIGNATION_BY_GRAPPBOX_ROLE_ID:
                retCursor = RoleAssignationCursors.query_RoleAssignationByGrappboxRoleId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case ROLE_ASSIGNATION_BY_USER_ID:
                retCursor = RoleAssignationCursors.query_RoleAssignationByUserId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case ROLE_ASSIGNATION_BY_GRAPPBOX_USER_ID:
                retCursor = RoleAssignationCursors.query_RoleAssignationByGrappboxUserId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case ROLE_ASSIGNATION_WITH_USER_ID_AND_PROJECT_ID:
                retCursor = RoleAssignationCursors.query_RoleAssignationWithUserIdAndProjectId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TAG:
                retCursor = BugtrackerTagCursors.query_Tag(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TAG_BY_ID:
                retCursor = BugtrackerTagCursors.query_TagById(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TAG_BY_GRAPPBOX_ID:
                retCursor = BugtrackerTagCursors.query_TagByGrappboxId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TAG_BY_PROJECT_ID:
                retCursor = BugtrackerTagCursors.query_TagByProjectId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TAG_BY_GRAPPBOX_PROJECT_ID:
                retCursor = BugtrackerTagCursors.query_TagByGrappboxProjectId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG:
                retCursor = BugCursors.query_Bug(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG_BY_ID:
                retCursor = BugCursors.query_BugById(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG_ALL_JOIN:
                retCursor = BugCursors.query_BugWithTagAndAssignation(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG_WITH_ASSIGNATION:
                retCursor = BugCursors.query_BugWithAssignation(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG_WITH_TAG:
                retCursor = BugCursors.query_BugWithTag(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG_WITH_CREATOR:
                retCursor = BugCursors.query_BugWithCreator(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG_TAG_BY_GRAPPBOX_BUG_ID:
                retCursor = BugTagCursors.query_BugTagByGrappboxBugId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG_ASSIGNATION:
                retCursor = BugAssignationCursors.query_BugAssignation(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG_ASSIGNATION_BY_BUG_ID:
                retCursor = BugAssignationCursors.query_BugAssignationByBugId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG_ASSIGNATION_BY_GRAPPBOX_BUG_ID:
                retCursor = BugAssignationCursors.query_BugAssignationByGrappboxBugId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG_ASSIGNATION_BY_USER_ID:
                retCursor = BugAssignationCursors.query_BugAssignationByUserId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG_TAG:
                retCursor = BugTagCursors.query_BugTag(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG_ASSIGNATION_BY_GRAPPBOX_USER_ID:
                retCursor = BugAssignationCursors.query_BugAssignationByGrappboxUserId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case CLOUD:
                retCursor = CloudCursors.query_Cloud(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case CLOUD_BY_ID:
                retCursor = CloudCursors.query_CloudById(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case CLOUD_WITH_PROJECT:
                retCursor = CloudCursors.query_CloudWithProject(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case CUSTOMER_ACCESS:
                retCursor = CustomerAccessCursors.query(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case CUSTOMER_ACCESS_WITH_PROJECT:
                retCursor = CustomerAccessCursors.query_withProject(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TASK:
                retCursor = TaskCursors.query(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TASK_WITH_TAG:
                retCursor = TaskCursors.query_withTag(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TASK_WITH_USER:
                retCursor = TaskCursors.query_withUser(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TASK_TAG:
                retCursor = TaskTagCursors.query(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TASK_USER_ASSIGN:
                retCursor = TaskUserAssignationCursors.query(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TASK_TAG_ASSIGN:
                retCursor = TaskTagAssignationCursors.query(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case STATS:
                retCursor = StatCursors.query_Stat(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case STATS_BY_ID:
                retCursor = StatCursors.query_StatById(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case ADVANCEMENT:
                retCursor = AdvancementCursors.query_Advancement(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case ADVANCEMENT_BY_ID:
                retCursor = AdvancementCursors.query_AdvancementById(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case ADVANCEMENT_BY_STAT_ID:
                retCursor = AdvancementCursors.query_AdvancementById(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case USER_ADVANCEMENT_TASK:
                retCursor = UserAdvancementTaskCursors.query_UserAdvancementTask(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case USER_ADVANCEMENT_TASK_BY_ID:
                retCursor = UserAdvancementTaskCursors.query_UserAdvancementById(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case USER_ADVANCEMENT_TASK_BY_STAT_ID:
                retCursor = UserAdvancementTaskCursors.query_UserAdvancementWithStat(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case LATE_TASK:
                retCursor = LateTaskCursors.query_LateTask(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TASK_REPARTITION:
                retCursor = TaskRepartitionCursors.query_TaskRepartition(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TASK_REPARTITION_BY_ID:
                retCursor = TaskRepartitionCursors.query_TaskRepartitionById(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TASK_REPARTITION_BY_STAT_ID:
                retCursor = TaskRepartitionCursors.query_TaskRepartitionWithStat(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TASK_REPARTITION_BY_USER_ID:
                retCursor = TaskRepartitionCursors.query_TaskRepartitionWithUser(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case USER_WORKING_CHARGE:
                retCursor = UserWorkingChargeCursors.query_UserWorkingCharge(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case USER_WORKING_CHARGE_BY_ID:
                retCursor = UserWorkingChargeCursors.query_UserWorkingChargeById(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG_USER_REPARTITION:
                retCursor = BugUserRepartitionCursors.query_BugUserRepartition(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG_USER_REPARTITION_BY_ID:
                retCursor = BugUserRepartitionCursors.query_BugUserRepartitionById(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG_USER_REPARTITION_BY_STAT_ID:
                retCursor = BugUserRepartitionCursors.query_BugUserRepartitionByStat(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG_TAGS_REPARTITION:
                retCursor = BugTagsRepartitionCursors.query_BugTagsRepartition(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG_TAGS_REPARTITION_BY_ID:
                retCursor = BugTagsRepartitionCursors.query_BugTagsRepartitionById(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG_TAGS_REPARTITION_BY_STAT_ID:
                retCursor = BugTagsRepartitionCursors.query_BugTagsRepartitionByStat(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG_EVOLUTION:
                retCursor = BugEvolutionCursors.query_BugEvolution(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG_EVOLUTION_BY_ID:
                retCursor = BugEvolutionCursors.query_BugEvolutionById(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG_EVOLUTION_BY_STAT_ID:
                retCursor = BugEvolutionCursors.query_BugEvolutionByStat(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case NEXT_MEETING:
                retCursor = NextMeetingCursor.query_NextMeeting(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case NEXT_MEETING_BY_ID:
                retCursor = NextMeetingCursor.query_NextMeetingById(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case NEXT_MEETING_BY_PROJECT_ID:
                retCursor = NextMeetingCursor.query_NextMeetingByProject(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            default:
                throw new UnsupportedOperationException(mContext.getString(R.string.error_unsupported_uri, uri.toString()));
        }
        if (getContext() != null && getContext().getContentResolver() != null)
            retCursor.setNotificationUri(getContext().getContentResolver(), uri);
        return retCursor;
    }

    @Nullable
    @Override
    public Uri insert(@NonNull Uri uri, ContentValues contentValues) {
        Uri returnedUri;

        //Pre-detect conflict and handle with update method
        if (contentValues.containsKey(GrappboxContract.GENERAL_GRAPPBOX_ID)){
            Cursor value = query(uri, new String[]{ getTableName(uri) + "." + BaseColumns._ID}, getTableName(uri) + "." + GrappboxContract.GENERAL_GRAPPBOX_ID + "=?", new String[]{contentValues.getAsString(GrappboxContract.GENERAL_GRAPPBOX_ID)}, null);
            if (value != null && value.moveToFirst()){
                Uri newUri = uri.buildUpon().appendPath(String.valueOf(value.getLong(0))).build();
                update(uri, contentValues, BaseColumns._ID + "=?", new String[]{String.valueOf(value.getLong(0))});
                value.close();
                return newUri;
            }
        }

        switch (sUriMatcher.match(uri)) {
            case USER:
                returnedUri = UserCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case OCCUPATION:
                returnedUri = OccupationCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case PROJECT:
                returnedUri = ProjectCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case PROJECT_ACCOUNT:
                returnedUri = ProjectCursors.insert_account(uri, contentValues, mOpenHelper);
                break;
            case ROLE:
                returnedUri = RoleCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case ROLE_ASSIGNATION:
                returnedUri = RoleAssignationCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case TAG:
                returnedUri = BugtrackerTagCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case BUG:
                returnedUri = BugCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case BUG_TAG:
                returnedUri = BugTagCursors.insert(uri, contentValues, mOpenHelper);
                getContext().getContentResolver().notifyChange(GrappboxContract.BugEntry.CONTENT_URI, null);
                break;
            case BUG_ASSIGNATION:
                returnedUri = BugAssignationCursors.insert(uri, contentValues, mOpenHelper);
                getContext().getContentResolver().notifyChange(GrappboxContract.BugEntry.CONTENT_URI, null);
                break;
            case TIMELINE:
                returnedUri = TimelineCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case TIMELINE_MESSAGES:
                returnedUri = TimelineMessageCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case TIMELINE_COMMENTS:
                returnedUri = TimelineCommentCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case CLOUD:
                returnedUri = CloudCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case EVENT:
                returnedUri = EventCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case CUSTOMER_ACCESS:
                returnedUri = CustomerAccessCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case TASK:
                returnedUri = TaskCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case TASK_TAG:
                returnedUri = TaskTagCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case TASK_USER_ASSIGN:
                returnedUri = TaskUserAssignationCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case TASK_TAG_ASSIGN:
                returnedUri = TaskTagAssignationCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case EVENT_PARTICIPANT:
                returnedUri = EventParticipantCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case STATS:
                returnedUri = StatCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case ADVANCEMENT:
                returnedUri = AdvancementCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case USER_ADVANCEMENT_TASK:
                returnedUri = UserAdvancementTaskCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case TASK_REPARTITION:
                returnedUri = TaskRepartitionCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case LATE_TASK:
                returnedUri = LateTaskCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case USER_WORKING_CHARGE:
                returnedUri = UserWorkingChargeCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case BUG_USER_REPARTITION:
                returnedUri = BugUserRepartitionCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case BUG_TAGS_REPARTITION:
                returnedUri = BugUserRepartitionCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case BUG_EVOLUTION:
                returnedUri = BugEvolutionCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case NEXT_MEETING:
                returnedUri = NextMeetingCursor.insert(uri, contentValues, mOpenHelper);
                break;
            default:
                throw new UnsupportedOperationException(mContext.getString(R.string.error_unsupported_uri, uri.toString()));
        }
        if (getContext() != null && getContext().getContentResolver() != null)
            getContext().getContentResolver().notifyChange(uri, null);
        return returnedUri;
    }

    @Override
    public int delete(@NonNull Uri uri, String selection, String[] args)
    {
        String tableName = getTableName(uri);
        if (tableName == null || tableName.isEmpty())
            throw new UnsupportedOperationException("Delete not implemented yet, think to implement it");
        int ret =  mOpenHelper.getWritableDatabase().delete(tableName, selection, args);
        if (ret > 0)
            getContext().getContentResolver().notifyChange(uri, null);
        if (tableName.equals(GrappboxContract.BugAssignationEntry.TABLE_NAME) || tableName.equals(GrappboxContract.BugTagEntry.TABLE_NAME) || tableName.equals(GrappboxContract.BugtrackerTagEntry.TABLE_NAME))
            getContext().getContentResolver().notifyChange(GrappboxContract.BugEntry.CONTENT_URI, null);
        return ret;
    }

    @Override
    public int update(@NonNull Uri uri, ContentValues contentValues, String selection, String[] args) {
        int ret;
        switch (sUriMatcher.match(uri)) {
            case USER:
                ret = UserCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case OCCUPATION:
                ret = OccupationCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case PROJECT:
                ret = ProjectCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case PROJECT_ACCOUNT:
                ret = ProjectCursors.update_account(uri, contentValues, selection, args, mOpenHelper);
                break;
            case ROLE:
                ret = RoleCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case ROLE_ASSIGNATION:
                ret = RoleAssignationCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case TAG:
                ret = BugtrackerTagCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case BUG:
                ret = BugCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case BUG_TAG:
                ret = BugTagCursors.update(uri, contentValues, selection, args, mOpenHelper);
                getContext().getContentResolver().notifyChange(GrappboxContract.BugEntry.CONTENT_URI, null);
                break;
            case BUG_ASSIGNATION:
                ret = BugAssignationCursors.update(uri, contentValues, selection, args, mOpenHelper);
                getContext().getContentResolver().notifyChange(GrappboxContract.BugEntry.CONTENT_URI, null);
                break;
            case TIMELINE:
                ret = TimelineCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case TIMELINE_MESSAGES:
                ret = TimelineMessageCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case TIMELINE_COMMENTS:
                ret = TimelineCommentCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case CLOUD:
                ret = CloudCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case EVENT:
                ret = EventCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case CUSTOMER_ACCESS:
                ret = CustomerAccessCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case TASK:
                ret = TaskCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case TASK_TAG:
                ret = TaskTagCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case TASK_USER_ASSIGN:
                ret = TaskUserAssignationCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case TASK_TAG_ASSIGN:
                ret = TaskTagAssignationCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case STATS:
                ret = StatCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case ADVANCEMENT:
                ret = AdvancementCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case USER_ADVANCEMENT_TASK:
                ret = AdvancementCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case TASK_REPARTITION:
                ret = TaskRepartitionCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case LATE_TASK:
                ret = LateTaskCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case USER_WORKING_CHARGE:
                ret = UserWorkingChargeCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case BUG_USER_REPARTITION:
                ret = BugUserRepartitionCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case BUG_TAGS_REPARTITION:
                ret = BugTagsRepartitionCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case BUG_EVOLUTION:
                ret = BugEvolutionCursors.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            case NEXT_MEETING:
                ret = NextMeetingCursor.update(uri, contentValues, selection, args, mOpenHelper);
                break;
            default:
                throw new UnsupportedOperationException("Update not supported, use insert instead, tables construct with ON CONFLICT REPLACE system");
        }
        if (getContext() != null && getContext().getContentResolver() != null)
            getContext().getContentResolver().notifyChange(uri, null);
        return ret;
    }

    @Override
    public int bulkInsert(@NonNull Uri uri, ContentValues[] values) {
        int returnCount = 0;

        switch (sUriMatcher.match(uri))
        {
            case PROJECT:
                returnCount = ProjectCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case USER:
                returnCount = UserCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case OCCUPATION:
                returnCount = OccupationCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case ROLE_ASSIGNATION:
                returnCount = RoleAssignationCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case ROLE:
                returnCount = RoleCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case BUG:
                returnCount = BugCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case BUG_TAG:
                returnCount = BugTagCursors.bulkInsert(uri, values, mOpenHelper);
                getContext().getContentResolver().notifyChange(GrappboxContract.BugEntry.CONTENT_URI, null);
                break;
            case BUG_ASSIGNATION:
                returnCount = BugAssignationCursors.bulkInsert(uri, values, mOpenHelper);
                getContext().getContentResolver().notifyChange(GrappboxContract.BugEntry.CONTENT_URI, null);
                break;
            case TIMELINE:
                returnCount = TimelineCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case TIMELINE_MESSAGES:
                returnCount = TimelineMessageCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case TIMELINE_COMMENTS:
                returnCount = TimelineCommentCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case CLOUD:
                returnCount = CloudCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case EVENT:
                returnCount = EventCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case CUSTOMER_ACCESS:
                returnCount = CustomerAccessCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case STATS:
                returnCount = StatCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case ADVANCEMENT:
                returnCount = AdvancementCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case USER_ADVANCEMENT_TASK:
                returnCount = UserAdvancementTaskCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case TASK_REPARTITION:
                returnCount = TaskRepartitionCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case LATE_TASK:
                returnCount = LateTaskCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case USER_WORKING_CHARGE:
                returnCount = UserWorkingChargeCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case BUG_USER_REPARTITION:
                returnCount = BugUserRepartitionCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case BUG_TAGS_REPARTITION:
                returnCount = BugTagsRepartitionCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case BUG_EVOLUTION:
                returnCount = BugEvolutionCursors.bulkinsert(uri, values, mOpenHelper);
                break;
            case  NEXT_MEETING:
                returnCount = NextMeetingCursor.bulkInsert(uri, values, mOpenHelper);
                break;
            default:
                return super.bulkInsert(uri, values);
        }
        if (getContext() != null && getContext().getContentResolver() != null)
            getContext().getContentResolver().notifyChange(uri, null);
        return returnCount;
    }
}
