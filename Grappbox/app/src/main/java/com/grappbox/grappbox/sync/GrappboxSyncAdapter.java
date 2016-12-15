package com.grappbox.grappbox.sync;

import android.accounts.Account;
import android.accounts.AccountManager;
import android.accounts.AuthenticatorException;
import android.accounts.NetworkErrorException;
import android.content.AbstractThreadedSyncAdapter;
import android.content.ContentProviderClient;
import android.content.ContentResolver;
import android.content.ContentValues;
import android.content.Context;
import android.content.Intent;
import android.content.OperationApplicationException;
import android.content.SyncRequest;
import android.content.SyncResult;
import android.database.Cursor;
import android.database.SQLException;
import android.database.sqlite.SQLiteAbortException;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.support.annotation.NonNull;
import android.support.annotation.Nullable;
import android.util.Log;
import android.util.Pair;

import com.grappbox.grappbox.BuildConfig;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.data.GrappboxContract.ProjectAccountEntry;
import com.grappbox.grappbox.data.GrappboxContract.ProjectEntry;
import com.grappbox.grappbox.data.GrappboxContract.RolesAssignationEntry;
import com.grappbox.grappbox.data.GrappboxContract.RolesEntry;
import com.grappbox.grappbox.data.GrappboxContract.TimelineEntry;
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;
import com.grappbox.grappbox.singleton.Session;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.ProtocolException;
import java.net.URL;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;

/**
 * Created by Marc Wieser on 30/08/2016.
 * If you have any question or problem with this work
 * please contact the author at marc.wieser33@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

public class GrappboxSyncAdapter extends AbstractThreadedSyncAdapter {
    private static final String LOG_TAG = GrappboxSyncAdapter.class.getSimpleName();

    private static final int SYNC_INTERVAL = 3600;
    private static final int SYNC_FLEXTIME = SYNC_INTERVAL / 2;

    GrappboxSyncAdapter(Context context, boolean autoInitialize) {
        super(context, autoInitialize);
    }

    @Nullable
    private Pair<String, Long> syncProjectInfos(String apiToken, String apiID) throws IOException, JSONException {
        HttpURLConnection connection = null;
        String returnedJson;
        Pair<String, Long> ret = null;
        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/project/" + apiID);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty())
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json))
                throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
            JSONObject data = json.getJSONObject("data");
            JSONObject creator = data.getJSONObject("creator");
            Cursor userCreator = getContext().getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{creator.getString("id")}, null);
            if (userCreator == null || !userCreator.moveToFirst()){
                ContentValues newUser = new ContentValues();
                newUser.put(UserEntry.COLUMN_FIRSTNAME, creator.getString("firstname"));
                newUser.put(UserEntry.COLUMN_LASTNAME, creator.getString("lastname"));
                newUser.put(UserEntry.COLUMN_GRAPPBOX_ID, creator.getString("id"));
                getContext().getContentResolver().insert(UserEntry.CONTENT_URI, newUser);
                userCreator = getContext().getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{creator.getString("id")}, null);
                if (userCreator == null || !userCreator.moveToFirst())
                    throw new SQLException("Insert failed");
                ret = new Pair<>(data.getString("color"), userCreator.getLong(0));
                userCreator.close();
            } else {
                ret = new Pair<>(data.getString("color"), userCreator.getLong(0));
                userCreator.close();
            }
        } catch (IOException | JSONException | NetworkErrorException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
        return ret;
    }

    private void syncAccountProject(long projectId, String accountName) {
        String selection = ProjectEntry.TABLE_NAME + "." + ProjectEntry._ID + "=? AND " + ProjectAccountEntry.TABLE_NAME + "." + ProjectAccountEntry.COLUMN_ACCOUNT_NAME + "=?";
        String[] selectionArgs = new String[]{
                String.valueOf(projectId),
                accountName
        };
        Cursor query_project = getContext().getContentResolver().query(ProjectAccountEntry.CONTENT_URI, null, selection, selectionArgs, null);

        if (query_project == null || query_project.getCount() == 0) {
            ContentValues value = new ContentValues();
            value.put(ProjectAccountEntry.COLUMN_PROJECT_LOCAL_ID, projectId);
            value.put(ProjectAccountEntry.COLUMN_ACCOUNT_NAME, accountName);
            getContext().getContentResolver().insert(ProjectAccountEntry.CONTENT_URI, value);
        }
        if (query_project != null)
            query_project.close();
    }

    private void syncProjects(String apiToken, String accountName) {

        //synchronize project's list
        HttpURLConnection connection = null;
        String returnedJson;

        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/dashboard/projects");
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            Log.v(LOG_TAG, "syncProjects : " + url.toString());
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty())
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json))
                throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
            JSONArray projects = json.getJSONObject("data").getJSONArray("array");
            for (int i = 0; i < projects.length(); ++i) {
                JSONObject current = projects.getJSONObject(i);
                ContentValues value = new ContentValues();

                value.put(ProjectEntry.COLUMN_GRAPPBOX_ID, current.getString("id"));
                value.put(ProjectEntry.COLUMN_NAME, current.getString("name"));
                value.put(ProjectEntry.COLUMN_DESCRIPTION, current.getString("description"));
                value.put(ProjectEntry.COLUMN_CONTACT_PHONE, current.getString("phone"));
                value.put(ProjectEntry.COLUMN_COMPANY_NAME, current.getString("company"));
                value.putNull(ProjectEntry.COLUMN_URI_LOGO);
                value.putNull(ProjectEntry.COLUMN_DATE_LOGO_LAST_EDITED_UTC);
                value.put(ProjectEntry.COLUMN_CONTACT_EMAIL, current.getString("contact_mail"));
                value.put(ProjectEntry.COLUMN_SOCIAL_FACEBOOK, current.getString("facebook"));
                value.put(ProjectEntry.COLUMN_SOCIAL_TWITTER, current.getString("twitter"));
                if (current.isNull("deleted_at"))
                    value.putNull(ProjectEntry.COLUMN_DATE_DELETED_UTC);
                else
                    value.put(ProjectEntry.COLUMN_DATE_DELETED_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(current.getString("deleted_at")));
                value.put(ProjectEntry.COLUMN_COUNT_BUG, current.getString("number_bugs"));
                value.put(ProjectEntry.COLUMN_COUNT_TASK, current.getString("number_ongoing_tasks"));
                Pair<String, Long> additionalData = syncProjectInfos(apiToken, current.getString("id"));
                if (additionalData == null)
                    continue;
                value.put(ProjectEntry.COLUMN_COLOR, additionalData.first);
                value.put(ProjectEntry.COLUMN_LOCAL_CREATOR_ID, additionalData.second);
                Uri insertedProject = getContext().getContentResolver().insert(ProjectEntry.CONTENT_URI, value);
                if (insertedProject == null)
                    continue;
                syncAccountProject(Long.valueOf(insertedProject.getLastPathSegment()), accountName);
            }
        } catch (IOException | JSONException | NetworkErrorException | ParseException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }

    private long syncAccountUser(String apiToken, Account account) throws IOException, JSONException, OperationApplicationException {
        final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/user/id/"+ account.name);
        HttpURLConnection connection;
        String returnedJson;

        connection = (HttpURLConnection) url.openConnection();
        connection.setRequestProperty("Authorization", apiToken);
        connection.setRequestMethod("GET");
        connection.connect();
        returnedJson = Utils.JSON.readDataFromConnection(connection);

        if (returnedJson == null || returnedJson.isEmpty())
            throw new JSONException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
        JSONObject json = new JSONObject(returnedJson);
        if (Utils.Errors.checkAPIError(json))
            throw new OperationApplicationException(Utils.Errors.ERROR_API_GENERIC);
        JSONObject data = json.getJSONObject("data");
        ContentValues user = new ContentValues();

        user.put(UserEntry.COLUMN_GRAPPBOX_ID, data.getString("id"));
        user.put(UserEntry.COLUMN_FIRSTNAME, data.getString("firstname"));
        user.put(UserEntry.COLUMN_LASTNAME, data.getString("lastname"));
        user.put(UserEntry.COLUMN_CONTACT_EMAIL, account.name);
        Uri uri = getContext().getContentResolver().insert(UserEntry.CONTENT_URI, user);
        if (uri == null || uri.getLastPathSegment().isEmpty() || Long.parseLong(uri.getLastPathSegment()) == -1)
            throw new SQLiteAbortException("Insert account user failed");
        Log.d(LOG_TAG, uri.toString());
        long localUID = Long.parseLong(uri.getLastPathSegment());
        AccountManager am = AccountManager.get(getContext());
        am.setUserData(account, GrappboxJustInTimeService.EXTRA_USER_ID, String.valueOf(localUID));
        return (localUID);
    }

    private void syncUserList(String apiToken, String apiProjectId, long pid) {
        HttpURLConnection connection = null;
        String returnedJson;

        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/project/users/"+ apiProjectId);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);

            if (returnedJson == null || returnedJson.isEmpty())
                return;
            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json))
                return;

            JSONArray users = json.getJSONObject("data").getJSONArray("array");
            if (users.length() == 0)
                return;
            for (int i = 0; i < users.length(); ++i) {
                ContentValues userValue = new ContentValues();
                JSONObject currentUser = users.getJSONObject(i);
                userValue.put(UserEntry.COLUMN_GRAPPBOX_ID, currentUser.getString("id"));
                userValue.put(UserEntry.COLUMN_FIRSTNAME, currentUser.getString("firstname"));
                userValue.put(UserEntry.COLUMN_LASTNAME, currentUser.getString("lastname"));
                Uri newUsr = getContext().getContentResolver().insert(UserEntry.CONTENT_URI, userValue);
                if (newUsr == null)
                    throw new SQLException("Content provider return invalid URI on insert");
                long id = Long.parseLong(newUsr.getLastPathSegment());
                syncProjectUserRole(apiToken, pid, id);
            }
        } catch (IOException e) {
            Log.e(LOG_TAG, "IOException : ", e);
        } catch (JSONException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }

    private void syncUsers(String apiToken) {
        //synchronize User's list
        final String[] projection = new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID, ProjectEntry._ID};
        final int GRAPPBOX_ID = 0;
        final int _ID = 1;

        Cursor allProjects = getContext().getContentResolver().query(ProjectEntry.CONTENT_URI, projection, null, null, null);
        Cursor allUsers = null;
        if (allProjects == null || !allProjects.moveToFirst())
            return;
        try {
            do {
                String apiProjectID = allProjects.getString(GRAPPBOX_ID);
                syncUserList(apiToken, apiProjectID, allProjects.getLong(_ID));
            } while (allProjects.moveToNext());
            allUsers = getContext().getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry.COLUMN_GRAPPBOX_ID}, null, null, null);
            if (allUsers != null && allUsers.moveToFirst()) {
                do {
                    Intent userDetailLaunch = new Intent(getContext(), GrappboxJustInTimeService.class);
                    userDetailLaunch.addCategory(GrappboxJustInTimeService.CATEGORY_GRAPPBOX_ID);
                    userDetailLaunch.setAction(GrappboxJustInTimeService.ACTION_SYNC_USER_DETAIL);
                    userDetailLaunch.putExtra(GrappboxJustInTimeService.EXTRA_API_TOKEN, apiToken);
                    userDetailLaunch.putExtra(GrappboxJustInTimeService.EXTRA_USER_ID, allUsers.getString(0));

                    getContext().startService(userDetailLaunch);
                } while (allUsers.moveToNext());
            }
        } finally {
            allProjects.close();
            if (allUsers != null)
                allUsers.close();
        }
    }

    private void syncProjectUserRole(String apiToken, long pid, long uid){
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null;
        Cursor user = null;

        try {
            project = getContext().getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID+"=?", new String[]{String.valueOf(pid)}, null);
            user = getContext().getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry.COLUMN_GRAPPBOX_ID}, UserEntry._ID+"=?", new String[]{String.valueOf(uid)}, null);
            if (project == null || user == null || !project.moveToFirst() || !user.moveToFirst())
                throw new SQLException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/roles/project/user/"+ project.getString(0) + "/" + user.getString(0));
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty())
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);

            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json))
                throw new OperationApplicationException(Utils.Errors.ERROR_API_GENERIC);
            JSONObject currentRole = json.getJSONObject("data");

            ContentValues roleValue = new ContentValues();
            ContentValues roleAssignationValue = new ContentValues();
            roleValue.put(RolesEntry.COLUMN_GRAPPBOX_ID, currentRole.getString("roleId"));
            roleValue.put(RolesEntry.COLUMN_LOCAL_PROJECT_ID, pid);
            roleValue.put(RolesEntry.COLUMN_NAME, currentRole.getString("name"));
            roleValue.put(RolesEntry.COLUMN_ACCESS_BUGTRACKER, currentRole.getString("bugtracker"));
            roleValue.put(RolesEntry.COLUMN_ACCESS_CLOUD, currentRole.getString("cloud"));
            roleValue.put(RolesEntry.COLUMN_ACCESS_CUSTOMER_TIMELINE, currentRole.getString("customerTimeline"));
            roleValue.put(RolesEntry.COLUMN_ACCESS_TEAM_TIMELINE, currentRole.getString("teamTimeline"));
            roleValue.put(RolesEntry.COLUMN_ACCESS_EVENT, currentRole.getString("event"));
            roleValue.put(RolesEntry.COLUMN_ACCESS_GANTT, currentRole.getString("gantt"));
            roleValue.put(RolesEntry.COLUMN_ACCESS_WHITEBOARD, currentRole.getString("whiteboard"));
            roleValue.put(RolesEntry.COLUMN_ACCESS_TASK, currentRole.getString("task"));
            roleValue.put(RolesEntry.COLUMN_ACCESS_PROJECT_SETTINGS, currentRole.getString("projectSettings"));
            Uri returnedUri = getContext().getContentResolver().insert(RolesEntry.CONTENT_URI, roleValue);
            if (returnedUri == null)
                throw new SQLException(Utils.Errors.ERROR_SQL_INSERT_FAILED);
            long id = Long.valueOf(returnedUri.getLastPathSegment());
            if (id <= 0)
                throw new SQLException(Utils.Errors.ERROR_SQL_INSERT_FAILED);
            roleAssignationValue.put(RolesAssignationEntry.COLUMN_LOCAL_ROLE_ID, id);
            roleAssignationValue.put(RolesAssignationEntry.COLUMN_LOCAL_USER_ID, uid);
            Cursor roleUser = getContext().getContentResolver().query(RolesAssignationEntry.CONTENT_URI, new String[]{RolesAssignationEntry.TABLE_NAME + "." + RolesAssignationEntry._ID}, RolesAssignationEntry.TABLE_NAME + "." + RolesAssignationEntry.COLUMN_LOCAL_ROLE_ID+"=? AND " + RolesAssignationEntry.TABLE_NAME + "." + RolesAssignationEntry.COLUMN_LOCAL_USER_ID+"=?", new String[]{String.valueOf(id), String.valueOf(uid)}, null);
            if (roleUser == null || !roleUser.moveToFirst() || roleUser.getCount() <= 0){
                getContext().getContentResolver().insert(RolesAssignationEntry.CONTENT_URI, roleAssignationValue);
            } else {
                roleUser.close();
            }
        } catch (IOException | JSONException | OperationApplicationException | NetworkErrorException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null)
                project.close();
            if (user != null)
                user.close();
        }
    }

    private void syncConnectedUserRole(String apiToken, long uid) {
        HttpURLConnection connection = null;
        String returnedJson;

        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/roles/user");
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty())
                return;

            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json))
                return;
            JSONArray roles = json.getJSONObject("data").getJSONArray("array");

            if (roles.length() == 0)
                return;
            for (int i = 0; i < roles.length(); ++i) {
                ContentValues roleValue = new ContentValues();
                ContentValues roleAssignationValue = new ContentValues();
                JSONObject currentRole = roles.getJSONObject(i);
                Cursor projectCursor = getContext().getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry._ID}, ProjectEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{currentRole.getString("projectId")}, null);
                if (projectCursor == null || !projectCursor.moveToFirst())
                    continue;
                roleValue.put(RolesEntry.COLUMN_GRAPPBOX_ID, currentRole.getString("roleId"));
                roleValue.put(RolesEntry.COLUMN_LOCAL_PROJECT_ID, projectCursor.getLong(0));
                roleValue.put(RolesEntry.COLUMN_NAME, currentRole.getString("name"));
                roleValue.put(RolesEntry.COLUMN_ACCESS_BUGTRACKER, currentRole.getString("bugtracker"));
                roleValue.put(RolesEntry.COLUMN_ACCESS_CLOUD, currentRole.getString("cloud"));
                roleValue.put(RolesEntry.COLUMN_ACCESS_CUSTOMER_TIMELINE, currentRole.getString("customerTimeline"));
                roleValue.put(RolesEntry.COLUMN_ACCESS_TEAM_TIMELINE, currentRole.getString("teamTimeline"));
                roleValue.put(RolesEntry.COLUMN_ACCESS_EVENT, currentRole.getString("event"));
                roleValue.put(RolesEntry.COLUMN_ACCESS_GANTT, currentRole.getString("gantt"));
                roleValue.put(RolesEntry.COLUMN_ACCESS_WHITEBOARD, currentRole.getString("whiteboard"));
                roleValue.put(RolesEntry.COLUMN_ACCESS_TASK, currentRole.getString("task"));
                roleValue.put(RolesEntry.COLUMN_ACCESS_PROJECT_SETTINGS, currentRole.getString("projectSettings"));
                Uri returnedUri = getContext().getContentResolver().insert(RolesEntry.CONTENT_URI, roleValue);
                if (returnedUri == null)
                    continue;
                long id = Long.valueOf(returnedUri.getLastPathSegment());
                if (id <= 0)
                    continue;
                roleAssignationValue.put(RolesAssignationEntry.COLUMN_LOCAL_ROLE_ID, id);
                roleAssignationValue.put(RolesAssignationEntry.COLUMN_LOCAL_USER_ID, uid);
                Cursor roleUser = getContext().getContentResolver().query(RolesAssignationEntry.CONTENT_URI, new String[]{RolesAssignationEntry.TABLE_NAME + "." + RolesAssignationEntry._ID}, RolesAssignationEntry.TABLE_NAME + "." + RolesAssignationEntry.COLUMN_LOCAL_ROLE_ID+"=? AND " + RolesAssignationEntry.TABLE_NAME + "." + RolesAssignationEntry.COLUMN_LOCAL_USER_ID+"=?", new String[]{String.valueOf(id), String.valueOf(uid)}, null);
                if (roleUser == null || !roleUser.moveToFirst() || roleUser.getCount() <= 0){
                    getContext().getContentResolver().insert(RolesAssignationEntry.CONTENT_URI, roleAssignationValue);
                } else {
                    roleUser.close();
                }
                projectCursor.close();
            }
        } catch (IOException|JSONException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }

    private void syncBug(String apiToken, long projectId, long uid) {
        Intent launchBugSyncing = new Intent(getContext(), GrappboxJustInTimeService.class);
        launchBugSyncing.setAction(GrappboxJustInTimeService.ACTION_SYNC_BUGS);
        launchBugSyncing.putExtra(GrappboxJustInTimeService.EXTRA_API_TOKEN, apiToken);
        launchBugSyncing.putExtra(GrappboxJustInTimeService.EXTRA_USER_ID, uid);
        launchBugSyncing.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, projectId);
        getContext().startService(launchBugSyncing);
        launchBugSyncing.addCategory(GrappboxJustInTimeService.CATEGORY_CLOSED);
        getContext().startService(launchBugSyncing);

        Intent syncTags = new Intent(getContext(), GrappboxJustInTimeService.class);
        syncTags.setAction(GrappboxJustInTimeService.ACTION_SYNC_TAGS);
        syncTags.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, projectId);
        getContext().startService(syncTags);
    }

    private void syncPlanningMonth(String apiToken, int offsetMonth){
        Intent launchEventSync = new Intent(getContext(), GrappboxJustInTimeService.class);
        launchEventSync.setAction(GrappboxJustInTimeService.ACTION_SYNC_EVENT);
        launchEventSync.putExtra(GrappboxJustInTimeService.EXTRA_CALENDAR_MONTH_OFFSET, offsetMonth);
        getContext().startService(launchEventSync);
    }

    private void syncTimeline(String apiToken, long projectId) {
        //synchronize Timeline's list
        Cursor grappboxProjectIdCursor = getContext().getContentResolver().query(ProjectEntry.buildProjectWithLocalIdUri(projectId), new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, null, null, null);
        if (grappboxProjectIdCursor == null || !grappboxProjectIdCursor.moveToFirst())
            return;
        HttpURLConnection connection = null;
        String returnedJson;
        try {
            Log.v(LOG_TAG, "project ID : " + grappboxProjectIdCursor.getString(0) + ", column name : " + grappboxProjectIdCursor.getColumnName(0));
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/timelines/"+ grappboxProjectIdCursor.getString(0));
            Log.v(LOG_TAG, "url : " + url.toString());
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty())
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);

            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json))
                throw new OperationApplicationException(Utils.Errors.ERROR_API_GENERIC);
            JSONArray timelinesData = json.getJSONObject("data").getJSONArray("array");
            for (int i = 0; i < timelinesData.length(); ++i) {
                JSONObject currentTimeline = timelinesData.getJSONObject(i);
                ContentValues timeline = new ContentValues();

                timeline.put(TimelineEntry.COLUMN_GRAPPBOX_ID, currentTimeline.getString("id"));
                timeline.put(TimelineEntry.COLUMN_TYPE_ID, currentTimeline.getInt("typeId"));
                timeline.put(TimelineEntry.COLUMN_LOCAL_PROJECT_ID, projectId);
                timeline.put(TimelineEntry.COLUMN_NAME, currentTimeline.getString("name"));
                timeline.put(TimelineEntry.COLUMN_TYPE_NAME, currentTimeline.getString("typeName"));
                Uri timelineURI = getContext().getContentResolver().insert(TimelineEntry.CONTENT_URI, timeline);
                if (timelineURI == null)
                    continue;
                long timelineId = Long.parseLong(timelineURI.getLastPathSegment());

                //Launch sync-ing last messages
                Intent launchTimelineMessageSync = new Intent(getContext(), GrappboxJustInTimeService.class);
                launchTimelineMessageSync.setAction(GrappboxJustInTimeService.ACTION_SYNC_TIMELINE_MESSAGES);
                launchTimelineMessageSync.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_ID, timelineId);
                launchTimelineMessageSync.putExtra(GrappboxJustInTimeService.EXTRA_API_TOKEN, apiToken);
                launchTimelineMessageSync.putExtra(GrappboxJustInTimeService.EXTRA_OFFSET, 0);
                launchTimelineMessageSync.putExtra(GrappboxJustInTimeService.EXTRA_LIMIT, 50);
                getContext().startService(launchTimelineMessageSync);
            }

        } catch (IOException e) {
            Log.e(LOG_TAG, "IOException : ", e);
        } catch (JSONException | NetworkErrorException | OperationApplicationException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            grappboxProjectIdCursor.close();
        }
    }

    private void syncNextMeeting(String apiToken, long projectId) {
        //synchronize next meeting's informations
        Intent launchNextMeetingSyncing = new Intent(getContext(), GrappboxJustInTimeService.class);
        launchNextMeetingSyncing.setAction(GrappboxJustInTimeService.ACTION_SYNC_NEXT_MEETINGS);
        launchNextMeetingSyncing.putExtra(GrappboxJustInTimeService.EXTRA_API_TOKEN, apiToken);
        launchNextMeetingSyncing.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, projectId);
        getContext().startService(launchNextMeetingSyncing);
    }

    private void syncStats(String apiToken, long projectId) {
        //synchronyze the stats
        Intent launchAllStatSync = new Intent(getContext(), GrappboxJustInTimeService.class);
        launchAllStatSync.setAction(GrappboxJustInTimeService.ACTION_SYNC_ALL_STATS);
        launchAllStatSync.putExtra(GrappboxJustInTimeService.EXTRA_API_TOKEN, apiToken);
        launchAllStatSync.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, projectId);
        getContext().startService(launchAllStatSync);
    }

    @Override
    public void onPerformSync(Account account, Bundle bundle, String s, ContentProviderClient contentProviderClient, SyncResult syncResult) {
        Log.e(LOG_TAG, "Sync Started");
        if (!Utils.Network.haveInternetConnection(getContext())) {
            return;
        }

        AccountManager am = AccountManager.get(getContext());
        am.invalidateAuthToken(getContext().getString(R.string.sync_account_type), Utils.Account.getAuthTokenService(getContext(), account));
        Calendar dateExpiration = Calendar.getInstance();
        Cursor projectsCursor = null;
        long uid;
        try {
            dateExpiration.setTimeInMillis(Long.parseLong(am.getUserData(account, Session.ACCOUNT_EXPIRATION_TOKEN)));
            String token = Utils.Account.getAuthTokenService(this.getContext(), account);

            //Control token validity and initialize current account details
            if (token == null || token.isEmpty())
                throw new AuthenticatorException("Returned token is null or empty [token = " + (token == null ? "(null)" : "(empty)") + "]");
            uid = syncAccountUser(token, account);
            syncProjects(token, account.name);
            syncUsers(token);
            syncConnectedUserRole(token, uid);
            projectsCursor = getContext().getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry._ID}, null, null, null);
            if (projectsCursor == null || !projectsCursor.moveToFirst())
                throw new OperationApplicationException();
            do {
                long projectId = projectsCursor.getLong(0);

                /*syncNextMeeting(token, projectId);
                syncBug(token, projectId, uid);
                syncTimeline(token, projectId);
                syncPlanningMonth(token, -1);
                syncPlanningMonth(token, 0);
                syncPlanningMonth(token, 1);*/
                syncStats(token, projectId);
            } while (projectsCursor.moveToNext());

        } catch (IOException | JSONException | OperationApplicationException | AuthenticatorException e) {
            e.printStackTrace();
        } finally {
            if (projectsCursor != null)
                projectsCursor.close();
        }
        Log.e(LOG_TAG, "Sync ended");
    }

    private static void configurePeriodicSync(Context context, int syncInterval, int flexTime) {
        Account[] accounts = getSyncAccounts(context);
        String authority = context.getString(R.string.content_authority);

        for (Account account : accounts) {
            SyncRequest request = new SyncRequest.Builder().
                    syncPeriodic(syncInterval, flexTime).
                    setSyncAdapter(account, authority).
                    setExtras(new Bundle()).build();
            ContentResolver.requestSync(request);
        }
    }

    public static void onAccountAdded(Account newAccount, Context context) {
        GrappboxSyncAdapter.configurePeriodicSync(context, SYNC_INTERVAL, SYNC_FLEXTIME);
        ContentResolver.setSyncAutomatically(newAccount, context.getString(R.string.content_authority), true);
    }

    @NonNull
    private static Account[] getSyncAccounts(Context context) throws SecurityException {
        return AccountManager.get(context).getAccountsByType(context.getString(R.string.sync_account_type));
    }

    public static void syncNow(Account account, Context context) {
        Bundle bundle = new Bundle();
        bundle.putBoolean(ContentResolver.SYNC_EXTRAS_EXPEDITED, true);
        bundle.putBoolean(ContentResolver.SYNC_EXTRAS_MANUAL, true);

        ContentResolver.requestSync(account, context.getString(R.string.content_authority), bundle);
    }


}
