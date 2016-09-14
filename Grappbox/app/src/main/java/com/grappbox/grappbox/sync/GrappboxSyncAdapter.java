package com.grappbox.grappbox.sync;

import android.accounts.Account;
import android.accounts.AccountManager;
import android.accounts.AccountManagerFuture;
import android.accounts.AuthenticatorException;
import android.accounts.OperationCanceledException;
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
import android.os.Bundle;
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
import java.net.URL;
import java.text.DateFormat;
import java.text.ParseException;
import java.util.Calendar;

/**
 * Created by marcw on 30/08/2016.
 */

public class GrappboxSyncAdapter extends AbstractThreadedSyncAdapter {
    private static final String LOG_TAG = GrappboxSyncAdapter.class.getSimpleName();
    private static final String[] accountsProjection = {
            UserEntry._ID
    };

    private static final int _ID = 0;

    private static final int SYNC_INTERVAL = 3600;
    private static final int SYNC_FLEXTIME = SYNC_INTERVAL / 2;

    public GrappboxSyncAdapter(Context context, boolean autoInitialize) {
        super(context, autoInitialize);
    }

    private Pair<Integer, Integer> syncProjectInfos(String apiToken, String apiID) throws IOException, JSONException {
        //synchronize project's list
        HttpURLConnection connection = null;
        String returnedJson = null;

        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/dashboard/getprojectsglobalprogress/" + apiToken);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);

            if (returnedJson == null || returnedJson.isEmpty())
                return null;
            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json))
                return null;
            JSONArray projects = json.getJSONObject("data").getJSONArray("array");
            for (int i = 0; i < projects.length(); ++i) {
                if (!projects.getJSONObject(i).getString("project_id").equals(apiID))
                    continue;
                JSONObject current = projects.getJSONObject(i);
                return new Pair<>(current.getInt("number_bugs"), current.getInt("number_ongoing_tasks"));
            }
        } finally {
            if (connection != null)
                connection.disconnect();
        }
        return null;
    }

    private void syncAccountProject(String apiToken, long projectId, String accountName) {
        String selection = ProjectEntry.TABLE_NAME + "." + ProjectEntry._ID + "=? AND " + ProjectAccountEntry.TABLE_NAME + "." + ProjectAccountEntry.COLUMN_ACCOUNT_NAME + "=?";
        String[] selectionArgs = new String[]{
                String.valueOf(projectId),
                accountName
        };
        Cursor query_project = getContext().getContentResolver().query(ProjectAccountEntry.CONTENT_URI, null, selection, selectionArgs, null);

        if (query_project == null || query_project.getCount() == 0)
        {
            ContentValues value = new ContentValues();
            value.put(ProjectAccountEntry.COLUMN_PROJECT_LOCAL_ID, projectId);
            value.put(ProjectAccountEntry.COLUMN_ACCOUNT_NAME, accountName);
            getContext().getContentResolver().insert(ProjectAccountEntry.CONTENT_URI, value);
        }
        else
            query_project.close();
    }

    public void syncProjects(String apiToken, String accountName)
    {
        Log.d(LOG_TAG, "Sync projects started");
        //synchronize project's list
        HttpURLConnection connection = null;
        String returnedJson = null;

        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/user/getprojects/" + apiToken);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);

            if (returnedJson == null || returnedJson.isEmpty())
                return;
            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json))
                return;

            //update project's list
            JSONArray projects = json.getJSONObject("data").getJSONArray("array");
            if (projects.length() <= 0)
                return;
            for (int i = 0; i < projects.length(); ++i)
            {
                JSONObject project = projects.getJSONObject(i);
                ContentValues projectValue = new ContentValues();
                ContentValues userValue = new ContentValues();

                projectValue.put(ProjectEntry.COLUMN_GRAPPBOX_ID, project.getString("id"));
                projectValue.put(ProjectEntry.COLUMN_NAME, project.getString("name"));
                projectValue.put(ProjectEntry.COLUMN_DESCRIPTION, project.getString("description"));

                JSONObject creator = project.getJSONObject("creator");
                userValue.put(UserEntry.COLUMN_GRAPPBOX_ID, creator.getString("id"));
                userValue.put(UserEntry.COLUMN_FIRSTNAME, creator.getString("firstname"));
                userValue.put(UserEntry.COLUMN_LASTNAME, creator.getString("lastname"));

                Uri insertedUri = getContext().getContentResolver().insert(UserEntry.CONTENT_URI, userValue);
                if (insertedUri != null)
                {
                    long id = Long.valueOf(insertedUri.getLastPathSegment());
                    if (id <= 0)
                        continue;
                    projectValue.put(ProjectEntry.COLUMN_LOCAL_CREATOR_ID, id);
                }

                projectValue.put(ProjectEntry.COLUMN_CONTACT_PHONE, project.getString("phone"));
                projectValue.put(ProjectEntry.COLUMN_COMPANY_NAME, project.getString("company"));

                projectValue.putNull(ProjectEntry.COLUMN_URI_LOGO);
                JSONObject logoExpiration = project.isNull("logo") ? null : project.getJSONObject("logo");
                if (logoExpiration != null)
                    projectValue.put(ProjectEntry.COLUMN_DATE_LOGO_LAST_EDITED_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(logoExpiration.getString("date")).getTime());
                else
                    projectValue.putNull(ProjectEntry.COLUMN_DATE_LOGO_LAST_EDITED_UTC);

                projectValue.put(ProjectEntry.COLUMN_CONTACT_EMAIL, project.getString("contact_mail"));
                projectValue.put(ProjectEntry.COLUMN_SOCIAL_FACEBOOK, project.getString("facebook"));
                projectValue.put(ProjectEntry.COLUMN_SOCIAL_TWITTER, project.getString("twitter"));
                JSONObject dateDeletion = project.isNull("deleted_at") ? null : project.getJSONObject("deleted_at");
                if (dateDeletion == null)
                    projectValue.putNull(ProjectEntry.COLUMN_DATE_DELETED_UTC);
                else
                    projectValue.put(ProjectEntry.COLUMN_DATE_DELETED_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(dateDeletion.getString("date")).getTime());
                Pair<Integer, Integer> infosCount = syncProjectInfos(apiToken, project.getString("id"));
                if (infosCount != null) {
                    projectValue.put(ProjectEntry.COLUMN_COUNT_BUG, infosCount.first);
                    projectValue.put(ProjectEntry.COLUMN_COUNT_TASK, infosCount.second);
                }
                long projectId = Long.parseLong(getContext().getContentResolver().insert(ProjectEntry.CONTENT_URI, projectValue).getLastPathSegment());
                if (projectId == -1)
                    return;
                syncAccountProject(apiToken, projectId, accountName);
            }

        } catch (IOException e) {
            Log.e(LOG_TAG, "IOException : ", e);
        } catch (JSONException | ParseException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }

    public long syncAccountUser(String apiToken, Account account) throws IOException, JSONException, OperationApplicationException {
        Log.d(LOG_TAG, "Sync projects started");
        final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/user/getidbyemail/" + apiToken + "/" + account.name);
        HttpURLConnection connection;
        String returnedJson;

        connection = (HttpURLConnection) url.openConnection();
        connection.setRequestMethod("GET");
        connection.connect();
        returnedJson = Utils.JSON.readDataFromConnection(connection);

        if (returnedJson == null || returnedJson.isEmpty())
            throw new JSONException("No json returned by API");
        JSONObject json = new JSONObject(returnedJson);
        if (Utils.Errors.checkAPIError(json))
            throw new OperationApplicationException("Api returned an error, stoping sync...");
        JSONObject data = json.getJSONObject("data");
        ContentValues user = new ContentValues();

        user.put(UserEntry.COLUMN_GRAPPBOX_ID, data.getString("id"));
        user.put(UserEntry.COLUMN_FIRSTNAME, data.getString("firstname"));
        user.put(UserEntry.COLUMN_LASTNAME, data.getString("lastname"));
        user.put(UserEntry.COLUMN_CONTACT_EMAIL, account.name);
        Uri uri = getContext().getContentResolver().insert(UserEntry.CONTENT_URI, user);
        if (uri == null || uri.getLastPathSegment().isEmpty() || Long.parseLong(uri.getLastPathSegment()) == -1)
            throw new SQLiteAbortException("Insert account user failed");
        return (Long.parseLong(uri.getLastPathSegment()));
    }

    public void syncUserList(String apiToken, String apiProjectId) {
        Log.d(LOG_TAG, "Sync projects started");
        HttpURLConnection connection = null;
        String returnedJson;

        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/projects/getusertoproject/" + apiToken + "/" + apiProjectId);
            connection = (HttpURLConnection) url.openConnection();
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
            ContentValues[] usersValues = new ContentValues[users.length()];
            for (int i = 0; i < users.length(); ++i) {
                ContentValues userValue = new ContentValues();
                JSONObject currentUser = users.getJSONObject(i);
                userValue.put(UserEntry.COLUMN_GRAPPBOX_ID, currentUser.getString("id"));
                userValue.put(UserEntry.COLUMN_FIRSTNAME, currentUser.getString("firstname"));
                userValue.put(UserEntry.COLUMN_LASTNAME, currentUser.getString("lastname"));
                usersValues[i] = userValue;
            }
            getContext().getContentResolver().bulkInsert(UserEntry.CONTENT_URI, usersValues);
        } catch (IOException e) {
            Log.e(LOG_TAG, "IOException : ", e);
        } catch (JSONException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }

    public void syncUsers(String apiToken)
    {
        Log.d(LOG_TAG, "Sync projects started");
        //synchronize User's list
        final String[] projection = new String[] {ProjectEntry.COLUMN_GRAPPBOX_ID};
        final int GRAPPBOX_ID = 0;

        Cursor allProjects = getContext().getContentResolver().query(ProjectEntry.CONTENT_URI, projection, null, null, null);
        Cursor allUsers = null;
        if (allProjects == null || !allProjects.moveToFirst())
            return;
        try {
            do {
                String apiProjectID = allProjects.getString(GRAPPBOX_ID);
                syncUserList(apiToken, apiProjectID);
            } while (allProjects.moveToNext());
            allUsers = getContext().getContentResolver().query(UserEntry.CONTENT_URI, new String[] {UserEntry.COLUMN_GRAPPBOX_ID}, null, null, null);
            if (allUsers != null && allUsers.moveToFirst()){
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

    public void syncConnectedUserRole(String apiToken, long uid) {
        Log.d(LOG_TAG, "Sync projects started");
        HttpURLConnection connection = null;
        String returnedJson = null;

        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/roles/getuserrolesinformations/" + apiToken);
            connection = (HttpURLConnection) url.openConnection();
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
            ContentValues[] rolesAssignationsValues = new ContentValues[roles.length()];
            for (int i = 0; i < roles.length(); ++i) {
                ContentValues roleValue = new ContentValues();
                ContentValues roleAssignationValue = new ContentValues();
                JSONObject currentRole = roles.getJSONObject(i).getJSONObject("role");
                JSONObject currentProject = roles.getJSONObject(i).getJSONObject("project");
                JSONObject currentValues = currentRole.getJSONObject("values");
                Cursor projectCursor = getContext().getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry._ID}, ProjectEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{currentProject.getString("id")}, null);
                if (projectCursor == null || !projectCursor.moveToFirst())
                    continue;
                roleValue.put(RolesEntry.COLUMN_GRAPPBOX_ID, currentRole.getString("id"));
                roleValue.put(RolesEntry.COLUMN_LOCAL_PROJECT_ID, projectCursor.getLong(0));
                roleValue.put(RolesEntry.COLUMN_NAME, currentRole.getString("name"));
                roleValue.put(RolesEntry.COLUMN_ACCESS_BUGTRACKER, currentValues.getString("bugtracker"));
                roleValue.put(RolesEntry.COLUMN_ACCESS_CLOUD, currentValues.getString("cloud"));
                roleValue.put(RolesEntry.COLUMN_ACCESS_CUSTOMER_TIMELINE, currentValues.getString("customerTimeline"));
                roleValue.put(RolesEntry.COLUMN_ACCESS_TEAM_TIMELINE, currentValues.getString("teamTimeline"));
                roleValue.put(RolesEntry.COLUMN_ACCESS_EVENT, currentValues.getString("event"));
                roleValue.put(RolesEntry.COLUMN_ACCESS_GANTT, currentValues.getString("gantt"));
                roleValue.put(RolesEntry.COLUMN_ACCESS_WHITEBOARD, currentValues.getString("whiteboard"));
                roleValue.put(RolesEntry.COLUMN_ACCESS_TASK, currentValues.getString("task"));
                roleValue.put(RolesEntry.COLUMN_ACCESS_PROJECT_SETTINGS, currentValues.getString("projectSettings"));
                Uri returnedUri = getContext().getContentResolver().insert(RolesEntry.CONTENT_URI, roleValue);
                if (returnedUri == null)
                    continue;
                long id = Long.valueOf(returnedUri.getLastPathSegment());
                if (id <= 0)
                    continue;
                roleAssignationValue.put(RolesAssignationEntry.COLUMN_LOCAL_ROLE_ID, id);
                roleAssignationValue.put(RolesAssignationEntry.COLUMN_LOCAL_USER_ID, uid);
                rolesAssignationsValues[i] = roleAssignationValue;
                projectCursor.close();
            }
            getContext().getContentResolver().bulkInsert(RolesAssignationEntry.CONTENT_URI, rolesAssignationsValues);
        } catch (IOException e) {
            Log.e(LOG_TAG, "IOException : ", e);
        } catch (JSONException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }

    public void syncBug(String apiToken, long projectId, long uid)
    {
        Log.d(LOG_TAG, "Sync projects started");
        Intent launchBugSyncing = new Intent(getContext(), GrappboxJustInTimeService.class);
        launchBugSyncing.setAction(GrappboxJustInTimeService.ACTION_SYNC_BUGS);
        launchBugSyncing.putExtra(GrappboxJustInTimeService.EXTRA_API_TOKEN, apiToken);
        launchBugSyncing.putExtra(GrappboxJustInTimeService.EXTRA_USER_ID, uid);
        launchBugSyncing.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, projectId);
        getContext().startService(launchBugSyncing);
    }

    public void syncTimeline(String apiToken, long projectId)
    {
        Log.d(LOG_TAG, "Sync projects started");
        //synchronize Timeline's list
        Cursor grappboxProjectIdCursor = getContext().getContentResolver().query(ProjectEntry.buildProjectWithLocalIdUri(projectId), new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, null, null, null);
        if (grappboxProjectIdCursor == null || !grappboxProjectIdCursor.moveToFirst())
            return;
        HttpURLConnection connection = null;
        String returnedJson = "";
        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/timeline/gettimelines/" + apiToken + "/" + grappboxProjectIdCursor.getString(0));
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty())
                return;

            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json))
                return;
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
                    return;
                long timelineId = Long.parseLong(timelineURI.getLastPathSegment());

                //Launch sync-ing last messages
                Intent launchTimelineMessageSync = new Intent(getContext(), GrappboxJustInTimeService.class);
                launchTimelineMessageSync.setAction(GrappboxJustInTimeService.ACTION_SYNC_TIMELINE_MESSAGES);
                launchTimelineMessageSync.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_ID, timelineId);
                launchTimelineMessageSync.putExtra(GrappboxJustInTimeService.EXTRA_API_TOKEN, apiToken);
                launchTimelineMessageSync.putExtra(GrappboxJustInTimeService.EXTRA_OFFSET, 0);
                launchTimelineMessageSync.putExtra(GrappboxJustInTimeService.EXTRA_LIMIT, 100);
                getContext().startService(launchTimelineMessageSync);
            }

        } catch (IOException e) {
            Log.e(LOG_TAG, "IOException : ", e);
        } catch (JSONException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            grappboxProjectIdCursor.close();
        }
    }

    public void syncNextMeeting(String apiToken, long projectId)
    {
        Log.d(LOG_TAG, "Sync projects started");
        //synchronize next meeting's informations
        Intent launchNextMeetingSyncing = new Intent(getContext(), GrappboxJustInTimeService.class);
        launchNextMeetingSyncing.setAction(GrappboxJustInTimeService.ACTION_SYNC_NEXT_MEETINGS);
        launchNextMeetingSyncing.putExtra(GrappboxJustInTimeService.EXTRA_API_TOKEN, apiToken);
        launchNextMeetingSyncing.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, projectId);
        getContext().startService(launchNextMeetingSyncing);
    }

    @Override
    public void onPerformSync(Account account, Bundle bundle, String s, ContentProviderClient contentProviderClient, SyncResult syncResult) {
        Log.d(LOG_TAG, "Sync Started");
        if (!Utils.Network.haveInternetConnection(getContext())){
            Log.d(LOG_TAG, "No internet connection, stop syncing...");
            return;
        }

        AccountManager am = AccountManager.get(getContext());
        Calendar today = Calendar.getInstance();
        Calendar dateExpiration = Calendar.getInstance();
        Cursor accountInfos = null, projectsCursor = null;
        long uid;
        try {
            dateExpiration.setTimeInMillis(Long.parseLong(am.getUserData(account, Session.ACCOUNT_EXPIRATION_TOKEN)));
            String token = am.getUserData(account, GrappboxJustInTimeService.EXTRA_API_TOKEN);

            //Control token validity and initialize current account details
            Log.d(LOG_TAG, "Control token validity token = " + token);
            if (token == null || token.isEmpty() || dateExpiration.getTimeInMillis() <= today.getTimeInMillis() + (3600 * 1000 * 2)){
                Log.d(LOG_TAG, "Invalid token, retreive it");
                AccountManagerFuture<Bundle> apiCredentials = am.getAuthToken(account, null, null, true, null, null);
                if (apiCredentials.getResult().containsKey(AccountManager.KEY_INTENT))
                    throw new AuthenticatorException("Authenticator need credential input by user, canceling sync...");
                token = apiCredentials.getResult().getString(AccountManager.KEY_AUTHTOKEN);
                if (token == null || token.isEmpty())
                    throw new AuthenticatorException("Returned token is null or empty [token = " + (token == null ? "(null)" : "(empty)") + "]");
                Log.d(LOG_TAG, "Token retreived : " + token);
                uid = syncAccountUser(token, account);
                Log.d(LOG_TAG, "Account user synced UID = " + uid);
            } else {
                Log.d(LOG_TAG, "Current token valid, retreiving user detail from db");
                accountInfos = getContext().getContentResolver().query(UserEntry.CONTENT_URI, accountsProjection, UserEntry.COLUMN_CONTACT_EMAIL + "=?", new String[]{account.name}, null);
                if (accountInfos == null || !accountInfos.moveToFirst()){
                    Log.d(LOG_TAG, "user not found in db, registering");
                    uid = syncAccountUser(token, account);
                } else {
                    uid = accountInfos.getLong(_ID);
                }
                Log.d(LOG_TAG, "User retreived, UID = " + uid);
            }
            syncProjects(token, account.name);
            Log.d(LOG_TAG, "Projects synced");
            syncUsers(token);
            Log.d(LOG_TAG, "Users synced");
            syncConnectedUserRole(token, uid);
            Log.d(LOG_TAG, "Connected user role synced");
            projectsCursor =  getContext().getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry._ID}, null, null, null);
            if (projectsCursor == null || !projectsCursor.moveToFirst()){
                Log.e(LOG_TAG, "No projects found in DB!");
                return;
            }

            do {
                long projectId = projectsCursor.getLong(0);

                syncNextMeeting(token, projectId);
                syncBug(token, projectId, uid);
                syncTimeline(token, projectId);
            } while (projectsCursor.moveToNext());

        } catch (IOException | JSONException | OperationApplicationException e) {
            e.printStackTrace();
        } catch(AuthenticatorException | OperationCanceledException e) {
            Log.e(LOG_TAG, "Authenticator failed :" + e.getMessage());
            e.printStackTrace();
        } finally{
            if (accountInfos != null)
                accountInfos.close();
            if (projectsCursor != null)
                projectsCursor.close();
        }
        Log.d(LOG_TAG, "Sync ended");
    }

    public static void configurePeriodicSync(Context context, int syncInterval, int flexTime) {
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

    public static Account[] getSyncAccounts(Context context) {
        return AccountManager.get(context).getAccountsByType(context.getString(R.string.sync_account_type));
    }

    public static void syncNow(Account account, Context context) {
        Log.d(LOG_TAG, "SyncNow called");
        Bundle bundle = new Bundle();
        bundle.putBoolean(ContentResolver.SYNC_EXTRAS_EXPEDITED, true);
        bundle.putBoolean(ContentResolver.SYNC_EXTRAS_MANUAL, true);

        ContentResolver.requestSync(account, context.getString(R.string.content_authority), bundle);
    }


}
