package com.grappbox.grappbox.sync;

import android.accounts.AccountManager;
import android.app.Activity;
import android.app.IntentService;
import android.content.ContentValues;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.database.Cursor;
import android.net.Uri;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v4.os.ResultReceiver;
import android.util.Log;
import android.util.Pair;

import com.grappbox.grappbox.BuildConfig;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.data.GrappboxContract.BugAssignationEntry;
import com.grappbox.grappbox.data.GrappboxContract.BugEntry;
import com.grappbox.grappbox.data.GrappboxContract.BugTagEntry;
import com.grappbox.grappbox.data.GrappboxContract.CloudEntry;
import com.grappbox.grappbox.data.GrappboxContract.EventEntry;
import com.grappbox.grappbox.data.GrappboxContract.EventTypeEntry;
import com.grappbox.grappbox.data.GrappboxContract.ProjectEntry;
import com.grappbox.grappbox.data.GrappboxContract.TagEntry;
import com.grappbox.grappbox.data.GrappboxContract.TimelineEntry;
import com.grappbox.grappbox.data.GrappboxContract.TimelineMessageEntry;
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;
import com.grappbox.grappbox.project_fragments.CloudFragment;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.net.HttpURLConnection;
import java.net.URL;
import java.text.ParseException;
import java.util.ArrayList;
import java.util.Date;
import java.util.Set;


    /**
     * An {@link IntentService} subclass for handling asynchronous task requests in
     * a service on a separate handler thread.
     */
    public class GrappboxJustInTimeService extends IntentService {
        private static final String LOG_TAG = GrappboxJustInTimeService.class.getSimpleName();

        public static final String ACTION_SYNC_USER_DETAIL = "com.grappbox.grappbox.sync.ACTION_SYNC_USER_DETAIL";
        public static final String ACTION_SYNC_BUGS = "com.grappbox.grappbox.sync.ACTION_SYNC_BUGS";
        public static final String ACTION_SYNC_TIMELINE_MESSAGES = "com.grappbox.grappbox.sync.ACTION_SYNC_TIMELINE_MESSAGES";
        public static final String ACTION_SYNC_NEXT_MEETINGS = "com.grappbox.grappbox.sync.ACTION_SYNC_NEXT_MEETINGS";
        public static final String ACTION_LOGIN = "com.grappbox.grappbox.sync.ACTION_LOGIN";
        public static final String ACTION_SYNC_PROJECT_LIST = "com.grappbox.grappbox.sync.ACTION_SYNC_PROJECT_LIST";
        public static final String ACTION_SYNC_CLOUD_PATH = "com.grappbox.grappbox.sync.ACTION_SYNC_CLOUD_PATH";
        public static final String ACTION_CLOUD_ADD_DIRECTORY = "com.grappbox.grappbox.sync.ACTION_CLOUD_ADD_DIRECTORY";
        public static final String ACTION_CLOUD_IMPORT_FILE = "com.grappbox.grappbox.sync.ACTION_CLOUD_IMPORT_FILE";

        public static final String EXTRA_API_TOKEN = "api_token";
        public static final String EXTRA_USER_ID = "uid";
        public static final String EXTRA_PROJECT_ID = "pid";
        public static final String EXTRA_OFFSET = "offset";
        public static final String EXTRA_LIMIT = "limit";
        public static final String EXTRA_TIMELINE_ID = "tid";
        public static final String EXTRA_RESPONSE_RECEIVER = "response_receiver";
        public static final String EXTRA_MAIL = "mail";
        public static final String EXTRA_CRYPTED_PASSWORD = "password";
        public static final String EXTRA_ACCOUNT_NAME = "account_name";
        public static final String EXTRA_CLOUD_PATH = "cloud_path";
        public static final String EXTRA_CLOUD_PASSWORD = "cloud_password";
        public static final String EXTRA_CLOUD_FILE_PASSWORD = "cloud_file_password";
        public static final String EXTRA_DIRECTORY_NAME = "dir_name";
        public static final String EXTRA_FILENAME = "filename";

    public static final String CATEGORY_GRAPPBOX_ID = "com.grappbox.grappbox.sync.CATEGORY_GRAPPBOX_ID";

    public static final String CATEGORY_LOCAL_ID = "com.grappbox.grappbox.sync.CATEGORY_LOCAL_ID";
    public static final String BUNDLE_KEY_JSON = "com.grappbox.grappbox.sync.BUNDLE_KEY_JSON";
    public static final String BUNDLE_KEY_ERROR_MSG = "com.grappbox.grappbox.sync.BUNDLE_KEY_ERROR_MSG";
    public static final String BUNDLE_KEY_ERROR_TYPE = "com.grappbox.grappbox.sync.BUNDLE_KEY_ERROR_TYPE";

    public GrappboxJustInTimeService() {
        super("GrappboxJustInTimeService");
    }

    @Override
    protected void onHandleIntent(Intent intent) {
        if (intent != null) {
            final String action = intent.getAction();
            ResultReceiver responseObserver = intent.hasExtra(EXTRA_RESPONSE_RECEIVER) ? (ResultReceiver) intent.getParcelableExtra(EXTRA_RESPONSE_RECEIVER) : null;
            if (ACTION_SYNC_USER_DETAIL.equals(action)){
                Set<String> categories = intent.getCategories();
                if (categories == null || categories.size() == 0 || categories.contains(CATEGORY_LOCAL_ID))
                    handleUserDetailSync(intent.getLongExtra(EXTRA_USER_ID, -1));
                else
                    handleUserDetailSync(intent.getStringExtra(EXTRA_USER_ID));
            }
            else if (ACTION_SYNC_BUGS.equals(action))
                handleBugsSync(intent.getLongExtra(EXTRA_USER_ID, -1), intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getIntExtra(EXTRA_OFFSET, 0), intent.getIntExtra(EXTRA_LIMIT, 50));
            else if (ACTION_SYNC_TIMELINE_MESSAGES.equals(action)) {
                handleTimelineMessagesSync(intent.getLongExtra(EXTRA_TIMELINE_ID, -1), intent.getIntExtra(EXTRA_OFFSET, 0), intent.getIntExtra(EXTRA_LIMIT, 50));
            }
            else if (ACTION_SYNC_NEXT_MEETINGS.equals(action)) {
                handleNextMeetingsSync(intent.getLongExtra(EXTRA_PROJECT_ID, -1));
            }
            else if (ACTION_LOGIN.equals(action)) {
                handleLogin(intent.getStringExtra(EXTRA_MAIL), Utils.Security.decryptString(intent.getStringExtra(EXTRA_CRYPTED_PASSWORD)), responseObserver);
            }
            else if (ACTION_SYNC_PROJECT_LIST.equals(action)){
                handleProjectListSync(intent.getStringExtra(EXTRA_ACCOUNT_NAME), responseObserver);
            }
            else if (ACTION_SYNC_CLOUD_PATH.equals(action)){
                handleCloudPathSync(intent.getStringExtra(EXTRA_CLOUD_PATH), intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getStringExtra(EXTRA_CLOUD_PASSWORD), responseObserver);
            }
            else if (ACTION_CLOUD_ADD_DIRECTORY.equals(action)){
                String password = intent.hasExtra(EXTRA_CLOUD_PASSWORD) ? intent.getStringExtra(EXTRA_CLOUD_PASSWORD) : null;
                handleCloudAddDirectory(intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getStringExtra(EXTRA_CLOUD_PATH), intent.getStringExtra(EXTRA_DIRECTORY_NAME), password, responseObserver);
            }
            else if (ACTION_CLOUD_IMPORT_FILE.equals(action)){
                String password = intent.hasExtra(EXTRA_CLOUD_FILE_PASSWORD) ? intent.getStringExtra(EXTRA_CLOUD_FILE_PASSWORD) : null;
                String passwordSafe = intent.hasExtra(EXTRA_CLOUD_PASSWORD) ? intent.getStringExtra(EXTRA_CLOUD_PASSWORD) : null;

                handleCloudImportFile(intent.getLongExtra(EXTRA_PROJECT_ID, 1), passwordSafe, intent.getStringExtra(EXTRA_CLOUD_PATH), intent.getData(), password);
            }
        }
    }

    private void handleCloudImportFile(long projectId, String passwordSafe, String cloudPath, Uri filename, String passwordFile){
        String apiToken = Utils.Account.getAuthTokenService(this, null);

        if (projectId == -1 || apiToken == null)
            return;

        Log.d(LOG_TAG, "File URI = " + filename);
    }

    private void handleCloudAddDirectory(long projectId, String path, String dirName, String passwordSafe, ResultReceiver responseObserver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);

        if (projectId == -1 || apiToken == null)
            return;

        Log.d(LOG_TAG, "REQUEST = handleCLoudAddDirectory");
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null;
        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/cloud/createdir");
            //Ask login
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID + "=?", new String[]{String.valueOf(projectId)}, null);
            if (project == null || !project.moveToFirst())
                throw new IllegalArgumentException("Invalid local project ID");
            data.put("token", apiToken);
            data.put("project_id", project.getString(0));
            data.put("path", path);
            data.put("dir_name", dirName);
            if (passwordSafe != null)
                data.put("passwordSafe", passwordSafe);
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("POST");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                if (responseObserver != null)
                {
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.0"));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null)
                    {
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        SharedPreferences prefs = getSharedPreferences(CloudFragment.CLOUD_SHARED_PREF, Context.MODE_PRIVATE);
                        prefs.edit().putString(CloudFragment.CLOUD_PREF_SAFE_BASE_KEY + projectId, null).apply();
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                } else {
                    handleCloudPathSync(path, projectId, passwordSafe, null);
                }
            }
        } catch (IOException | JSONException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null)
                project.close();
        }
    }

    private void handleCloudPathSync(String cloudPath, long projectId, String passwordSafe, ResultReceiver responseObserver) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);

        if (projectId == -1 || apiToken == null)
            return;
        Cursor project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID+"=?", new String[]{String.valueOf(projectId)}, null);
        if (project == null || !project.moveToFirst())
            return;
        cloudPath = cloudPath.replace('/', ',');
        cloudPath = cloudPath.replace(' ', '|');
        Log.d(LOG_TAG, "Cloud Sync started");
        cloudPath = cloudPath.replace('/', ',');
        HttpURLConnection connection = null;
        String returnedJson;

        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/cloud/list/"+apiToken+"/"+project.getString(0)+"/"+cloudPath+(passwordSafe == null || passwordSafe.isEmpty() ? "" : "/"+passwordSafe));
            //Ask login
            Log.d(LOG_TAG, "Start connection : " + url);
            JSONObject json;
            JSONObject data;


            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            Log.d(LOG_TAG, "Returned JSON : " + String.valueOf(returnedJson));
            if (returnedJson != null && !returnedJson.isEmpty()){
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null){
                        Bundle ans = new Bundle();
                        String errorCode =  json.getJSONObject("info").getString("return_code");
                        ans.putInt(BUNDLE_KEY_ERROR_TYPE, Integer.valueOf(errorCode.split("\\.")[2]));
                        ans.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, errorCode));
                        responseObserver.send(Activity.RESULT_CANCELED, ans);
                    }
                } else {
                    data = json.getJSONObject("data");
                    JSONArray array = data.getJSONArray("array");
                    if (array.length() != 0){
                        ArrayList<ContentValues> directory = new ArrayList<>();
                        for (int i = 0; i < array.length(); ++i){
                            JSONObject current = array.getJSONObject(i);
                            ContentValues value = new ContentValues();
                            String type = current.getString("type");
                            String filename = current.getString("filename");

                            if (type.equals("file")){
                                value.put(CloudEntry.COLUMN_TYPE, 0);
                                value.put(CloudEntry.COLUMN_SIZE, current.getLong("size"));
                                value.put(CloudEntry.COLUMN_MIMETYPE, current.getLong("mimetype"));
                                value.put(CloudEntry.COLUMN_DATE_LAST_EDITED_UTC, current.getLong("last_modified"));
                            } else if (filename.equals("Safe")){
                                value.put(CloudEntry.COLUMN_TYPE, 2);
                            } else {
                                value.put(CloudEntry.COLUMN_TYPE, 1);
                            }
                            filename = filename.replace('|', ' ');
                            cloudPath = cloudPath.replace(',', '/').replace('|', ' ');
                            value.put(CloudEntry.COLUMN_PATH, cloudPath);
                            value.put(CloudEntry.COLUMN_FILENAME, filename);
                            value.put(CloudEntry.COLUMN_IS_SECURED, current.getBoolean("is_secured"));
                            value.put(CloudEntry.COLUMN_LOCAL_PROJECT_ID, projectId);
                            Log.d(LOG_TAG, current.toString());
                            String checkSelection = CloudEntry.COLUMN_FILENAME + "=? AND " + CloudEntry.COLUMN_PATH + "=? AND " + CloudEntry.COLUMN_LOCAL_PROJECT_ID + "=?";
                            String[] checkArgs = new String[]{
                                    filename,
                                    cloudPath,
                                    String.valueOf(projectId)
                            };
                            Cursor check = getContentResolver().query(CloudEntry.CONTENT_URI, null, checkSelection, checkArgs, null);
                            if (check == null || !check.moveToFirst())
                                directory.add(value);
                            else
                                check.close();
                        }
                        getContentResolver().bulkInsert(CloudEntry.CONTENT_URI, directory.toArray(new ContentValues[directory.size()]));
                        if (responseObserver != null)
                            responseObserver.send(Activity.RESULT_OK, null);
                    }
                }
            }
        } catch (IOException | JSONException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (responseObserver != null)
                responseObserver.send(Activity.RESULT_OK, null);
        }
    }

    private void handleLogin(String mail, String password, @Nullable ResultReceiver responseObserver) {
        HttpURLConnection connection = null;
        String returnedJson;

        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/accountadministration/login");
            //Ask login
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();

            data.put("login",mail);
            data.put("password", password);
            json.put("data", data);
            Log.e("TEST", json.toString());
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("POST");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                if (responseObserver != null)
                    responseObserver.send(Activity.RESULT_CANCELED, null);
                return;
            }


            json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json)){
                if (responseObserver != null)
                    responseObserver.send(Activity.RESULT_CANCELED, null);
                return;
            }
            data = json.getJSONObject("data");
            Bundle answer = new Bundle();
            answer.putString(AccountManager.KEY_ACCOUNT_NAME, mail);
            answer.putString(AccountManager.KEY_ACCOUNT_TYPE, getString(R.string.sync_account_type));
            answer.putString(EXTRA_CRYPTED_PASSWORD, Utils.Security.cryptString(password));
            answer.putString(EXTRA_API_TOKEN, data.getString("token"));
            if (responseObserver != null)
                responseObserver.send(Activity.RESULT_OK, answer);
        } catch (IOException | JSONException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }

    private void handleBugsSync(long localUID, long localPID, int offset, int limit) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);

        if (apiToken.isEmpty() || localUID == -1 || localPID == -1)
            return;
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor projectRole = getContentResolver().query(GrappboxContract.RolesAssignationEntry.buildRoleAssignationWithUIDAndPID(localUID, localPID), null, null, null, null);
        Cursor grappboxProjectId = getContentResolver().query(ProjectEntry.buildProjectWithLocalIdUri(localPID), new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, null, null, null);

        if (projectRole == null || !projectRole.moveToFirst() || grappboxProjectId == null || !grappboxProjectId.moveToFirst())
            return;
        try {
            if (projectRole.getInt(projectRole.getColumnIndex(GrappboxContract.RolesEntry.COLUMN_ACCESS_BUGTRACKER)) < 1)
                return;
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/getlasttickets/"+apiToken+"/"+grappboxProjectId.getString(0)+"/"+String.valueOf(offset)+"/" + String.valueOf(limit));

            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty())
                return;

            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json))
                return;
            JSONArray bugsData = json.getJSONObject("data").getJSONArray("array");
            if (bugsData.length() == 0)
                return;
            for (int i = 0; i < bugsData.length(); ++i) {
                ContentValues currentValue = new ContentValues();
                JSONObject bug = bugsData.getJSONObject(i);
                JSONArray bugTag = bug.getJSONArray("tags");
                JSONArray bugUser = bug.getJSONArray("users");

                ContentValues[] tagAssignationValue = new ContentValues[bugTag.length()];
                ContentValues[] userAssignationValue = new ContentValues[bugUser.length()];

                String grappboxCID = bug.getJSONObject("creator").getString("id");
                Cursor creatorId = getContentResolver().query(UserEntry.buildUserWithGrappboxIdUri(grappboxCID), new String[] {UserEntry._ID}, null, null, null);

                if (creatorId == null || !creatorId.moveToFirst())
                {
                    handleUserDetailSync(grappboxCID);
                    creatorId = getContentResolver().query(UserEntry.buildUserWithGrappboxIdUri(grappboxCID), new String[] {UserEntry._ID}, null, null, null);
                    if (creatorId == null || !creatorId.moveToFirst())
                        continue;
                }

                currentValue.put(BugEntry.COLUMN_GRAPPBOX_ID, bug.getString("id"));
                currentValue.put(BugEntry.COLUMN_LOCAL_CREATOR_ID, creatorId.getLong(0));
                creatorId.close();
                currentValue.put(BugEntry.COLUMN_LOCAL_PROJECT_ID, localPID);
                currentValue.put(BugEntry.COLUMN_TITLE, bug.getString("title"));
                currentValue.put(BugEntry.COLUMN_DESCRIPTION, bug.getString("description"));
                currentValue.putNull(BugEntry.COLUMN_LOCAL_PARENT_ID);
                Date last_edited = Utils.Date.getDateFromGrappboxAPIToUTC(bug.getJSONObject(bug.isNull("editedAt") ? "createdAt" : "editedAt").getString("date"));
                currentValue.put(BugEntry.COLUMN_DATE_LAST_EDITED_UTC, last_edited.getTime());
                Uri bugUri = getContentResolver().insert(BugEntry.CONTENT_URI, currentValue);
                if (bugUri == null)
                    continue;
                long bugId = Long.parseLong(bugUri.getLastPathSegment());

                //Insert tags
                for (int j = 0; j < bugTag.length(); ++j) {
                    JSONObject currentTag = bugTag.getJSONObject(j);
                    ContentValues tagValue = new ContentValues();
                    ContentValues tagAssignValue = new ContentValues();

                    tagValue.put(TagEntry.COLUMN_GRAPPBOX_ID, currentTag.getString("id"));
                    tagValue.put(TagEntry.COLUMN_LOCAL_PROJECT_ID, localPID);
                    tagValue.put(TagEntry.COLUMN_NAME, currentTag.getString("name"));
                    Uri tagURI = getContentResolver().insert(TagEntry.CONTENT_URI, tagValue);
                    if (tagURI == null)
                        continue;
                    tagAssignValue.put(BugTagEntry.COLUMN_LOCAL_BUG_ID, bugId);
                    tagAssignValue.put(BugTagEntry.COLUMN_LOCAL_TAG_ID, Long.parseLong(tagURI.getLastPathSegment()));
                    tagAssignationValue[j] = tagAssignValue;
                }
                getContentResolver().bulkInsert(BugTagEntry.CONTENT_URI, tagAssignationValue);

                //insert users
                for (int j = 0; j < bugTag.length(); ++j) {
                    JSONObject currentUser = bugUser.getJSONObject(j);
                    Cursor userCursor = getContentResolver().query(UserEntry.buildUserWithGrappboxIdUri(currentUser.getString("id")), new String[]{UserEntry._ID}, null, null, null);
                    ContentValues currentUserAssignation = new ContentValues();

                    if (userCursor == null || !userCursor.moveToFirst())
                    {
                        handleUserDetailSync(currentUser.getString("id"));
                        userCursor = getContentResolver().query(UserEntry.buildUserWithGrappboxIdUri(currentUser.getString("id")), new String[]{UserEntry._ID}, null, null, null);
                        if (userCursor == null || !userCursor.moveToFirst())
                            continue;
                    }
                    currentUserAssignation.put(BugAssignationEntry.COLUMN_LOCAL_BUG_ID, bugId);
                    currentUserAssignation.put(BugAssignationEntry.COLUMN_LOCAL_USER_ID, userCursor.getLong(0));
                    userAssignationValue[j] = currentUserAssignation;
                    userCursor.close();
                }
                getContentResolver().bulkInsert(BugAssignationEntry.CONTENT_URI, userAssignationValue);
            }
        } catch (IOException e) {
            Log.e(LOG_TAG, "IOException : ", e);
        } catch (JSONException | ParseException e) {
            e.printStackTrace();
        } finally {
            projectRole.close();
            grappboxProjectId.close();
            if (connection != null)
                connection.disconnect();
        }
    }

    private void handleTimelineMessagesSync(long localTimelineId, int offset, int limit) {
        Cursor timelineGrappbox = getContentResolver().query(TimelineEntry.buildTimelineWithLocalIdUri(localTimelineId), new String[]{TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_GRAPPBOX_ID}, null, null, null);
        String apiToken = Utils.Account.getAuthTokenService(this, null);

        if (timelineGrappbox == null || !timelineGrappbox.moveToFirst())
            return;
        HttpURLConnection connection = null;
        String returnedJson;
        String apiTimelineID = timelineGrappbox.getString(0);
        try{
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/timeline/getlastmessages/"+apiToken+"/"+apiTimelineID + "/" + String.valueOf(offset) + "/" + String.valueOf(limit));

            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty())
                return;

            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json))
                return;
            JSONArray msgs = json.getJSONObject("data").getJSONArray("array");
            if (msgs.length() == 0)
                return;
            ContentValues[] messagesValues = new ContentValues[msgs.length()];
            for (int i = 0; i < msgs.length(); ++i) {
                JSONObject current = msgs.getJSONObject(i);
                ContentValues message = new ContentValues();

                String creatorId = current.getJSONObject("creator").getString("id");
                Cursor creator = getContentResolver().query(UserEntry.buildUserWithGrappboxIdUri(creatorId), new String[]{UserEntry._ID}, null, null, null);
                if (creator == null || !creator.moveToFirst()){
                    handleUserDetailSync(creatorId);
                    creator = getContentResolver().query(UserEntry.buildUserWithGrappboxIdUri(creatorId), new String[]{UserEntry._ID}, null, null, null);
                    if (creator == null || !creator.moveToFirst())
                        return;
                }
                message.put(TimelineMessageEntry.COLUMN_GRAPPBOX_ID, current.getString("id"));
                message.put(TimelineMessageEntry.COLUMN_LOCAL_CREATOR_ID, creator.getLong(0));
                message.put(TimelineMessageEntry.COLUMN_LOCAL_TIMELINE_ID, localTimelineId);
                message.put(TimelineMessageEntry.COLUMN_TITLE, current.getString("title"));
                message.put(TimelineMessageEntry.COLUMN_MESSAGE, current.getString("message"));
                if (current.has("parentId"))
                    message.put(TimelineMessageEntry.COLUMN_PARENT_ID, current.getString("parentId"));
                else
                    message.putNull(TimelineMessageEntry.COLUMN_PARENT_ID);
                Date lastEditedMsg = Utils.Date.getDateFromGrappboxAPIToUTC(current.isNull("editedAt") ? current.getJSONObject("createdAt").getString("date") : current.getJSONObject("editedAt").getString("date"));

                message.put(TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC, lastEditedMsg.getTime());
                message.put(TimelineMessageEntry.COLUMN_COUNT_ANSWER, Integer.valueOf(current.getString("nbComment")));
                messagesValues[i] = message;
                creator.close();
            }
            getContentResolver().bulkInsert(TimelineMessageEntry.CONTENT_URI, messagesValues);
        } catch (IOException | JSONException | ParseException e) {
            e.printStackTrace();
        } finally {
            timelineGrappbox.close();
            if (connection != null)
                connection.disconnect();
        }

    }

    private void handleUserDetailSync(long localUID) {
        Cursor userGrappboxID = getContentResolver().query(UserEntry.buildUserWithLocalIdUri(localUID), new String[]{UserEntry.COLUMN_GRAPPBOX_ID}, null, null, null);
        String apiToken = Utils.Account.getAuthTokenService(this, null);

        if (userGrappboxID == null || !userGrappboxID.moveToFirst() || apiToken == null)
            return;
        try {
            handleUserDetailSync(userGrappboxID.getString(0));
        } finally {
            userGrappboxID.close();
        }
    }

    private void handleUserDetailSync(String apiUID) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);

        if (apiToken.isEmpty() || apiUID.isEmpty())
            return;
        HttpURLConnection connection = null;
        String returnedJson;

        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/user/getuserbasicinformations/"+apiToken+"/"+apiUID);

            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty())
                return;

            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json))
                return;
            JSONObject data = json.getJSONObject("data");
            ContentValues userValue = new ContentValues();
            userValue.put(UserEntry.COLUMN_GRAPPBOX_ID, apiUID);
            userValue.put(UserEntry.COLUMN_FIRSTNAME, data.getString("firstname"));
            userValue.put(UserEntry.COLUMN_LASTNAME, data.getString("lastname"));
            userValue.put(UserEntry.COLUMN_DATE_BIRTHDAY_UTC, data.getString("birthday"));
            userValue.put(UserEntry.COLUMN_CONTACT_EMAIL, data.getString("email"));
            userValue.put(UserEntry.COLUMN_CONTACT_PHONE, data.getString("phone"));
            userValue.put(UserEntry.COLUMN_COUNTRY, data.getString("country"));
            userValue.put(UserEntry.COLUMN_SOCIAL_LINKEDIN, data.getString("linkedin"));
            userValue.put(UserEntry.COLUMN_SOCIAL_TWITTER, data.getString("twitter"));
            getContentResolver().insert(UserEntry.CONTENT_URI, userValue);
        } catch (IOException e) {
            Log.e(LOG_TAG, "IOException : ", e);
        } catch (JSONException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }

    private void handleEventTypeSync() throws IOException, JSONException {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        if (apiToken.isEmpty())
            return;
        HttpURLConnection connection = null;
        String returnedJson;

        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/event/gettypes/" + apiToken);

            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty())
                return;

            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json))
                return;
            JSONArray typesData = json.getJSONObject("data").getJSONArray("array");
            if (typesData.length() == 0)
                return;
            ContentValues[] typesValues = new ContentValues[typesData.length()];
            for (int i = 0; i < typesData.length(); ++i) {
                JSONObject current = typesData.getJSONObject(i);
                ContentValues value = new ContentValues();

                value.put(EventTypeEntry.COLUMN_GRAPPBOX_ID, current.getString("id"));
                value.put(EventTypeEntry.COLUMN_NAME, current.getString("name"));
                typesValues[i] = value;
            }
            getContentResolver().bulkInsert(EventTypeEntry.CONTENT_URI, typesValues);
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }

    private void handleNextMeetingsSync(long localPID) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        if (apiToken.isEmpty() || localPID == -1)
            return;
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor grappboxProjectId = getContentResolver().query(ProjectEntry.buildProjectWithLocalIdUri(localPID), new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, null, null, null);
        if (grappboxProjectId == null || !grappboxProjectId.moveToFirst())
            return;
        try {
            handleEventTypeSync();
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/dashboard/getnextmeetings/" + apiToken + "/" + grappboxProjectId.getString(0));

            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty())
                return;

            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json))
                return;
            JSONArray nextMeetingsData = json.getJSONObject("data").getJSONArray("array");
            if (nextMeetingsData.length() == 0)
                return;
            ContentValues[] nextMeetingsValues = new ContentValues[nextMeetingsData.length()];
            for (int i = 0; i < nextMeetingsData.length(); ++i) {
                ContentValues nextMeeting = new ContentValues();
                JSONObject current = nextMeetingsData.getJSONObject(i);

                nextMeeting.put(EventEntry.COLUMN_GRAPPBOX_ID, current.getString("id"));
                Cursor typeCursor = getContentResolver().query(EventTypeEntry.CONTENT_URI, new String[]{EventTypeEntry._ID}, EventTypeEntry.COLUMN_NAME + "=?", new String[] {current.getString("type")}, null);
                if (typeCursor == null || !typeCursor.moveToFirst())
                    continue;
                nextMeeting.put(EventEntry.COLUMN_LOCAL_EVENT_TYPE_ID, typeCursor.getLong(0));
                nextMeeting.put(EventEntry.COLUMN_EVENT_DESCRIPTION, current.getString("description"));
                nextMeeting.put(EventEntry.COLUMN_EVENT_TITLE, current.getString("title"));
                nextMeeting.put(EventEntry.COLUMN_DATE_BEGIN_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(current.getJSONObject("begin_date").getString("date")).getTime());
                nextMeeting.put(EventEntry.COLUMN_DATE_END_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(current.getJSONObject("end_date").getString("date")).getTime());
                typeCursor.close();
                nextMeetingsValues[i] = nextMeeting;
            }
            getContentResolver().bulkInsert(EventEntry.CONTENT_URI, nextMeetingsValues);
        } catch (IOException e) {
            Log.e(LOG_TAG, "IOException : ", e);
        } catch (JSONException | ParseException e) {
            e.printStackTrace();
        } finally {
            grappboxProjectId.close();
            if (connection != null)
                connection.disconnect();
        }
    }

    private Pair<Integer, Integer> syncProjectInfos(String apiID) throws IOException, JSONException {
        //synchronize project's list
        String apiToken = Utils.Account.getAuthTokenService(this, null);
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

    private void syncAccountProject(long projectId, String accountName) {
        String selection = GrappboxContract.ProjectAccountEntry.TABLE_NAME + "." + GrappboxContract.ProjectAccountEntry.COLUMN_PROJECT_LOCAL_ID + "=? AND " + GrappboxContract.ProjectAccountEntry.TABLE_NAME + "." + GrappboxContract.ProjectAccountEntry.COLUMN_ACCOUNT_NAME + "=?";
        String[] selectionArgs = new String[]{
                String.valueOf(projectId),
                accountName
        };
        Cursor query_project = getContentResolver().query(GrappboxContract.ProjectAccountEntry.CONTENT_URI, null, selection, selectionArgs, null);

        if (query_project == null || query_project.getCount() == 0)
        {
            ContentValues value = new ContentValues();
            value.put(GrappboxContract.ProjectAccountEntry.COLUMN_PROJECT_LOCAL_ID, projectId);
            value.put(GrappboxContract.ProjectAccountEntry.COLUMN_ACCOUNT_NAME, accountName);
            getContentResolver().insert(GrappboxContract.ProjectAccountEntry.CONTENT_URI, value);
        } else
            query_project.close();
    }

    private void handleProjectListSync(String accountName, ResultReceiver responseObserver){
        //synchronize project's list
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson = null;

        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/user/getprojects/" + apiToken);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);

            if (returnedJson == null || returnedJson.isEmpty()){
                responseObserver.send(Activity.RESULT_CANCELED, null);
                return;
            }
            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json)){
                Bundle jsonBundle = new Bundle();
                jsonBundle.putString(BUNDLE_KEY_JSON, json.toString());
                if (responseObserver != null)
                    responseObserver.send(Activity.RESULT_CANCELED, jsonBundle);
                return;
            }

            //update project's list
            JSONArray projects = json.getJSONObject("data").getJSONArray("array");
            if (projects.length() <= 0){
                if (responseObserver != null)
                    responseObserver.send(Activity.RESULT_OK, null);
                return;
            }
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

                Uri insertedUri = getContentResolver().insert(UserEntry.CONTENT_URI, userValue);
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
                Pair<Integer, Integer> infosCount = syncProjectInfos(project.getString("id"));
                if (infosCount != null) {
                    projectValue.put(ProjectEntry.COLUMN_COUNT_BUG, infosCount.first);
                    projectValue.put(ProjectEntry.COLUMN_COUNT_TASK, infosCount.second);
                }
                long projectId = Long.parseLong(getContentResolver().insert(ProjectEntry.CONTENT_URI, projectValue).getLastPathSegment());
                if (projectId == -1)
                    return;
                syncAccountProject(projectId, accountName);
            }

            if (responseObserver != null)
                responseObserver.send(Activity.RESULT_OK, null);
        } catch (IOException e) {
            Log.e(LOG_TAG, "IOException : ", e);
        } catch (JSONException | ParseException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }
}
