package com.grappbox.grappbox.sync;

import android.accounts.Account;
import android.accounts.AccountManager;
import android.accounts.NetworkErrorException;
import android.annotation.SuppressLint;
import android.app.Activity;
import android.app.DownloadManager;
import android.app.IntentService;
import android.app.Notification;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.ContentValues;
import android.content.Context;
import android.content.Intent;
import android.content.OperationApplicationException;
import android.content.SharedPreferences;
import android.database.Cursor;
import android.database.SQLException;
import android.graphics.BitmapFactory;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.os.Environment;
import android.provider.OpenableColumns;
import android.provider.Settings;
import android.support.annotation.Nullable;
import android.support.v4.app.TaskStackBuilder;
import android.support.v4.os.ResultReceiver;
import android.util.Base64;
import android.util.Log;
import android.util.Pair;

import com.grappbox.grappbox.BuildConfig;
import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.data.GrappboxContract.BugAssignationEntry;
import com.grappbox.grappbox.data.GrappboxContract.BugEntry;
import com.grappbox.grappbox.data.GrappboxContract.BugTagEntry;
import com.grappbox.grappbox.data.GrappboxContract.BugtrackerTagEntry;
import com.grappbox.grappbox.data.GrappboxContract.CloudEntry;
import com.grappbox.grappbox.data.GrappboxContract.EventEntry;
import com.grappbox.grappbox.data.GrappboxContract.ProjectEntry;
import com.grappbox.grappbox.data.GrappboxContract.TimelineEntry;
import com.grappbox.grappbox.data.GrappboxContract.TimelineMessageEntry;
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;
import com.grappbox.grappbox.model.BugModel;
import com.grappbox.grappbox.project_fragments.CloudFragment;
import com.grappbox.grappbox.receiver.BugReceiver;
import com.grappbox.grappbox.singleton.Session;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.text.ParseException;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.Set;

/**
 * Created by Marc Wieser on 21/10/2016.
 * If you have any problem or question about this work
 * please contact the author at marc.wieser33@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

public class GrappboxJustInTimeService extends IntentService {
    private static final String LOG_TAG = GrappboxJustInTimeService.class.getSimpleName();

    public static final String ACTION_SYNC_USER_DETAIL = "com.grappbox.grappbox.sync.ACTION_SYNC_USER_DETAIL";
    public static final String ACTION_SYNC_BUGS = "com.grappbox.grappbox.sync.ACTION_SYNC_BUGS";
    public static final String ACTION_SYNC_TIMELINE_MESSAGES = "com.grappbox.grappbox.sync.ACTION_SYNC_TIMELINE_MESSAGES";
    public static final String ACTION_SYNC_NEXT_MEETINGS = "com.grappbox.grappbox.sync.ACTION_SYNC_NEXT_MEETINGS";
    public static final String ACTION_SYNC_PROJECT_LIST = "com.grappbox.grappbox.sync.ACTION_SYNC_PROJECT_LIST";
    public static final String ACTION_SYNC_CLOUD_PATH = "com.grappbox.grappbox.sync.ACTION_SYNC_CLOUD_PATH";
    public static final String ACTION_CLOUD_ADD_DIRECTORY = "com.grappbox.grappbox.sync.ACTION_CLOUD_ADD_DIRECTORY";
    public static final String ACTION_CLOUD_IMPORT_FILE = "com.grappbox.grappbox.sync.ACTION_CLOUD_IMPORT_FILE";
    public static final String ACTION_CLOUD_DELETE = "com.grappbox.grappbox.sync.ACTION_CLOUD_DELETE";
    public static final String ACTION_CLOUD_DOWNLOAD = "com.grappbox.grappbox.sync.ACTION_CLOUD_DOWNLOAD";
    public static final String ACTION_SYNC_BUG_COMMENT = "com.grappbox.grappbox.sync.ACTION_SYNC_BUG_COMMENT";
    public static final String ACTION_POST_COMMENT = "com.grappbox.grappbox.sync.ACTION_POST_COMMENT";
    public static final String ACTION_EDIT_COMMENT = "com.grappbox.grappbox.sync.ACTION_EDIT_COMMENT";
    public static final String ACTION_DELETE_COMMENT = "com.grappbox.grappbox.sync.ACTION_DELETE_COMMENT";
    public static final String ACTION_CLOSE_BUG = "com.grappbox.grappbox.sync.ACTION_CLOSE_BUG";
    public static final String ACTION_REOPEN_BUG = "com.grappbox.grappbox.sync.ACTION_REOPEN_BUG";
    public static final String ACTION_CREATE_BUG = "com.grappbox.grappboxy.sync.ACTION_CREATE_BUG";
    public static final String ACTION_EDIT_BUG = "com.grappbox.grappbox.sync.ACTION_EDIT_BUG";
    public static final String ACTION_SYNC_TAGS = "com.grappbox.grappbox.sync.ACTION_SYNC_TAGS";
    public static final String ACTION_CREATE_TAG = "com.grappbox.grappbox.sync.ACTION_CREATE_TAG";
    public static final String ACTION_EDIT_BUGTAG = "com.grappbox.grappbox.sync.ACTION_EDIT_BUGTAG";
    public static final String ACTION_REMOVE_BUGTAG = "com.grappbox.grappbox.sync.ACTION_REMOVE_BUGTAG";
    public static final String ACTION_SET_PARTICIPANT = "com.grappbox.grappbox.sync.ACTION_SET_PARTICIPANT";
    public static final String ACTION_LOGIN = "com.grappbox.grappbox.sync.ACTION_LOGIN";
    public static final String ACTION_UPDATE_USER_SETTINGS = "com.grappbox.grappbox.sync.ACTION_UPDATE_USER_SETTINGS";
    public static final String ACTION_UPDATE_PROJECT_SETTINGS = "com.grappbox.grappbox.sync.ACTION_UPDATE_PROJECT_SETTINGS";
    public static final String ACTION_REMOVE_PROJECT = "com.grappbox.grappbox.sync.ACTION_REMOVE_PROJECT";
    public static final String ACTION_RETRIEVE_PROJECT = "com.grappbox.grappbox.sync.ACTION_RETRIEVE_PROJECT";
    public static final String ACTION_ADD_USER_TO_PROJECT = "com.grappbox.grappbox.sync.ACTION_ADD_USER_TO_PROJECT";
    public static final String ACTION_ASSIGN_USER_ROLE = "com.grappbox.grappbox.sync.ACTION_ASSIGN_USER_ROLE";
    public static final String ACTION_DELETE_USER_FROM_PROJECT = "com.grappbox.grappbox.sync.ACTION_DELETE_USER_FROM_PROJECT";
    public static final String ACTION_UNASSIGN_USER_ROLE = "com.grappbox.grappbox.sync.ACTION_UNASSIGN_USER_ROLE";
    public static final String ACTION_SYNC_CUSTOMER_ACCESS = "com.grappbox.grappbox.sync.ACTION_SYNC_CUSTOMER_ACCESS";
    public static final String ACTION_DELETE_CUTOMER_ACCESS = "com.grappbox.grappbox.sync.ACTION_DELETE_CUSTOMER_ACCESS";
    public static final String ACTION_ADD_CUSTOMER_ACCESS = "com.grappbox.grappbox.sync.ACTION_ADD_CUSTOMER_ACCESS";
    public static final String ACTION_CREATE_ROLE = "com.grappbox.grappbox.sync.ACTION_CREATE_ROLE";
    public static final String ACTION_UPDATE_ROLE = "com.grappbox.grappbox.sync.ACTION_UPDATE_ROLE";
    public static final String ACTION_DELETE_ROLE = "com.grappbox.grappbox.sync.ACTION_DELETE_ROLE";
    public static final String ACTION_REGISTER_DEVICE = "com.grappbox.grappbox.sync.ACTION_REGISTER_DEVICE";

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
    public static final String EXTRA_BUG_ID = "bug_id";
    public static final String EXTRA_MESSAGE = "msg";
    public static final String EXTRA_COMMENT_ID = "comment_id";
    public static final String EXTRA_TITLE = "title";
    public static final String EXTRA_DESCRIPTION = "description";
    public static final String EXTRA_CLIENT_ACTION = "client_action";
    public static final String EXTRA_TAG_ID = "tag_id";
    public static final String EXTRA_BUNDLE = "android:bundle";
    public static final String EXTRA_ADD_PARTICIPANT = "toAdd";
    public static final String EXTRA_DEL_PARTICIPANT = "toDel";
    public static final String EXTRA_COLOR = "color";
    public static final String EXTRA_ROLE_ID = "role_id";
    public static final String EXTRA_CUSTOMER_ACCESS_ID = "customer_access_ID";
    public static final String EXTRA_NAME = "name";
    public static final String EXTRA_ACCOUNT = "account";

    public static final String CATEGORY_GRAPPBOX_ID = "com.grappbox.grappbox.sync.CATEGORY_GRAPPBOX_ID";
    public static final String CATEGORY_LOCAL_ID = "com.grappbox.grappbox.sync.CATEGORY_LOCAL_ID";
    public static final String CATEGORY_CLOSED = "com.grappbox.grappbox.sync.CATEGORY_CLOSED";
    public static final String CATEGORY_NEW = "com.grappbox.grappbox.sync.NEW";


    public static final String BUNDLE_KEY_JSON = "com.grappbox.grappbox.sync.BUNDLE_KEY_JSON";
    public static final String BUNDLE_KEY_ERROR_MSG = "com.grappbox.grappbox.sync.BUNDLE_KEY_ERROR_MSG";
    public static final String BUNDLE_KEY_ERROR_TYPE = "com.grappbox.grappbox.sync.BUNDLE_KEY_ERROR_TYPE";

    public static final int NOTIF_CLOUD_FILE_UPLOAD = 2000;

    public static final int CLOUD_DATA_BYTE_READ = 5242880; //Thanks to a quick and simple calculation, this is the good number to upload in a chunk


    public GrappboxJustInTimeService() {
        super("GrappboxJustInTimeService");
    }

    @SuppressWarnings("unchecked")
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
            else if (ACTION_SYNC_BUGS.equals(action)){
                boolean isClosedSyncing = intent.getCategories() != null && intent.getCategories().contains(CATEGORY_CLOSED);
                handleBugsSync(intent.getLongExtra(EXTRA_USER_ID, -1), intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getIntExtra(EXTRA_OFFSET, 0), intent.getIntExtra(EXTRA_LIMIT, 50), isClosedSyncing, responseObserver);
            }
            else if (ACTION_SYNC_TIMELINE_MESSAGES.equals(action)) {
                handleTimelineMessagesSync(intent.getLongExtra(EXTRA_TIMELINE_ID, -1), intent.getIntExtra(EXTRA_OFFSET, 0), intent.getIntExtra(EXTRA_LIMIT, 50));
            }
            else if (ACTION_SYNC_NEXT_MEETINGS.equals(action)) {
                handleNextMeetingsSync(intent.getLongExtra(EXTRA_PROJECT_ID, -1));
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

                handleCloudImportFile(intent.getLongExtra(EXTRA_PROJECT_ID, -1), passwordSafe, intent.getStringExtra(EXTRA_CLOUD_PATH), intent.getData(), password);
            }
            else if (ACTION_CLOUD_DELETE.equals(action)){
                String password = intent.hasExtra(EXTRA_CLOUD_FILE_PASSWORD) ? intent.getStringExtra(EXTRA_CLOUD_FILE_PASSWORD) : null;
                String passwordSafe = intent.hasExtra(EXTRA_CLOUD_PASSWORD) ? intent.getStringExtra(EXTRA_CLOUD_PASSWORD) : null;
                handleCloudDelete(intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getStringExtra(EXTRA_CLOUD_PATH), intent.getStringExtra(EXTRA_FILENAME), passwordSafe, password, responseObserver);
            }
            else if (ACTION_CLOUD_DOWNLOAD.equals(action)){
                String password = intent.hasExtra(EXTRA_CLOUD_FILE_PASSWORD) ? intent.getStringExtra(EXTRA_CLOUD_FILE_PASSWORD) : null;
                String passwordSafe = intent.hasExtra(EXTRA_CLOUD_PASSWORD) ? intent.getStringExtra(EXTRA_CLOUD_PASSWORD) : null;
                handleCloudDownload(intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getStringExtra(EXTRA_CLOUD_PATH), intent.getStringExtra(EXTRA_FILENAME), passwordSafe, password, responseObserver);
            }
            else if (ACTION_SYNC_BUG_COMMENT.equals(action)){
                handleBugsCommentsSync(intent.getLongExtra(EXTRA_USER_ID, -1), intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getLongExtra(EXTRA_BUG_ID, -1), responseObserver);
            }
            else if (ACTION_POST_COMMENT.equals(action)){
                handleBugPostComment(intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getLongExtra(EXTRA_BUG_ID, -1), intent.getStringExtra(EXTRA_MESSAGE), responseObserver);
            }
            else if (ACTION_EDIT_COMMENT.equals(action)){
                handleBugEditComment(intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getLongExtra(EXTRA_BUG_ID, -1), intent.getLongExtra(EXTRA_COMMENT_ID, -1),intent.getStringExtra(EXTRA_MESSAGE), responseObserver);
            }
            else if (ACTION_DELETE_COMMENT.equals(action)){
                handleBugCloseComment(intent.getLongExtra(EXTRA_COMMENT_ID, -1), responseObserver);
            }
            else if (ACTION_CLOSE_BUG.equals(action)){
                handleBugClose(intent.getLongExtra(EXTRA_BUG_ID, -1), responseObserver);
            }
            else if (ACTION_REOPEN_BUG.equals(action)){
                handleBugReopenComment(intent.getLongExtra(EXTRA_BUG_ID, -1), responseObserver);
            }
            else if (ACTION_CREATE_BUG.equals(action)){
                handleBugCreate(false, intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getStringExtra(EXTRA_TITLE), intent.getStringExtra(EXTRA_DESCRIPTION), intent.getBooleanExtra(EXTRA_CLIENT_ACTION, false), responseObserver);
            }
            else if (ACTION_EDIT_BUG.equals(action)){
                handleBugCreate(true, intent.getLongExtra(EXTRA_BUG_ID, -1), intent.getStringExtra(EXTRA_TITLE), intent.getStringExtra(EXTRA_DESCRIPTION), intent.getBooleanExtra(EXTRA_CLIENT_ACTION, false), responseObserver);
            } else if (ACTION_SYNC_TAGS.equals(action)){
                handleTagSync(intent.getLongExtra(EXTRA_PROJECT_ID, -1));
            } else if (ACTION_CREATE_TAG.equals(action)){
                handleCreateTag(intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getLongExtra(EXTRA_BUG_ID, -1), intent.getStringExtra(EXTRA_TITLE), intent.getStringExtra(EXTRA_COLOR), responseObserver);
            } else if (ACTION_EDIT_BUGTAG.equals(action)){
                handleEditBugTag(intent.getLongExtra(EXTRA_BUG_ID, -1), intent.getLongExtra(EXTRA_TAG_ID, -1), responseObserver);
            } else if (ACTION_REMOVE_BUGTAG.equals(action)){
                handleRemoveBugTag(intent.getLongExtra(EXTRA_BUG_ID, -1), intent.getLongExtra(EXTRA_TAG_ID, -1), responseObserver);
            } else if (ACTION_SET_PARTICIPANT.equals(action)){
                Bundle arg = intent.getBundleExtra(EXTRA_BUNDLE);
                handleBugSetParticipant(intent.getLongExtra(EXTRA_BUG_ID, -1), (List<Long>) arg.getSerializable(EXTRA_ADD_PARTICIPANT), (List<Long>) arg.getSerializable(EXTRA_DEL_PARTICIPANT), responseObserver);
            } else if (ACTION_LOGIN.equals(action)) {
                handleLogin(intent.getStringExtra(EXTRA_MAIL), Utils.Security.decryptString(intent.getStringExtra(EXTRA_CRYPTED_PASSWORD)), responseObserver);
            } else if (ACTION_UPDATE_USER_SETTINGS.equals(action)){
                handleUpdateUserSettings(intent.getBundleExtra(EXTRA_BUNDLE), responseObserver);
            } else if (ACTION_UPDATE_PROJECT_SETTINGS.equals(action)){
                handleUpdateProjectSettings(intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getBundleExtra(EXTRA_BUNDLE), responseObserver);
            } else if (ACTION_REMOVE_PROJECT.equals(action)){
                handleRemoveProject(intent.getLongExtra(EXTRA_PROJECT_ID, -1), responseObserver);
            } else if (ACTION_RETRIEVE_PROJECT.equals(action)){
                handleRetrieveProject(intent.getLongExtra(EXTRA_PROJECT_ID, -1), responseObserver);
            } else if (ACTION_ADD_USER_TO_PROJECT.equals(action)){
                handleAddUserToProject(intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getStringExtra(EXTRA_MAIL), responseObserver);
            } else if (ACTION_ASSIGN_USER_ROLE.equals(action)){
                handleSetUserRole(intent.getLongExtra(EXTRA_ROLE_ID, -1), intent.getLongExtra(EXTRA_USER_ID, -1), responseObserver);
            } else if (ACTION_DELETE_USER_FROM_PROJECT.equals(action)){
                handleRemoveUserFromProject(intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getLongExtra(EXTRA_USER_ID, -1), responseObserver);
            } else if (ACTION_UNASSIGN_USER_ROLE.equals(action)){
                handleUnassignRole(intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getLongExtra(EXTRA_USER_ID, -1), intent.getLongExtra(EXTRA_ROLE_ID, -1), responseObserver);
            } else if (ACTION_SYNC_CUSTOMER_ACCESS.equals(action)){
                handleSyncCustomerAccess(intent.getLongExtra(EXTRA_PROJECT_ID, -1), responseObserver);
            } else if (ACTION_DELETE_CUTOMER_ACCESS.equals(action)){
                handleDeleteCustomerAccess(intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getLongExtra(EXTRA_CUSTOMER_ACCESS_ID, -1), responseObserver);
            } else if (ACTION_ADD_CUSTOMER_ACCESS.equals(action)){
                handleAddCustomerAccess(intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getStringExtra(EXTRA_NAME), responseObserver);
            } else if (ACTION_CREATE_ROLE.equals(action) || ACTION_UPDATE_ROLE.equals(action)){
                handleRole(intent.getBundleExtra(EXTRA_BUNDLE), intent.hasCategory(CATEGORY_NEW), responseObserver);
            } else if (ACTION_DELETE_ROLE.equals(action)){
                handleDeleteRole(intent.getLongExtra(EXTRA_ROLE_ID, -1), responseObserver);
            } else if (ACTION_REGISTER_DEVICE.equals(action)){
                handleRegisterDevice(intent.hasExtra(EXTRA_ACCOUNT) ? (Account) intent.getParcelableExtra(EXTRA_ACCOUNT) : null);
            }
        }
    }

    private void handleRegisterDevice(Account account){
        String[] tokens;
        String firebaseToken = getSharedPreferences(FirebaseCloudMessagingService.SHARED_FIREBASE_PREF, MODE_PRIVATE).getString(FirebaseCloudMessagingService.FIREBASE_PREF_TOKEN, null);
        if (account == null){
            tokens = Utils.Account.getAllAccountAuthTokenService(this);
        }
        else {
            tokens = new String[1];
            tokens[0] = Utils.Account.getAuthTokenService(this, account);
        }

        if (firebaseToken == null || tokens == null)
            return;
        for (String token : tokens) {
            if (token == null)
                continue;
            HttpURLConnection connection = null;
            String returnedJson;

            try {
                final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/notification/device");
                JSONObject json = new JSONObject();
                JSONObject data = new JSONObject();

                data.put("device_type", "Android");
                data.put("device_token", firebaseToken);
                data.put("device_name", "My Android Device");
                json.put("data", data);
                connection = (HttpURLConnection) url.openConnection();
                connection.setRequestProperty("Authorization", token);
                connection.setRequestMethod("POST");
                Utils.JSON.sendJsonOverConnection(connection, json);
                connection.connect();
                returnedJson = Utils.JSON.readDataFromConnection(connection);
                if (returnedJson == null || returnedJson.isEmpty()){
                    throw new NetworkErrorException("Returned JSON is empty");
                } else {
                    json = new JSONObject(returnedJson);
                    if (Utils.Errors.checkAPIError(json)){
                        throw new NetworkErrorException("API error");
                    }
                }
            } catch (IOException | JSONException | NetworkErrorException e) {
                e.printStackTrace();
            } finally {
                if (connection != null)
                    connection.disconnect();
            }
        }
    }

    private void handleDeleteRole(long roleId, ResultReceiver responseObserver) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor role = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);
            role = getContentResolver().query(GrappboxContract.RolesEntry.CONTENT_URI, new String[]{GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry._ID+"=?", new String[]{String.valueOf(roleId)}, null);
            if (role == null || !role.moveToFirst())
                throw new OperationApplicationException();
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/role/"+role.getString(0));
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("DELETE");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                if (responseObserver != null)
                {
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.9"));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            }
            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json)){
                if (responseObserver != null){
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
            }
            getContentResolver().delete(GrappboxContract.RolesEntry.CONTENT_URI, GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry._ID +"=?", new String[]{String.valueOf(roleId)});
        } catch (NetworkErrorException | JSONException | IOException | OperationApplicationException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (null != role) {
                role.close();
            }
        }
    }

    public void handleRole(Bundle args, boolean isNew, ResultReceiver responseObserver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);
            if (isNew)
                project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ ProjectEntry.COLUMN_GRAPPBOX_ID }, ProjectEntry._ID + "=?", new String[]{String.valueOf(args.getLong("_id"))}, null);
            else
                project = getContentResolver().query(GrappboxContract.RolesEntry.CONTENT_URI, new String[]{GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry.COLUMN_GRAPPBOX_ID }, GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry._ID + "=?", new String[]{String.valueOf(args.getLong("_id"))}, null);
            if (project == null || !project.moveToFirst())
                throw new OperationApplicationException();
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/role" + (!isNew ? "/" + project.getString(0) : ""));
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod(isNew ? "POST" : "PUT");
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            if (isNew){
                data.put("projectId", project.getString(0));
            }
            for (String key : args.keySet()) {
                if (key.startsWith("_"))
                    continue;
                if (key.equals("name"))
                    data.put(key, args.getString(key));
                else
                    data.put(key, args.getInt(key));
            }
            json.put("data", data);
            Log.d(LOG_TAG, apiToken);
            Log.d(LOG_TAG, url.toString());
            Log.d(LOG_TAG, json.toString());
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                if (responseObserver != null)
                {
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.9"));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            }
            json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json)){
                if (responseObserver != null){
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
            }
            JSONObject currentRole = json.getJSONObject("data");
            ContentValues roleValue = new ContentValues();
            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ ProjectEntry._ID }, ProjectEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{currentRole.getString("projectId")}, null);
            if (project == null || !project.moveToFirst())
                throw new OperationApplicationException(Utils.Errors.ERROR_INVALID_ID);
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_GRAPPBOX_ID, currentRole.getString("roleId"));
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_LOCAL_PROJECT_ID, project.getLong(0));
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_NAME, currentRole.getString("name"));
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_ACCESS_BUGTRACKER, currentRole.getString("bugtracker"));
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_ACCESS_CLOUD, currentRole.getString("cloud"));
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_ACCESS_CUSTOMER_TIMELINE, currentRole.getString("customerTimeline"));
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_ACCESS_TEAM_TIMELINE, currentRole.getString("teamTimeline"));
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_ACCESS_EVENT, currentRole.getString("event"));
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_ACCESS_GANTT, currentRole.getString("gantt"));
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_ACCESS_WHITEBOARD, currentRole.getString("whiteboard"));
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_ACCESS_TASK, currentRole.getString("task"));
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_ACCESS_PROJECT_SETTINGS, currentRole.getString("projectSettings"));
            getContentResolver().insert(GrappboxContract.RolesEntry.CONTENT_URI, roleValue);
        } catch (NetworkErrorException | JSONException | IOException | OperationApplicationException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null)
                project.close();
        }
    }

    public void handleAddCustomerAccess(long projectId, String name, ResultReceiver responseObserver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);
            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ ProjectEntry.COLUMN_GRAPPBOX_ID }, ProjectEntry._ID + "=?", new String[]{String.valueOf(projectId)}, null);
            if (project == null || !project.moveToFirst())
                throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/project/customeraccess");
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("POST");
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();

            data.put("projectId", project.getString(0));
            data.put("name", name);
            json.put("data", data);
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                if (responseObserver != null)
                {
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.9"));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            }
            json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json)){
                if (responseObserver != null){
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
            }
            data = json.getJSONObject("data");
            ContentValues customer = new ContentValues();
            customer.put(GrappboxContract.CustomerAccessEntry.COLUMN_GRAPPBOX_ID, data.getString("id"));
            customer.put(GrappboxContract.CustomerAccessEntry.COLUMN_TOKEN, data.getString("token"));
            customer.put(GrappboxContract.CustomerAccessEntry.COLUMN_PROJECT_ID, projectId);
            customer.put(GrappboxContract.CustomerAccessEntry.COLUMN_NAME, name);
            getContentResolver().insert(GrappboxContract.CustomerAccessEntry.CONTENT_URI, customer);
        } catch (NetworkErrorException | JSONException | IOException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null)
                project.close();
        }
    }

    public void handleDeleteCustomerAccess(long projectId, long customerAccessId, ResultReceiver responseObserver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null, customer = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);
            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ ProjectEntry.COLUMN_GRAPPBOX_ID }, ProjectEntry._ID + "=?", new String[]{String.valueOf(projectId)}, null);
            customer = getContentResolver().query(GrappboxContract.CustomerAccessEntry.CONTENT_URI, new String[]{GrappboxContract.CustomerAccessEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.CustomerAccessEntry._ID+"=?", new String[]{String.valueOf(customerAccessId)}, null);
            if (project == null || !project.moveToFirst() || customer == null || !customer.moveToFirst())
                throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/project/customeraccess/"+project.getString(0)+"/"+customer.getString(0));
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("DELETE");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                if (responseObserver != null)
                {
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.9"));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            }
            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json)){
                if (responseObserver != null){
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
            }
            getContentResolver().delete(GrappboxContract.CustomerAccessEntry.CONTENT_URI, GrappboxContract.CustomerAccessEntry._ID+"=?", new String[]{String.valueOf(customerAccessId)});
        } catch (NetworkErrorException | JSONException | IOException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null)
                project.close();
            if (customer != null)
                customer.close();
        }
    }

    public void handleSyncCustomerAccess(long projectId, ResultReceiver responseObserver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);
            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ ProjectEntry.COLUMN_GRAPPBOX_ID }, ProjectEntry._ID + "=?", new String[]{String.valueOf(projectId)}, null);
            if (project == null || !project.moveToFirst())
                throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/project/customeraccesses/"+project.getString(0));
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                if (responseObserver != null)
                {
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.9"));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            }
            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json)){
                if (responseObserver != null){
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
            }

            JSONArray data = json.getJSONObject("data").getJSONArray("array");
            for (int i = 0; i < data.length(); ++i){
                JSONObject current = data.getJSONObject(i);
                ContentValues values = new ContentValues();
                values.put(GrappboxContract.CustomerAccessEntry.COLUMN_GRAPPBOX_ID, current.getString("id"));
                values.put(GrappboxContract.CustomerAccessEntry.COLUMN_NAME, current.getString("name"));
                values.put(GrappboxContract.CustomerAccessEntry.COLUMN_PROJECT_ID, projectId);
                values.put(GrappboxContract.CustomerAccessEntry.COLUMN_TOKEN, current.getString("token"));
                getContentResolver().insert(GrappboxContract.CustomerAccessEntry.CONTENT_URI, values);
            }

        } catch (NetworkErrorException | JSONException | IOException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null)
                project.close();
        }
    }

    public void handleUnassignRole(long projectId, long uid, long roleId, ResultReceiver responseObserver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor role = null, user = null, project = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);
            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID+"=?", new String[]{String.valueOf(projectId)}, null);
            role = getContentResolver().query(GrappboxContract.RolesEntry.CONTENT_URI, new String[]{GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry._ID+"=?", new String[]{String.valueOf(roleId)}, null);
            user = getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[]{GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.UserEntry._ID+"=?", new String[]{String.valueOf(uid)}, null);
            if (role == null || !role.moveToFirst() || user == null || !user.moveToFirst() || project == null || !project.moveToFirst())
                throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/role/user/"+project.getString(0)+"/"+user.getString(0)+"/"+role.getString(0));
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("DELETE");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                if (responseObserver != null)
                {
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.9"));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            }
            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json)){
                if (responseObserver != null){
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
            }
            getContentResolver().delete(GrappboxContract.RolesAssignationEntry.CONTENT_URI, GrappboxContract.RolesAssignationEntry.COLUMN_LOCAL_ROLE_ID+"=? AND "+ GrappboxContract.RolesAssignationEntry.COLUMN_LOCAL_USER_ID+"=?", new String[]{String.valueOf(roleId), String.valueOf(uid)});
        } catch (NetworkErrorException | JSONException | IOException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (role != null)
                role.close();
            if (user != null)
                user.close();
            if (project != null)
                project.close();
        }
    }

    public void handleRemoveUserFromProject(long projectId, long userId, ResultReceiver responseObserver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null, user = null, role = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);
            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID+"=?", new String[]{String.valueOf(projectId)}, null);
            user = getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry.COLUMN_GRAPPBOX_ID}, UserEntry._ID+"=?", new String[]{String.valueOf(userId)}, null);
            role = getContentResolver().query(GrappboxContract.RolesAssignationEntry.buildRoleAssignationWithUIDAndPID(), new String[]{GrappboxContract.RolesAssignationEntry.TABLE_NAME + "." + GrappboxContract.RolesAssignationEntry.COLUMN_LOCAL_ROLE_ID}, GrappboxContract.RolesAssignationEntry.TABLE_NAME + "." + GrappboxContract.RolesAssignationEntry.COLUMN_LOCAL_USER_ID+"=? AND "+ ProjectEntry.TABLE_NAME + "." + ProjectEntry._ID+"=?", new String[]{String.valueOf(userId), String.valueOf(projectId)}, null);
            if (project == null || !project.moveToFirst() || user == null || !user.moveToFirst() || role == null || !role.moveToFirst())
                throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/project/user/"+project.getString(0)+"/"+user.getString(0));
            handleUnassignRole(projectId, userId, role.getLong(0), responseObserver);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("DELETE");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                if (responseObserver != null)
                {
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.9"));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            }
            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json)){
                if (responseObserver != null){
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
            }
            getContentResolver().notifyChange(GrappboxContract.UserEntry.buildUserWithProject(), null);
        } catch (NetworkErrorException | JSONException | IOException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null)
                project.close();
            if (user != null)
                user.close();
            if (role != null)
                role.close();
            if (role != null)
                role.close();
        }
    }

    public void handleAddUserToProject(long projectId, String email, ResultReceiver responseObserver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);
            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID+"=?", new String[]{String.valueOf(projectId)}, null);
            if (project == null || !project.moveToFirst())
                throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/project/user");
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("POST");
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            data.put("id", project.getString(0));
            data.put("email", email);
            json.put("data", data);
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                if (responseObserver != null)
                {
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.9"));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            }
            json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json)){
                if (responseObserver != null){
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
            }
            data = json.getJSONObject("data");
            ContentValues values = new ContentValues();
            values.put(UserEntry.COLUMN_GRAPPBOX_ID, data.getString("id"));
            values.put(UserEntry.COLUMN_FIRSTNAME, data.getString("firstname"));
            values.put(UserEntry.COLUMN_LASTNAME, data.getString("lastname"));
            Uri lastIdUri = getContentResolver().insert(UserEntry.CONTENT_URI, values);
            if (lastIdUri == null)
                throw new OperationApplicationException(Utils.Errors.ERROR_SQL_INSERT_FAILED);
            long id = Long.parseLong(lastIdUri.getLastPathSegment());
            Cursor adminRole = getContentResolver().query(GrappboxContract.RolesEntry.CONTENT_URI, new String[]{GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry._ID}, GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry.COLUMN_NAME+"=?", new String[]{"Admin"}, null);
            adminRole.moveToFirst();
            handleSetUserRole(adminRole.getLong(0
            ), id, responseObserver);
            handleUserDetailSync(data.getString("id"));
        } catch (NetworkErrorException | JSONException | IOException | OperationApplicationException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null)
                project.close();
        }
    }

    public void handleSetUserRole(long roleId, long uid, ResultReceiver responseObserver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor role = null, user = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);
            role = getContentResolver().query(GrappboxContract.RolesEntry.CONTENT_URI, new String[]{GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry._ID+"=?", new String[]{String.valueOf(roleId)}, null);
            user = getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[]{GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.UserEntry._ID+"=?", new String[]{String.valueOf(uid)}, null);
            if (role == null || !role.moveToFirst() || user == null || !user.moveToFirst())
                throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/role/user");
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("POST");
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            data.put("userId", user.getString(0));
            data.put("roleId", role.getString(0));
            json.put("data", data);
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                if (responseObserver != null)
                {
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.9"));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            }
            json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json)){
                if (responseObserver != null){
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
            }
            ContentValues values = new ContentValues();
            values.put(GrappboxContract.RolesAssignationEntry.COLUMN_LOCAL_ROLE_ID, roleId);
            values.put(GrappboxContract.RolesAssignationEntry.COLUMN_LOCAL_USER_ID, uid);
            getContentResolver().insert(GrappboxContract.RolesAssignationEntry.CONTENT_URI, values);
        } catch (NetworkErrorException | JSONException | IOException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (role != null)
                role.close();
            if (user != null)
                user.close();
        }
    }

    private void syncProjectUserRole(String apiToken, long pid, long uid){
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null;
        Cursor user = null;

        try {
            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID+"=?", new String[]{String.valueOf(pid)}, null);
            user = getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry.COLUMN_GRAPPBOX_ID}, UserEntry._ID+"=?", new String[]{String.valueOf(uid)}, null);
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
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_GRAPPBOX_ID, currentRole.getString("roleId"));
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_LOCAL_PROJECT_ID, pid);
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_NAME, currentRole.getString("name"));
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_ACCESS_BUGTRACKER, currentRole.getString("bugtracker"));
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_ACCESS_CLOUD, currentRole.getString("cloud"));
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_ACCESS_CUSTOMER_TIMELINE, currentRole.getString("customerTimeline"));
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_ACCESS_TEAM_TIMELINE, currentRole.getString("teamTimeline"));
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_ACCESS_EVENT, currentRole.getString("event"));
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_ACCESS_GANTT, currentRole.getString("gantt"));
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_ACCESS_WHITEBOARD, currentRole.getString("whiteboard"));
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_ACCESS_TASK, currentRole.getString("task"));
            roleValue.put(GrappboxContract.RolesEntry.COLUMN_ACCESS_PROJECT_SETTINGS, currentRole.getString("projectSettings"));
            Uri returnedUri = getContentResolver().insert(GrappboxContract.RolesEntry.CONTENT_URI, roleValue);
            if (returnedUri == null)
                throw new SQLException(Utils.Errors.ERROR_SQL_INSERT_FAILED);
            long id = Long.valueOf(returnedUri.getLastPathSegment());
            if (id <= 0)
                throw new SQLException(Utils.Errors.ERROR_SQL_INSERT_FAILED);
            roleAssignationValue.put(GrappboxContract.RolesAssignationEntry.COLUMN_LOCAL_ROLE_ID, id);
            roleAssignationValue.put(GrappboxContract.RolesAssignationEntry.COLUMN_LOCAL_USER_ID, uid);
            Cursor roleUser = getContentResolver().query(GrappboxContract.RolesAssignationEntry.CONTENT_URI, new String[]{GrappboxContract.RolesAssignationEntry.TABLE_NAME + "." + GrappboxContract.RolesAssignationEntry._ID}, GrappboxContract.RolesAssignationEntry.TABLE_NAME + "." + GrappboxContract.RolesAssignationEntry.COLUMN_LOCAL_ROLE_ID+"=? AND " + GrappboxContract.RolesAssignationEntry.TABLE_NAME + "." + GrappboxContract.RolesAssignationEntry.COLUMN_LOCAL_USER_ID+"=?", new String[]{String.valueOf(id), String.valueOf(uid)}, null);
            if (roleUser == null || !roleUser.moveToFirst() || roleUser.getCount() <= 0){
                getContentResolver().insert(GrappboxContract.RolesAssignationEntry.CONTENT_URI, roleAssignationValue);
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

    public void handleRetrieveProject(long projectId, ResultReceiver responseObserver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);
            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID+"=?", new String[]{String.valueOf(projectId)}, null);
            if (project == null || !project.moveToFirst())
                throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/project/retrieve/"+project.getString(0));
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                if (responseObserver != null)
                {
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.9"));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            }
            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json)){
                if (responseObserver != null){
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
            }
            ContentValues values = new ContentValues();
            values.putNull(ProjectEntry.COLUMN_DATE_DELETED_UTC);
            values.put(ProjectEntry.COLUMN_GRAPPBOX_ID, project.getString(0));
            getContentResolver().insert(ProjectEntry.CONTENT_URI, values);
        } catch (NetworkErrorException | JSONException | IOException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null)
                project.close();
        }
    }

    public void handleRemoveProject(long projectId, ResultReceiver responseObserver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);
            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID+"=?", new String[]{String.valueOf(projectId)}, null);
            if (project == null || !project.moveToFirst())
                throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/project/"+project.getString(0));
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("DELETE");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                if (responseObserver != null)
                {
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.9"));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            }
            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json)){
                if (responseObserver != null){
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
            }
            ContentValues values = new ContentValues();
            Calendar cal = Calendar.getInstance();
            cal.set(Calendar.DATE, cal.get(Calendar.DATE)+7);
            values.put(ProjectEntry.COLUMN_DATE_DELETED_UTC, Utils.Date.grappboxFormatter.format(cal.getTime()));
            values.put(ProjectEntry.COLUMN_GRAPPBOX_ID, project.getString(0));
            getContentResolver().insert(ProjectEntry.CONTENT_URI, values);
        } catch (NetworkErrorException | JSONException | IOException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null)
                project.close();
        }
    }

    public void handleUpdateProjectSettings(long projectId, Bundle keys, ResultReceiver responseObserver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);
            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID+"=?", new String[]{String.valueOf(projectId)}, null);
            if (project == null || !project.moveToFirst())
                throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/project/"+project.getString(0));
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            for (String key : keys.keySet()){
                data.put(key, keys.get(key));
            }
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("PUT");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            Log.d(LOG_TAG, returnedJson);
            if (returnedJson == null || returnedJson.isEmpty()){
                if (responseObserver != null)
                {
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.9"));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            }
            Log.d(LOG_TAG, returnedJson);
            json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json)){
                if (responseObserver != null){
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
            }
            data = json.getJSONObject("data");
            ContentValues values = new ContentValues();
            values.put(ProjectEntry.COLUMN_GRAPPBOX_ID, data.getString("id"));
            for (String key : keys.keySet()){
                if (!key.equals("password") && !key.equals("oldPassword"))
                    values.put(Utils.Database.sProjectApiDBMap.get(key), keys.getString(key));
            }
            getContentResolver().insert(ProjectEntry.CONTENT_URI, values);
        } catch (NetworkErrorException | JSONException | IOException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null)
                project.close();
        }
    }

    public void handleUpdateUserSettings(Bundle keys, ResultReceiver responseObserver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/user");
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            for (String key : keys.keySet()){
                data.put(key, keys.get(key));
            }
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("PUT");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                if (responseObserver != null)
                {
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.9"));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            }
            Log.d(LOG_TAG, returnedJson);
            json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json)){
                if (responseObserver != null){
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
            }
            data = json.getJSONObject("data");
            ContentValues values = new ContentValues();
            values.put(UserEntry.COLUMN_GRAPPBOX_ID, data.getString("id"));
            for (String key : keys.keySet()){
                if (!key.equals("password") && !key.equals("oldPassword"))
                    values.put(Utils.Database.sUserApiDBMap.get(key), keys.getString(key));
                else if (key.equals("password")){
                    AccountManager.get(this).setPassword(Session.getInstance(this).getCurrentAccount(), Utils.Security.cryptString(keys.getString(key)));
                }
            }
            getContentResolver().insert(UserEntry.CONTENT_URI, values);
        } catch (NetworkErrorException | JSONException | IOException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }

    @SuppressLint("HardwareIds")
    private void handleLogin(String mail, String password, @Nullable ResultReceiver responseObserver) {
        HttpURLConnection connection = null;
        String returnedJson;
        Bundle answer = new Bundle();


        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/account/login");
            //Ask login
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();

            data.put("login", mail);
            data.put("password", password);
            data.put("mac", Settings.Secure.getString(getContentResolver(), Settings.Secure.ANDROID_ID));
            data.put("flag", "and");
            data.put("device_name", Build.MODEL + " " + Build.SERIAL);
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("POST");
            connection.connect();
            Utils.JSON.sendJsonOverConnection(connection, json);
            Log.d(LOG_TAG, json.toString());
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                if (responseObserver != null)
                    responseObserver.send(Activity.RESULT_CANCELED, null);
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            }

            json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json)){
                if (responseObserver != null)
                    responseObserver.send(Activity.RESULT_CANCELED, null);
                throw new OperationApplicationException(Utils.Errors.ERROR_API_GENERIC);
            }
            data = json.getJSONObject("data");
            Calendar cal = Calendar.getInstance();
            cal.add(Calendar.DATE, 1);
            cal.add(Calendar.HOUR, -2);
            answer.putString(AccountManager.KEY_ACCOUNT_NAME, mail);
            answer.putString(AccountManager.KEY_ACCOUNT_TYPE, getString(R.string.sync_account_type));
            answer.putSerializable(EXTRA_API_TOKEN, data.getString("token"));
            answer.putString(AccountManager.KEY_AUTHTOKEN, data.getString("token"));
            answer.putString(EXTRA_CRYPTED_PASSWORD, Utils.Security.cryptString(password));
            if (responseObserver != null)
                responseObserver.send(Activity.RESULT_OK, answer);
        } catch (IOException | JSONException | NetworkErrorException | OperationApplicationException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }

    private void handleBugSetParticipant(long bugId, List<Long> toAdd, List<Long> toDel, ResultReceiver responseObserver) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor bug = null, userToAdd = null, userToDel = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);
            bug = getContentResolver().query(BugEntry.CONTENT_URI, new String[]{BugEntry.COLUMN_GRAPPBOX_ID}, BugEntry._ID+"=?", new String[]{String.valueOf(bugId)}, null);
            String userAddSelection = "";
            String userDelSelection = "";
            for (Long add : toAdd){
                userAddSelection += userAddSelection.isEmpty() ? "(" + add : "," + add;
            }
            if (!userAddSelection.isEmpty()){
                userAddSelection += ")";
                userToAdd = getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry.COLUMN_GRAPPBOX_ID}, UserEntry._ID+" IN " + userAddSelection, null, null);
            }
            for (Long del : toDel){
                userDelSelection += userDelSelection.isEmpty() ? "(" + del : "," + del;
            }
            if (!userDelSelection.isEmpty()){
                userDelSelection += ")";
                userToDel = getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry.COLUMN_GRAPPBOX_ID}, UserEntry._ID+" IN " + userDelSelection, null, null);
            }
            if (bug == null || !bug.moveToFirst())
                throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
            JSONArray toAddObj = new JSONArray();
            if (userToAdd != null && userToAdd.moveToFirst()){
                do{
                    toAddObj.put(userToAdd.getString(0));
                } while (userToAdd.moveToNext());
            }
            JSONArray toDelObj = new JSONArray();
            if (userToDel != null && userToDel.moveToFirst()){
                do{
                    toDelObj.put(userToDel.getString(0));
                } while (userToDel.moveToNext());
            }
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/users/"+bug.getString(0));
            JSONObject json = new JSONObject(), data = new JSONObject();

            data.put("toAdd", toAddObj);
            data.put("toRemove", toDelObj);
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("PUT");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null){
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                } else {
                    for (Long del : toDel){
                        getContentResolver().delete(BugAssignationEntry.CONTENT_URI, BugAssignationEntry.COLUMN_LOCAL_USER_ID+"=?", new String[]{String.valueOf(del)});
                    }
                    for (Long add : toAdd){
                        ContentValues value = new ContentValues();
                        value.put(BugAssignationEntry.COLUMN_LOCAL_BUG_ID, bugId);
                        value.put(BugAssignationEntry.COLUMN_LOCAL_USER_ID, add);
                        getContentResolver().insert(BugAssignationEntry.CONTENT_URI, value);
                    }
                }
            }
        } catch (IOException | JSONException | NetworkErrorException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (bug != null)
                bug.close();
            if (userToAdd != null)
                userToAdd.close();
            if (userToDel != null)
                userToDel.close();
        }
    }

    private void handleRemoveBugTag(long bugId, long tagId, ResultReceiver responseObserver) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor bug = null, tag = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);
            bug = getContentResolver().query(BugEntry.CONTENT_URI, new String[]{BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_GRAPPBOX_ID}, BugEntry.TABLE_NAME + "." + BugEntry._ID+"=?", new String[]{String.valueOf(bugId)}, null);
            tag = getContentResolver().query(BugtrackerTagEntry.CONTENT_URI, new String[]{BugtrackerTagEntry.TABLE_NAME + "." + BugtrackerTagEntry.COLUMN_GRAPPBOX_ID}, BugtrackerTagEntry.TABLE_NAME + "." + BugtrackerTagEntry._ID+"=?", new String[]{String.valueOf(tagId)}, null);
            if (bug == null || tag == null || !bug.moveToFirst() || !tag.moveToFirst())
                throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/tag/remove/"+bug.getString(0)+"/"+tag.getString(0));
            JSONObject json;

            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("DELETE");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null){
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                } else {
                    Cursor tagAssign = getContentResolver().query(BugTagEntry.CONTENT_URI, new String[]{BugTagEntry._ID}, BugTagEntry.COLUMN_LOCAL_BUG_ID+"=? AND " + BugTagEntry.COLUMN_LOCAL_TAG_ID+"=?", new String[]{String.valueOf(bugId), String.valueOf(tagId)}, null);
                    if (tagAssign != null && tagAssign.moveToFirst() && tagAssign.getCount() > 0){
                        getContentResolver().delete(BugTagEntry.CONTENT_URI, BugTagEntry.COLUMN_LOCAL_BUG_ID+"=? AND " + BugTagEntry.COLUMN_LOCAL_TAG_ID+"=?", new String[]{String.valueOf(bugId), String.valueOf(tagId)});
                        tagAssign.close();
                    }
                }
            }
        } catch (IOException | JSONException | NetworkErrorException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (bug != null)
                bug.close();
            if (tag != null)
                tag.close();
        }
    }

    private void handleEditBugTag(long bugId, long tagId, ResultReceiver responseObserver) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor bug = null, tag = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);
            bug = getContentResolver().query(BugEntry.CONTENT_URI, new String[]{BugEntry.COLUMN_GRAPPBOX_ID}, BugEntry._ID+"=?", new String[]{String.valueOf(bugId)}, null);
            tag = getContentResolver().query(BugtrackerTagEntry.CONTENT_URI, new String[]{BugtrackerTagEntry.TABLE_NAME + "." + BugtrackerTagEntry.COLUMN_GRAPPBOX_ID}, BugtrackerTagEntry.TABLE_NAME + "." + BugtrackerTagEntry._ID+"=?", new String[]{String.valueOf(tagId)}, null);
            if (tag == null || !tag.moveToFirst() || bug == null || !bug.moveToFirst())
                throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/tag/assign/" + bug.getString(0));
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();

            data.put("tagId", tag.getString(0));
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("PUT");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null){
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                } else {
                    Cursor tagAssign = getContentResolver().query(BugTagEntry.CONTENT_URI, new String[]{BugTagEntry._ID}, BugTagEntry.COLUMN_LOCAL_BUG_ID+"=? AND " + BugTagEntry.COLUMN_LOCAL_TAG_ID+"=?", new String[]{String.valueOf(bugId), String.valueOf(tagId)}, null);
                    if (tagAssign == null || !tagAssign.moveToFirst() || tagAssign.getCount() == 0){
                        ContentValues value = new ContentValues();
                        value.put(BugTagEntry.COLUMN_LOCAL_BUG_ID, bugId);
                        value.put(BugTagEntry.COLUMN_LOCAL_TAG_ID, tagId);
                        getContentResolver().insert(BugTagEntry.CONTENT_URI, value);
                    } else {
                        tagAssign.close();
                    }
                }
            }
        } catch (IOException | JSONException | NetworkErrorException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (bug != null)
                bug.close();
            if (tag != null)
                tag.close();
        }
    }

    private void handleCreateTag(long projectId, long bugId, String tagname, String color, ResultReceiver responseObserver) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;

        Cursor project = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);

            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID + "=?", new String[]{String.valueOf(projectId)}, null);
            if (project == null || !project.moveToFirst())
                throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/tag");
            Log.d(LOG_TAG, String.valueOf(url));
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();

            data.put("projectId", project.getString(0));
            data.put("name", tagname);
            data.put("color", (color.startsWith("#") ? color.substring(1) : color));
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("POST");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null){
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                    throw new OperationApplicationException(Utils.Errors.ERROR_API_GENERIC);
                } else {
                    ContentValues value = new ContentValues();
                    value.put(BugtrackerTagEntry.COLUMN_GRAPPBOX_ID, json.getJSONObject("data").getString("id"));
                    value.put(BugtrackerTagEntry.COLUMN_LOCAL_PROJECT_ID, projectId);
                    value.put(BugtrackerTagEntry.COLUMN_NAME, tagname);
                    value.put(BugtrackerTagEntry.COLUMN_COLOR, color);
                    Uri res = getContentResolver().insert(BugtrackerTagEntry.CONTENT_URI, value);
                    if (res == null)
                        throw new SQLException(Utils.Errors.ERROR_SQL_INSERT_FAILED);
                    long id = Long.parseLong(res.getLastPathSegment());
                    handleEditBugTag(bugId, id, responseObserver);
                }
            }
        } catch (IOException | JSONException | NetworkErrorException | OperationApplicationException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null)
                project.close();
        }
    }

    private void handleTagSync(long projectId) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;

        Cursor project = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);

            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID + "=?", new String[]{String.valueOf(projectId)}, null);
            if (project == null || !project.moveToFirst())
                throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/project/tags/"+project.getString(0));
            Log.d(LOG_TAG, String.valueOf(url));
            JSONObject json;
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
                } else {
                    JSONArray data = json.getJSONObject("data").getJSONArray("array");

                    for (int i = 0; i < data.length(); ++i){
                        JSONObject current = data.getJSONObject(i);
                        ContentValues value = new ContentValues();
                        value.put(BugtrackerTagEntry.COLUMN_GRAPPBOX_ID, current.getString("id"));
                        value.put(BugtrackerTagEntry.COLUMN_LOCAL_PROJECT_ID, projectId);
                        value.put(BugtrackerTagEntry.COLUMN_NAME, current.getString("name"));
                        value.put(BugtrackerTagEntry.COLUMN_COLOR, current.getString("color").startsWith("#") ? current.getString("color") : "#"+current.getString("color"));
                        getContentResolver().insert(BugtrackerTagEntry.CONTENT_URI, value);
                    }
                }
            }
        } catch (IOException | JSONException | NetworkErrorException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null)
                project.close();
        }
    }

    private void handleBugCreate(boolean isEditMode, long bugOrProjectID, String title, String description, boolean isClientOrigin, ResultReceiver responseObserver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor bug = null;
        Cursor project = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);

            bug = getContentResolver().query(BugEntry.CONTENT_URI, new String[]{BugEntry.COLUMN_GRAPPBOX_ID}, BugEntry._ID + "=?", new String[]{String.valueOf(bugOrProjectID)}, null);
            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID + "=?", new String[]{String.valueOf(bugOrProjectID)}, null);
            if ((isEditMode && (bug == null || !bug.moveToFirst())) || (!isEditMode && (project == null || !project.moveToFirst())))
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/ticket"+(isEditMode ? "/" + bug.getString(0) : ""));
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            //TODO : Optimize bugtracker to use this request with full features on edition and creation
            if (isEditMode){
                data.put("addTags", new JSONArray());
                data.put("removeTags", new JSONArray());
                data.put("addUsers", new JSONArray());
                data.put("removeUsers", new JSONArray());
            }else{
                data.put("projectId", project.getString(0));
                data.put("tags", new JSONArray());
                data.put("users", new JSONArray());
            }
            data.put("title", title);
            data.put("description", description);
            data.put("clientOrigin", isClientOrigin);
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod(isEditMode ? "PUT" : "POST");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException("Returned JSON is empty");
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null){
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                } else {
                    data = json.getJSONObject("data");
                    ContentValues values = new ContentValues();
                    values.put(BugEntry.COLUMN_TITLE, title);
                    values.putNull(BugEntry.COLUMN_DATE_DELETED_UTC);
                    values.put(BugEntry.COLUMN_DATE_LAST_EDITED_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(data.getString("createdAt")));
                    values.put(BugEntry.COLUMN_DESCRIPTION, data.getString("description"));
                    values.putNull(BugEntry.COLUMN_LOCAL_PARENT_ID);

                    String grappboxCID = data.getJSONObject("creator").getString("id");
                    Cursor creatorId = getContentResolver().query(UserEntry.CONTENT_URI, new String[] {UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);

                    if (creatorId == null || !creatorId.moveToFirst())
                    {
                        handleUserDetailSync(grappboxCID);
                        creatorId = getContentResolver().query(UserEntry.CONTENT_URI, new String[] {UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);
                        if (creatorId == null || !creatorId.moveToFirst()){
                            throw new UnknownError();
                        }
                    }
                    if (!isEditMode)
                        project.close();
                    project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry._ID}, ProjectEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{data.getString("projectId")}, null);
                    if (project == null || !project.moveToFirst())
                        throw new NetworkErrorException("Returned grappboxID is invalid, try to resynchronize your project");
                    values.put(BugEntry.COLUMN_GRAPPBOX_ID, data.getString("id"));
                    values.put(BugEntry.COLUMN_LOCAL_CREATOR_ID, creatorId.getLong(0));
                    values.put(BugEntry.COLUMN_LOCAL_PROJECT_ID, project.getLong(0));
                    Uri res = getContentResolver().insert(BugEntry.CONTENT_URI, values);
                    if (res == null)
                        throw new SQLException(Utils.Errors.ERROR_SQL_INSERT_FAILED);
                    long id = Long.parseLong(res.getLastPathSegment());
                    creatorId.close();
                    Cursor user = getContentResolver().query(BugEntry.CONTENT_URI, null, BugEntry._ID+"=?", new String[]{String.valueOf(id)}, null);
                    if (user != null){
                        if (user.moveToFirst()){
                            Bundle answer = new Bundle();
                            BugModel model = new BugModel(this, user);
                            model.setProjectID(user.getLong(user.getColumnIndex(BugEntry.COLUMN_LOCAL_PROJECT_ID)));
                            answer.putParcelable(BugReceiver.EXTRA_BUG_MODEL, model);
                            responseObserver.send(Activity.RESULT_OK, answer);
                        }
                        user.close();
                    }
                }
            }
        } catch (IOException | JSONException | NetworkErrorException | ParseException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (bug != null)
                bug.close();
            if (project != null)
                project.close();
        }
    }

    private void handleBugReopenComment(long bugId, ResultReceiver responseObserver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor bug = null;
        Cursor user = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);

            bug = getContentResolver().query(BugEntry.CONTENT_URI, new String[]{BugEntry.COLUMN_GRAPPBOX_ID, BugEntry.COLUMN_TITLE}, BugEntry._ID + "=?", new String[]{String.valueOf(bugId)}, null);
            user = getContentResolver().query(UserEntry.CONTENT_URI, new String[]{BugEntry._ID}, UserEntry.COLUMN_CONTACT_EMAIL+"=?", new String[]{Session.getInstance(this).getCurrentAccount().name}, null);
            if (bug == null || !bug.moveToFirst() || user == null || !user.moveToFirst())
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/ticket/reopen/"+bug.getString(0));

            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            } else {
                JSONObject json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null){
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                    throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
                } else {
                    ContentValues values = new ContentValues();
                    values.put(BugEntry.COLUMN_GRAPPBOX_ID, bug.getString(0));
                    values.put(BugEntry.COLUMN_LOCAL_CREATOR_ID, user.getLong(0));
                    values.putNull(BugEntry.COLUMN_DATE_DELETED_UTC);
                    values.put(BugEntry.COLUMN_DATE_LAST_EDITED_UTC, Utils.Date.nowUTC());
                    getContentResolver().insert(BugEntry.CONTENT_URI, values);
                }
            }
        } catch (IOException | JSONException | NetworkErrorException | ParseException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (bug != null)
                bug.close();
            if (user != null)
                user.close();
        }
    }

    private void handleBugClose(long bugID, ResultReceiver responseObserver){
        handleBugCloseComment(bugID, responseObserver, true);
    }

    private void handleBugCloseComment(long commentID, ResultReceiver responseObserver, boolean... keepDB){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        boolean isBug = keepDB != null && keepDB.length > 0 && keepDB[0];
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor bug = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);

            bug = getContentResolver().query(BugEntry.CONTENT_URI, new String[]{BugEntry.COLUMN_GRAPPBOX_ID}, BugEntry._ID + "=?", new String[]{String.valueOf(commentID)}, null);
            if (bug == null || !bug.moveToFirst())
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/" + (isBug ? "ticket/closed/" : "comment/")+bug.getString(0));
            JSONObject json = new JSONObject();

            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("DELETE");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null){
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                    throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
                } else {
                    if (!isBug)
                        getContentResolver().delete(BugEntry.CONTENT_URI, BugEntry._ID+"=?", new String[]{String.valueOf(commentID)});
                    else{
                        ContentValues values = new ContentValues();
                        values.put(BugEntry.COLUMN_GRAPPBOX_ID, bug.getString(0));
                        values.put(BugEntry.COLUMN_DATE_DELETED_UTC, Utils.Date.nowUTC());
                        getContentResolver().insert(BugEntry.CONTENT_URI, values);
                    }

                }
            }
        } catch (IOException | JSONException | NetworkErrorException | ParseException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (bug != null)
                bug.close();
        }
    }

    private void handleBugEditComment(long projectId, long bugID, long commentID, String message, ResultReceiver responseObserver) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null;
        Cursor bug = null;

        try {
            if (projectId == -1 || apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID + " || " + Utils.Errors.ERROR_INVALID_TOKEN);

            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID + "=?", new String[]{String.valueOf(projectId)}, null);
            bug = getContentResolver().query(BugEntry.CONTENT_URI, new String[]{BugEntry.COLUMN_GRAPPBOX_ID}, BugEntry._ID + "=?", new String[]{String.valueOf(commentID)}, null);
            if (project == null || !project.moveToFirst() || bug == null || !bug.moveToFirst())
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/comment/"+bug.getString(0));
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();

            data.put("comment", message);
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("PUT");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null){
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                    throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
                } else {
                    data = json.getJSONObject("data");
                    ContentValues values = new ContentValues();
                    values.put(BugEntry.COLUMN_TITLE, "");
                    values.putNull(BugEntry.COLUMN_DATE_DELETED_UTC);
                    values.put(BugEntry.COLUMN_DATE_LAST_EDITED_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(data.isNull("editedAt") ? data.getString("createdAt") : data.getString("editedAt")));
                    values.put(BugEntry.COLUMN_DESCRIPTION, data.getString("comment"));
                    values.put(BugEntry.COLUMN_LOCAL_PARENT_ID, bugID);

                    String grappboxCID = data.getJSONObject("creator").getString("id");
                    Cursor creatorId = getContentResolver().query(UserEntry.CONTENT_URI, new String[] {UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);

                    if (creatorId == null || !creatorId.moveToFirst())
                    {
                        handleUserDetailSync(grappboxCID);
                        creatorId = getContentResolver().query(UserEntry.CONTENT_URI, new String[] {UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);
                        if (creatorId == null || !creatorId.moveToFirst())
                            throw new UnknownError();
                    }
                    values.put(BugEntry.COLUMN_GRAPPBOX_ID, data.getString("id"));
                    values.put(BugEntry.COLUMN_LOCAL_CREATOR_ID, creatorId.getLong(0));
                    values.put(BugEntry.COLUMN_LOCAL_PROJECT_ID, projectId);
                    getContentResolver().insert(BugEntry.CONTENT_URI, values);
                    creatorId.close();
                }
            }
        } catch (IOException | JSONException | NetworkErrorException | ParseException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null)
                project.close();
            if (bug != null)
                bug.close();
        }
    }

    private void handleBugPostComment(long projectId, long bugID, String message, ResultReceiver responseObserver) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null;
        Cursor bug = null;

        try {
            if (projectId == -1 || apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID + " || " + Utils.Errors.ERROR_INVALID_TOKEN);

            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID + "=?", new String[]{String.valueOf(projectId)}, null);
            bug = getContentResolver().query(BugEntry.CONTENT_URI, new String[]{BugEntry.COLUMN_GRAPPBOX_ID}, BugEntry._ID + "=?", new String[]{String.valueOf(bugID)}, null);
            if (project == null || !project.moveToFirst() || bug == null || !bug.moveToFirst())
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/comment");
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();

            data.put("comment", message);
            data.put("parentId", bug.getString(0));
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("POST");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null){
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                    throw new OperationApplicationException(Utils.Errors.ERROR_API_GENERIC);
                } else {
                    data = json.getJSONObject("data");
                    ContentValues values = new ContentValues();
                    values.put(BugEntry.COLUMN_TITLE, "");
                    values.putNull(BugEntry.COLUMN_DATE_DELETED_UTC);
                    values.put(BugEntry.COLUMN_DATE_LAST_EDITED_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(data.isNull("editedAt") ? data.getString("createdAt") : data.getString("editedAt")));
                    values.put(BugEntry.COLUMN_DESCRIPTION, data.getString("comment"));
                    values.put(BugEntry.COLUMN_LOCAL_PARENT_ID, bugID);

                    String grappboxCID = data.getJSONObject("creator").getString("id");
                    Cursor creatorId = getContentResolver().query(UserEntry.CONTENT_URI, new String[] {UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);

                    if (creatorId == null || !creatorId.moveToFirst())
                    {
                        handleUserDetailSync(grappboxCID);
                        creatorId = getContentResolver().query(UserEntry.CONTENT_URI, new String[] {UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);
                        if (creatorId == null || !creatorId.moveToFirst()){
                            Log.e(LOG_TAG, Utils.Errors.ERROR_INVALID_ID);
                            throw new UnknownError();
                        }
                    }
                    values.put(BugEntry.COLUMN_GRAPPBOX_ID, data.getString("id"));
                    values.put(BugEntry.COLUMN_LOCAL_CREATOR_ID, creatorId.getLong(0));
                    values.put(BugEntry.COLUMN_LOCAL_PROJECT_ID, projectId);
                    getContentResolver().insert(BugEntry.CONTENT_URI, values);
                    creatorId.close();
                }
            }
        } catch (IOException | JSONException | NetworkErrorException | ParseException | OperationApplicationException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null)
                project.close();
            if (bug != null)
                bug.close();
        }
    }

    private void handleCloudDownload(long projectId, String cloudPath, String filename, String passwordSafe, String passwordFile, ResultReceiver responseObserver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        boolean isSecured = passwordFile != null;
        HttpURLConnection connection = null;
        if (projectId == -1 || apiToken == null)
            return;
        Cursor project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID+"=?", new String[]{String.valueOf(projectId)}, null);
        cloudPath = cloudPath.replace('/', ',');
        cloudPath = cloudPath.replace(' ', '|');
        filename = filename.replace(' ', '|');
        String path = (cloudPath.endsWith(",") ? cloudPath + filename : cloudPath + "," + filename);
        if (project == null || !project.moveToFirst())
            return;
        try {
            String urlBuilder = "/cloud/" + (isSecured ? "filesecured" : "file") + "/" + path + "/" + project.getString(0);
            urlBuilder += (isSecured ? "/" + passwordFile : "");
            urlBuilder += (cloudPath.contains(",Safe") ? "/" + passwordSafe : "");
            URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + urlBuilder);
            Log.d(LOG_TAG, url.toString());
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.setInstanceFollowRedirects(false);
            String resultString = Utils.JSON.readDataFromConnection(connection);
            JSONObject json = new JSONObject(resultString);
            if (resultString == null || (json.has("info") && Utils.Errors.checkAPIError(new JSONObject(resultString)))){
                if (responseObserver != null)
                {
                    Bundle error = new Bundle();
                    error.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.9"));
                    responseObserver.send(Activity.RESULT_CANCELED, error);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC + " :: " + resultString);
            }
            else{
                String cloudUrl = connection.getHeaderField("Location");
                DownloadManager.Request request = new DownloadManager.Request(Uri.parse(cloudUrl));
                request.allowScanningByMediaScanner();
                request.setTitle(filename);
                request.setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE);
                request.setDestinationInExternalPublicDir(Environment.DIRECTORY_DOWNLOADS, filename);
                DownloadManager manager = (DownloadManager) getSystemService(Context.DOWNLOAD_SERVICE);
                manager.enqueue(request);
            }
        } catch (IOException | NetworkErrorException | JSONException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            project.close();
        }
    }

    private void handleCloudDelete(long projectId, String cloudPath, String filename, String passwordSafe, String passwordFile, ResultReceiver responseObserver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        boolean isSecured = passwordFile != null;

        if (projectId == -1 || apiToken == null)
            return;
        Cursor project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID+"=?", new String[]{String.valueOf(projectId)}, null);
        if (project == null || !project.moveToFirst())
            return;
        cloudPath = cloudPath.replace('/', ',');
        cloudPath = cloudPath.replace(' ', '|');
        filename = filename.replace(' ', '|');
        String path = (cloudPath.endsWith(",") ? cloudPath + filename : cloudPath + "," + filename);
        HttpURLConnection connection = null;
        String returnedJson;

        try {
            String urlBuilder = "/cloud/"+(isSecured ? "filesecured" : "file")+"/"+project.getString(0)+"/"+path;
            urlBuilder += (isSecured ? "/" + passwordFile : "");
            urlBuilder += (passwordSafe == null ? "" : "/" + passwordSafe);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + urlBuilder);

            Log.d(LOG_TAG, "Start connection : " + url);
            JSONObject json;

            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("DELETE");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
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
                    throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
                } else {
                    handleCloudPathSync(cloudPath, projectId, passwordSafe, responseObserver);
                }
            }
        } catch (IOException | JSONException | NetworkErrorException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            project.close();
            if (responseObserver != null)
                responseObserver.send(Activity.RESULT_OK, null);
        }
    }

    private String handleCloudOpenStream(long projectId, String filename, String path, String passwordSafe, String password) throws NetworkErrorException {
        String apiToken = Utils.Account.getAuthTokenService(this, null);

        if (projectId == -1 || apiToken == null)
            throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID + " || " + Utils.Errors.ERROR_INVALID_TOKEN);

        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null;
        String streamId;
        try {
            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID + "=?", new String[]{String.valueOf(projectId)}, null);
            if (project == null || !project.moveToFirst())
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/cloud/stream/"+project.getString(0)+(passwordSafe == null ? "" : "/" + passwordSafe));
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();

            data.put("filename", filename);
            data.put("path", path);
            if (password != null)
                data.put("password", password);
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("POST");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
                }
                streamId = json.getJSONObject("data").getString("stream_id");
            }
        } catch (IOException | JSONException e) {
            e.printStackTrace();
            throw new NetworkErrorException("Code error see stacktrace");
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null)
                project.close();
        }
        return streamId;
    }

    private void handleCloudCloseStream(long projectId, String streamId) throws NetworkErrorException {
        String apiToken = Utils.Account.getAuthTokenService(this, null);

        if (projectId == -1 || apiToken == null)
            throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID);

        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null;
        try {
            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID + "=?", new String[]{String.valueOf(projectId)}, null);
            if (project == null || !project.moveToFirst())
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/cloud/stream/"+project.getString(0)+"/"+streamId);
            JSONObject json = new JSONObject();

            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("DELETE");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
                }
            }
        } catch (IOException | JSONException e) {
            e.printStackTrace();
            throw new NetworkErrorException("Code error see stacktrace");
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null)
                project.close();
        }
    }

    private void handleCloudChunkSending(long projectId, String streamId, int chunkNumbers, int currentChunk, byte[] chunk) throws NetworkErrorException {
        String apiToken = Utils.Account.getAuthTokenService(this, null);

        if (projectId == -1 || apiToken == null)
            throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);

        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null;
        try {
            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID + "=?", new String[]{String.valueOf(projectId)}, null);
            if (project == null || !project.moveToFirst())
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/cloud/file");
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            String chunkData = Base64.encodeToString(chunk, Base64.DEFAULT);

            data.put("stream_id", streamId);
            data.put("projectId", project.getString(0));
            data.put("chunk_numbers", chunkNumbers);
            data.put("current_chunk", currentChunk);
            data.put("file_chunk", chunkData);
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("PUT");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
                }
            }
        } catch (IOException | JSONException e) {
            e.printStackTrace();
            throw new NetworkErrorException("Code error see stacktrace");
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null)
                project.close();
        }
    }

    private void handleCloudImportFile(long projectId, String passwordSafe, String cloudPath, Uri filenameURI, String passwordFile){
        NotificationManager mNotifManager = (NotificationManager) getSystemService(Context.NOTIFICATION_SERVICE);

        InputStream file;
        Cursor fileData = getContentResolver().query(filenameURI, new String[]{OpenableColumns.DISPLAY_NAME}, null, null, null);
        Cursor project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_NAME}, ProjectEntry._ID + "=?", new String[]{String.valueOf(projectId)}, null);
        if (fileData == null || !fileData.moveToFirst() || project == null || !project.moveToFirst())
            return;
        try {
            file = getContentResolver().openInputStream(filenameURI);
            if (file != null){
                int bytesNumber = file.available();

                if (bytesNumber > 0){
                    byte[] reader = new byte[CLOUD_DATA_BYTE_READ];
                    int chunkNumbers = (bytesNumber / CLOUD_DATA_BYTE_READ) + 1;
                    int i = 0;
                    String streamId = handleCloudOpenStream(projectId, fileData.getString(fileData.getColumnIndex(OpenableColumns.DISPLAY_NAME)), cloudPath, passwordSafe, passwordFile);
                    Notification.Builder notifbuilder = new Notification.Builder(this)
                                        .setContentTitle(getString(R.string.notif_text_cloud_send_file, fileData.getString(fileData.getColumnIndex(OpenableColumns.DISPLAY_NAME))))
                                        .setProgress(100, 0, true)
                                        .setSmallIcon(R.drawable.ic_upload)
                                        .setLargeIcon(BitmapFactory.decodeResource(getResources(), R.drawable.ic_upload))
                                        .setContentText(getString(R.string.upload_progress));
                    mNotifManager.notify(NOTIF_CLOUD_FILE_UPLOAD, notifbuilder.build());
                    while (file.read(reader) > -1){
                        handleCloudChunkSending(projectId, streamId, chunkNumbers, i, reader);
                        ++i;
                        notifbuilder.setProgress(100, 100 * (i+1) / chunkNumbers, false);
                        mNotifManager.notify(NOTIF_CLOUD_FILE_UPLOAD, notifbuilder.build());
                    }
                    handleCloudCloseStream(projectId, streamId);
                    Intent seeFile = new Intent(this, ProjectActivity.class);
                    seeFile.setAction(ProjectActivity.ACTION_CLOUD_IMPORT);
                    seeFile.putExtra(ProjectActivity.EXTRA_PROJECT_ID, projectId);
                    seeFile.putExtra(ProjectActivity.EXTRA_CLOUD_PATH, cloudPath);
                    seeFile.putExtra(ProjectActivity.EXTRA_PROJECT_NAME, project.getString(0));

                    TaskStackBuilder stackBuilder = TaskStackBuilder.create(this);
                    stackBuilder.addParentStack(ProjectActivity.class);
                    stackBuilder.addNextIntent(seeFile);

                    notifbuilder.setProgress(100, 100, false)
                                .setContentText(getString(R.string.upload_ended))
                                .setContentIntent(stackBuilder.getPendingIntent(0, PendingIntent.FLAG_UPDATE_CURRENT));

                    mNotifManager.notify(NOTIF_CLOUD_FILE_UPLOAD, notifbuilder.build());
                }
                file.close();
                handleCloudPathSync(cloudPath, projectId, passwordSafe, null);
            }
        } catch (IOException | NetworkErrorException e) {
            e.printStackTrace();
        } finally {
            project.close();
            fileData.close();
        }
    }

    private void handleCloudAddDirectory(long projectId, String path, String dirName, String passwordSafe, ResultReceiver responseObserver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);

        if (projectId == -1 || apiToken == null)
            return;

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
                throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
            data.put("project_id", project.getString(0));
            data.put("path", path);
            data.put("dir_name", dirName);
            if (passwordSafe != null)
                data.put("passwordSafe", passwordSafe);
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
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
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
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
                    throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
                } else {
                    handleCloudPathSync(path, projectId, passwordSafe, null);
                }
            }
        } catch (IOException | JSONException | NetworkErrorException e) {
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
        HttpURLConnection connection = null;
        String returnedJson;

        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/cloud/list/"+project.getString(0)+"/"+cloudPath+(passwordSafe == null || passwordSafe.isEmpty() ? "" : "/"+passwordSafe));
            JSONObject json;
            JSONObject data;

            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
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
                    throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
                } else {
                    data = json.getJSONObject("data");
                    JSONArray array = data.getJSONArray("array");
                    ArrayList<Long> existingFiles = new ArrayList<>();
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
                                value.put(CloudEntry.COLUMN_MIMETYPE, current.getString("mimetype"));
                                value.put(CloudEntry.COLUMN_DATE_LAST_EDITED_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(current.getJSONObject("last_modified").getString("date")));
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
                            if (check != null && check.moveToFirst()){
                                existingFiles.add(check.getLong(check.getColumnIndex(CloudEntry._ID)));
                                check.close();
                            } else
                                directory.add(value);

                        }
                        if (existingFiles.size() == 0){
                            getContentResolver().delete(CloudEntry.CONTENT_URI, CloudEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND " + CloudEntry.COLUMN_PATH + "=?", new String[]{String.valueOf(projectId), cloudPath});
                        } else {
                            String deleteIds = "";
                            for (Long id : existingFiles){
                                deleteIds += deleteIds.isEmpty() ? id.toString() : "," + id.toString();
                            }
                            String deleteSelecion = CloudEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND "+ CloudEntry.COLUMN_PATH+" =? AND " + CloudEntry._ID + " NOT IN ("+deleteIds+")";
                            getContentResolver().delete(CloudEntry.CONTENT_URI, deleteSelecion, new String[]{String.valueOf(projectId), cloudPath});
                        }

                        getContentResolver().bulkInsert(CloudEntry.CONTENT_URI, directory.toArray(new ContentValues[directory.size()]));
                        if (responseObserver != null)
                            responseObserver.send(Activity.RESULT_OK, null);
                    }
                }
            }
        } catch (IOException | JSONException | ParseException | NetworkErrorException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            project.close();
            if (responseObserver != null)
                responseObserver.send(Activity.RESULT_OK, null);
        }
    }

    private void handleBugsCommentsSync(long localUID, long localPID, long bugId, @Nullable ResultReceiver responseObserver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        if (apiToken.isEmpty() || localUID == -1 || localPID == -1)
            return;
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor grappboxProjectId = getContentResolver().query(ProjectEntry.buildProjectWithLocalIdUri(localPID), new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, null, null, null);
        Cursor grappboxBugId = getContentResolver().query(BugEntry.CONTENT_URI, new String[]{BugEntry.COLUMN_GRAPPBOX_ID}, BugEntry._ID + "=?", new String[]{String.valueOf(bugId)}, null);
        if (grappboxProjectId == null || !grappboxProjectId.moveToFirst() || grappboxBugId == null || !grappboxBugId.moveToFirst())
            return;
        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/comments/"+grappboxBugId.getString(0));
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson != null && !returnedJson.isEmpty()){
                JSONObject json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null)
                    {
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                    throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
                }
                else {
                    JSONArray bugsData = json.getJSONObject("data").getJSONArray("array");
                    ArrayList<Long> existingComments = new ArrayList<>();
                    if (bugsData.length() != 0){
                        for (int i = 0; i < bugsData.length(); ++i) {
                            ContentValues currentValue = new ContentValues();
                            JSONObject bug = bugsData.getJSONObject(i);

                            String grappboxCID = bug.getJSONObject("creator").getString("id");
                            Cursor creatorId = getContentResolver().query(UserEntry.CONTENT_URI, new String[] {UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);

                            if (creatorId == null || !creatorId.moveToFirst())
                            {
                                handleUserDetailSync(grappboxCID);
                                creatorId = getContentResolver().query(UserEntry.CONTENT_URI, new String[] {UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);
                                if (creatorId == null || !creatorId.moveToFirst()){
                                    continue;
                                }
                            }
                            currentValue.put(BugEntry.COLUMN_GRAPPBOX_ID, bug.getString("id"));
                            currentValue.put(BugEntry.COLUMN_LOCAL_CREATOR_ID, creatorId.getLong(0));
                            creatorId.close();
                            currentValue.put(BugEntry.COLUMN_LOCAL_PROJECT_ID, localPID);
                            currentValue.put(BugEntry.COLUMN_TITLE, "");
                            currentValue.put(BugEntry.COLUMN_DESCRIPTION, bug.getString("comment"));
                            currentValue.put(BugEntry.COLUMN_LOCAL_PARENT_ID, bugId);
                            String last_edited = Utils.Date.getDateFromGrappboxAPIToUTC(bug.getString(bug.isNull("editedAt") ? "createdAt" : "editedAt"));
                            currentValue.put(BugEntry.COLUMN_DATE_LAST_EDITED_UTC, last_edited);
                            if (!bug.isNull("deletedAt")){
                                currentValue.put(BugEntry.COLUMN_DATE_DELETED_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(bug.getString("deletedAt")));
                            }
                            Uri commentUri = getContentResolver().insert(BugEntry.CONTENT_URI, currentValue);
                            if (commentUri == null)
                                continue;
                            long commentId = Long.parseLong(commentUri.getLastPathSegment());
                            existingComments.add(commentId);
                        }
                    }
                    String selection = BugEntry.COLUMN_LOCAL_PARENT_ID + "="+bugId+ (existingComments.size() > 0 ? " AND " + BugEntry._ID + " NOT IN (" : "");
                    if (existingComments.size() > 0){
                        boolean isFirst = true;
                        for (Long commentId : existingComments){
                            selection += isFirst ? commentId.toString() : ", " + commentId.toString();
                            isFirst = false;
                        }
                        selection += ")";
                    }
                    getContentResolver().delete(BugEntry.CONTENT_URI, selection, null);
                }
            }
        } catch (IOException | JSONException | ParseException | NetworkErrorException e) {
            e.printStackTrace();
        } finally {
            grappboxBugId.close();
            grappboxProjectId.close();
            if (connection != null)
                connection.disconnect();
            if (responseObserver != null){
                responseObserver.send(Activity.RESULT_OK, null);
            }
        }
    }

    private void handleBugsSync(long localUID, long localPID, int offset, int limit, boolean isSyncingClosedBugs, ResultReceiver responseObserver) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        if (apiToken.isEmpty() || localUID == -1 || localPID == -1)
            return;
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor grappboxProjectId = getContentResolver().query(ProjectEntry.buildProjectWithLocalIdUri(localPID), new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, null, null, null);

        if (grappboxProjectId == null || !grappboxProjectId.moveToFirst())
            return;
        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/tickets/"+ (isSyncingClosedBugs ? "closed/" : "opened/")+grappboxProjectId.getString(0)+"/"+String.valueOf(offset)+"/" + String.valueOf(limit));
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson != null && !returnedJson.isEmpty()){
                JSONObject json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (responseObserver != null)
                    {
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                    throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
                }
                else {
                    JSONArray bugsData = json.getJSONObject("data").getJSONArray("array");

                    if (bugsData.length() != 0){

                        for (int i = 0; i < bugsData.length(); ++i) {
                            ContentValues currentValue = new ContentValues();
                            JSONObject bug = bugsData.getJSONObject(i);
                            JSONArray bugTag = bug.getJSONArray("tags");
                            JSONArray bugUser = bug.getJSONArray("users");

                            String grappboxCID = bug.getJSONObject("creator").getString("id");
                            Cursor creatorId = getContentResolver().query(UserEntry.CONTENT_URI, new String[] {UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);

                            if (creatorId == null || !creatorId.moveToFirst())
                            {
                                handleUserDetailSync(grappboxCID);
                                creatorId = getContentResolver().query(UserEntry.CONTENT_URI, new String[] {UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);
                                if (creatorId == null || !creatorId.moveToFirst()){
                                    continue;
                                }
                            }
                            currentValue.put(BugEntry.COLUMN_GRAPPBOX_ID, bug.getString("id"));
                            currentValue.put(BugEntry.COLUMN_LOCAL_CREATOR_ID, creatorId.getLong(0));
                            creatorId.close();
                            currentValue.put(BugEntry.COLUMN_LOCAL_PROJECT_ID, localPID);
                            currentValue.put(BugEntry.COLUMN_TITLE, bug.getString("title"));
                            currentValue.put(BugEntry.COLUMN_DESCRIPTION, bug.getString("description"));
                            currentValue.putNull(BugEntry.COLUMN_LOCAL_PARENT_ID);
                            //TODO : manage client origin
                            String last_edited = Utils.Date.getDateFromGrappboxAPIToUTC(bug.getString(bug.isNull("editedAt") ? "createdAt" : "editedAt"));
                            currentValue.put(BugEntry.COLUMN_DATE_LAST_EDITED_UTC, last_edited);
                            if (!bug.isNull("deletedAt")){
                                currentValue.put(BugEntry.COLUMN_DATE_DELETED_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(bug.getString("deletedAt")));
                            }
                            Uri bugUri = getContentResolver().insert(BugEntry.CONTENT_URI, currentValue);
                            if (bugUri == null)
                                continue;
                            long bugId = Long.parseLong(bugUri.getLastPathSegment());
                            if (bugId < 0)
                                continue;
                            Intent syncComments = new Intent(this, GrappboxJustInTimeService.class);
                            syncComments.setAction(ACTION_SYNC_BUG_COMMENT);
                            syncComments.putExtra(EXTRA_PROJECT_ID, localPID);
                            syncComments.putExtra(EXTRA_USER_ID, localUID);
                            syncComments.putExtra(EXTRA_RESPONSE_RECEIVER, responseObserver);
                            syncComments.putExtra(EXTRA_BUG_ID, bugId);
                            startService(syncComments);

                            ArrayList<Long> existingTags = new ArrayList<>();
                            for (int j = 0; j < bugTag.length(); ++j) {
                                JSONObject currentTag = bugTag.getJSONObject(j);
                                ContentValues tagValue = new ContentValues();
                                ContentValues tagAssignValue = new ContentValues();

                                tagValue.put(BugtrackerTagEntry.COLUMN_GRAPPBOX_ID, currentTag.getString("id"));
                                tagValue.put(BugtrackerTagEntry.COLUMN_LOCAL_PROJECT_ID, localPID);
                                tagValue.put(BugtrackerTagEntry.COLUMN_NAME, currentTag.getString("name"));
                                tagValue.put(BugtrackerTagEntry.COLUMN_COLOR, currentTag.getString("color").startsWith("#") ? currentTag.getString("color") : "#" + currentTag.getString("color"));
                                Uri tagURI = getContentResolver().insert(BugtrackerTagEntry.CONTENT_URI, tagValue);
                                if (tagURI == null)
                                    continue;
                                tagAssignValue.put(BugTagEntry.COLUMN_LOCAL_BUG_ID, bugId);
                                tagAssignValue.put(BugTagEntry.COLUMN_LOCAL_TAG_ID, Long.parseLong(tagURI.getLastPathSegment()));
                                Uri bugtagUri = getContentResolver().insert(BugTagEntry.CONTENT_URI, tagAssignValue);
                                if (bugtagUri == null)
                                    continue;
                                long bugtagId = Long.parseLong(bugtagUri.getLastPathSegment());
                                existingTags.add(bugtagId);
                            }
                            String selection = BugTagEntry.COLUMN_LOCAL_BUG_ID + "=?"+ (existingTags.size() > 0 ? " AND " + BugTagEntry._ID + " NOT IN (" : "");
                            if (existingTags.size() > 0){
                                boolean isFirst = true;
                                for (Long idTag : existingTags){
                                    selection += isFirst ? idTag.toString() : "," + idTag.toString();
                                    isFirst = false;
                                }
                                selection += ")";
                            }
                            getContentResolver().delete(BugTagEntry.CONTENT_URI, selection, new String[]{String.valueOf(bugId)});
                            //insert users
                            ArrayList<Long> existingUsers = new ArrayList<>();
                            for (int j = 0; j < bugUser.length(); ++j) {
                                JSONObject currentUser = bugUser.getJSONObject(j);
                                Cursor userCursor = getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{currentUser.getString("id")}, null);
                                ContentValues currentUserAssignation = new ContentValues();

                                if (userCursor == null || !userCursor.moveToFirst())
                                {
                                    handleUserDetailSync(currentUser.getString("id"));
                                    userCursor = getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{currentUser.getString("id")}, null);
                                    if (userCursor == null || !userCursor.moveToFirst()){
                                        continue;
                                    }
                                }
                                currentUserAssignation.put(BugAssignationEntry.COLUMN_LOCAL_BUG_ID, bugId);
                                currentUserAssignation.put(BugAssignationEntry.COLUMN_LOCAL_USER_ID, userCursor.getLong(0));
                                Uri userEntryURI = getContentResolver().insert(BugAssignationEntry.CONTENT_URI, currentUserAssignation);
                                if (userEntryURI == null)
                                    continue;
                                long userEntryId = Long.parseLong(userEntryURI.getLastPathSegment());
                                existingUsers.add(userEntryId);
                                userCursor.close();
                            }
                            selection = BugTagEntry.COLUMN_LOCAL_BUG_ID + "=?" + (existingUsers.size() > 0 ? " AND " + BugAssignationEntry._ID + " NOT IN (" : "");
                            if (existingUsers.size() > 0){
                                boolean isFirst = true;
                                for (Long idUser : existingUsers){
                                    selection += isFirst ? idUser.toString() : "," + idUser.toString();
                                    isFirst = false;
                                }
                                selection += ")";
                            }
                            getContentResolver().delete(BugAssignationEntry.CONTENT_URI, selection, new String[]{String.valueOf(bugId)});
                        }
                    }
                }
            }
        } catch (IOException e) {
            Log.e(LOG_TAG, "IOException : ", e);
        } catch (JSONException | ParseException | NetworkErrorException e) {
            e.printStackTrace();
        } finally {
            grappboxProjectId.close();
            if (connection != null)
                connection.disconnect();
            if (responseObserver != null){
                responseObserver.send(Activity.RESULT_OK, null);
            }
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
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/timeline/messages/"+apiTimelineID + "/" + String.valueOf(offset) + "/" + String.valueOf(limit));

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
                String lastEditedMsg = Utils.Date.getDateFromGrappboxAPIToUTC(current.isNull("editedAt") ? current.getJSONObject("createdAt").getString("date") : current.getJSONObject("editedAt").getString("date"));

                message.put(TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC, lastEditedMsg);
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
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/user/"+apiUID);

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
            //TODO : manage client origin
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
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/dashboard/meetings/"+ grappboxProjectId.getString(0));

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
            JSONArray nextMeetingsData = json.getJSONObject("data").getJSONArray("array");
            if (nextMeetingsData.length() == 0)
                return;
            ContentValues[] nextMeetingsValues = new ContentValues[nextMeetingsData.length()];
            for (int i = 0; i < nextMeetingsData.length(); ++i) {
                ContentValues nextMeeting = new ContentValues();
                JSONObject current = nextMeetingsData.getJSONObject(i);

                nextMeeting.put(EventEntry.COLUMN_GRAPPBOX_ID, current.getString("id"));
                nextMeeting.put(EventEntry.COLUMN_EVENT_DESCRIPTION, current.getString("description"));
                nextMeeting.put(EventEntry.COLUMN_EVENT_TITLE, current.getString("title"));
                nextMeeting.put(EventEntry.COLUMN_DATE_BEGIN_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(current.getJSONObject("begin_date").getString("date")));
                nextMeeting.put(EventEntry.COLUMN_DATE_END_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(current.getJSONObject("end_date").getString("date")));
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
            Cursor userCreator = getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{creator.getString("id")}, null);
            if (userCreator == null || !userCreator.moveToFirst()){
                ContentValues newUser = new ContentValues();
                newUser.put(UserEntry.COLUMN_FIRSTNAME, creator.getString("firstname"));
                newUser.put(UserEntry.COLUMN_LASTNAME, creator.getString("lastname"));
                newUser.put(UserEntry.COLUMN_GRAPPBOX_ID, creator.getString("id"));
                getContentResolver().insert(UserEntry.CONTENT_URI, newUser);
                userCreator = getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{creator.getString("id")}, null);
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
        String returnedJson;

        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/dashboard/projects");
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty())
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json)){
                Bundle jsonBundle = new Bundle();
                jsonBundle.putString(BUNDLE_KEY_JSON, json.toString());
                if (responseObserver != null)
                    responseObserver.send(Activity.RESULT_CANCELED, jsonBundle);
                throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
            }
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
                Uri insertedProject = getContentResolver().insert(ProjectEntry.CONTENT_URI, value);
                if (insertedProject == null)
                    continue;
                syncAccountProject(Long.valueOf(insertedProject.getLastPathSegment()), accountName);
            }
            if (responseObserver != null)
                responseObserver.send(Activity.RESULT_OK, null);
        } catch (IOException | JSONException | NetworkErrorException | ParseException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }
}
