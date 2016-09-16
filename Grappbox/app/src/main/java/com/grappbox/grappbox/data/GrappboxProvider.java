package com.grappbox.grappbox.data;

import android.content.ContentProvider;
import android.content.ContentValues;
import android.content.Context;
import android.content.UriMatcher;
import android.database.Cursor;
import android.net.Uri;
import android.provider.BaseColumns;
import android.support.annotation.NonNull;
import android.support.annotation.Nullable;

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

    public static final int OCCUPATION = 120;
    public static final int OCCUPATION_ALL_BY_PROJECT_ID = 121;
    public static final int OCCUPATION_ALL_BY_GRAPPBOX_PROJECT_ID = 122;

    public static final int EVENT = 130;
    public static final int EVENT_BY_ID = 132;
    public static final int EVENT_BY_GRAPPBOX_ID = 133;
    public static final int EVENT_BY_TYPE_ID = 136;

    public static final int EVENT_TYPE = 140;

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
    public static final int ROLE_ASSIGNATION_BY_USER_ID_AND_PROJECT_ID = 195;

    public static final int TAG = 200;
    public static final int TAG_BY_ID = 201;
    public static final int TAG_BY_GRAPPBOX_ID = 202;
    public static final int TAG_BY_PROJECT_ID = 203;
    public static final int TAG_BY_GRAPPBOX_PROJECT_ID = 204;

    public static final int BUG = 210;
    public static final int BUG_BY_ID = 211;
    public static final int BUG_BY_GRAPPBOX_ID = 212;

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
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_USER + "/#", USER_BY_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_USER + "/*", USER_BY_GRAPPBOX_ID);

        //Occupation related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_OCCUPATION, OCCUPATION);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_OCCUPATION + "/project/#", OCCUPATION_ALL_BY_PROJECT_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_OCCUPATION + "/project/*", OCCUPATION_ALL_BY_GRAPPBOX_PROJECT_ID);

        //Event related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_EVENT, EVENT);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_EVENT + "/type/#", EVENT_BY_TYPE_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_EVENT + "/#", EVENT_BY_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_EVENT + "/*", EVENT_BY_GRAPPBOX_ID);

        //Event type related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_EVENT_TYPE, EVENT_TYPE);

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
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_ROLE_ASSIGNATION + "/projectuser/#/#", ROLE_ASSIGNATION_BY_USER_ID_AND_PROJECT_ID);

        //Tag related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TAG, TAG);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TAG + "/project/#", TAG_BY_PROJECT_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TAG + "/project/*", TAG_BY_GRAPPBOX_PROJECT_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TAG + "/#", TAG_BY_ID);
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_TAG + "/*", TAG_BY_GRAPPBOX_ID);

        //Bug related URIs
        matcher.addURI(GrappboxContract.CONTENT_AUTHORITY, GrappboxContract.PATH_BUG, BUG);
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
            case EVENT_BY_TYPE_ID:
                return GrappboxContract.EventEntry.CONTENT_TYPE;
            case EVENT_BY_ID:
            case EVENT_BY_GRAPPBOX_ID:
                return GrappboxContract.EventEntry.CONTENT_ITEM_TYPE;

            case EVENT_TYPE:
                return GrappboxContract.EventTypeEntry.CONTENT_TYPE;

            case EVENT_PARTICIPANT:
                return GrappboxContract.EventTypeEntry.CONTENT_TYPE;
            case EVENT_PARTICIPANT_BY_EVENT_ID:
            case EVENT_PARTICIPANT_BY_GRAPPBOX_EVENT_ID:
                return GrappboxContract.EventTypeEntry.CONTENT_ITEM_TYPE;

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
            case ROLE_ASSIGNATION_BY_USER_ID_AND_PROJECT_ID:
                return GrappboxContract.RolesAssignationEntry.CONTENT_ITEM_TYPE;

            case TAG:
            case TAG_BY_PROJECT_ID:
            case TAG_BY_GRAPPBOX_PROJECT_ID:
                return GrappboxContract.TagEntry.CONTENT_TYPE;
            case TAG_BY_ID:
            case TAG_BY_GRAPPBOX_ID:
                return GrappboxContract.TagEntry.CONTENT_ITEM_TYPE;

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
            default:
                throw new UnsupportedOperationException(mContext.getString(R.string.error_unsupported_uri, uri.toString()));
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
            case EVENT_BY_TYPE_ID:
                retCursor = EventCursors.query_EventByTypeId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case EVENT_TYPE:
                retCursor = EventTypeCursors.query_EventType(uri, projection, selection, args, sortOrder, mOpenHelper);
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
            case ROLE_ASSIGNATION_BY_USER_ID_AND_PROJECT_ID:
                retCursor = RoleAssignationCursors.query_RoleAssignationByUserIdAndProjectId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TAG:
                retCursor = TagCursors.query_Tag(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TAG_BY_ID:
                retCursor = TagCursors.query_TagById(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TAG_BY_GRAPPBOX_ID:
                retCursor = TagCursors.query_TagByGrappboxId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TAG_BY_PROJECT_ID:
                retCursor = TagCursors.query_TagByProjectId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case TAG_BY_GRAPPBOX_PROJECT_ID:
                retCursor = TagCursors.query_TagByGrappboxProjectId(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG:
                retCursor = BugCursors.query_Bug(uri, projection, selection, args, sortOrder, mOpenHelper);
                break;
            case BUG_BY_ID:
                retCursor = BugCursors.query_BugById(uri, projection, selection, args, sortOrder, mOpenHelper);
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
            Cursor value = query(uri, new String[]{BaseColumns._ID}, GrappboxContract.GENERAL_GRAPPBOX_ID + "=?", new String[]{contentValues.getAsString(GrappboxContract.GENERAL_GRAPPBOX_ID)}, null);
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
            case BUG:
                returnedUri = BugCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case BUG_TAG:
                returnedUri = BugTagCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case BUG_ASSIGNATION:
                returnedUri = BugAssignationCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case TIMELINE:
                returnedUri = TimelineCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case TIMELINE_MESSAGES:
                returnedUri = TimelineMessageCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case CLOUD:
                returnedUri = CloudCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case EVENT:
                returnedUri = EventCursors.insert(uri, contentValues, mOpenHelper);
                break;
            case EVENT_TYPE:
                returnedUri = EventTypeCursors.insert(uri, contentValues, mOpenHelper);
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
        throw new UnsupportedOperationException("Delete not implemented yet, think to implement it"); //TODO : Implement it
    }

    @Override
    public int update(@NonNull Uri uri, ContentValues contentValues, String selection, String[] args) {
        switch (sUriMatcher.match(uri)) {
            case USER:
                return UserCursors.update(uri, contentValues, selection, args, mOpenHelper);
            case PROJECT:
                return ProjectCursors.update(uri, contentValues, selection, args, mOpenHelper);
            case PROJECT_ACCOUNT:
                return ProjectCursors.update_account(uri, contentValues, selection, args, mOpenHelper);
            case ROLE:
                return RoleCursors.update(uri, contentValues, selection, args, mOpenHelper);
            case ROLE_ASSIGNATION:
                return RoleAssignationCursors.update(uri, contentValues, selection, args, mOpenHelper);
            case BUG:
                return BugCursors.update(uri, contentValues, selection, args, mOpenHelper);
            case BUG_TAG:
                return BugTagCursors.update(uri, contentValues, selection, args, mOpenHelper);
            case BUG_ASSIGNATION:
                return BugAssignationCursors.update(uri, contentValues, selection, args, mOpenHelper);
            case TIMELINE:
                return TimelineCursors.update(uri, contentValues, selection, args, mOpenHelper);
            case TIMELINE_MESSAGES:
               return TimelineMessageCursors.update(uri, contentValues, selection, args, mOpenHelper);
            case CLOUD:
                return CloudCursors.update(uri, contentValues, selection, args, mOpenHelper);
            case EVENT:
                return EventCursors.update(uri, contentValues, selection, args, mOpenHelper);
            case EVENT_TYPE:
                return EventTypeCursors.update(uri, contentValues, selection, args, mOpenHelper);
            default:
                throw new UnsupportedOperationException("Update not supported, use insert instead, tables construct with ON CONFLICT REPLACE system");
        }

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
                break;
            case BUG_ASSIGNATION:
                returnCount = BugAssignationCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case TIMELINE:
                returnCount = TimelineCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case TIMELINE_MESSAGES:
                returnCount = TimelineMessageCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case CLOUD:
                returnCount = CloudCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case EVENT:
                returnCount = EventCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            case EVENT_TYPE:
                returnCount = EventTypeCursors.bulkInsert(uri, values, mOpenHelper);
                break;
            default:
                return super.bulkInsert(uri, values);
        }
        if (getContext() != null && getContext().getContentResolver() != null)
            getContext().getContentResolver().notifyChange(uri, null);
        return returnCount;
    }
}
