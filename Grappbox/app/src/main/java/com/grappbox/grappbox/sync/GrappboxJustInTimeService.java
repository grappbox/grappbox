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
import android.app.Service;
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
import com.grappbox.grappbox.data.GrappboxContract.CloudEntry;
import com.grappbox.grappbox.data.GrappboxContract.EventEntry;
import com.grappbox.grappbox.data.GrappboxContract.ProjectEntry;
import com.grappbox.grappbox.data.GrappboxContract.TimelineEntry;
import com.grappbox.grappbox.data.GrappboxContract.TimelineMessageEntry;
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;
import com.grappbox.grappbox.messaging.FirebaseCloudMessagingService;
import com.grappbox.grappbox.project_fragments.CloudFragment;
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
import java.util.Set;

/**
 * Created by Marc Wieser the 21/10/2016.
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
    public static final String ACTION_SYNC_TIMELINE_MESSAGES = "com.grappbox.grappbox.sync.ACTION_SYNC_TIMELINE_MESSAGES";
    public static final String ACTION_SYNC_NEXT_MEETINGS = "com.grappbox.grappbox.sync.ACTION_SYNC_NEXT_MEETINGS";
    public static final String ACTION_SYNC_PROJECT_LIST = "com.grappbox.grappbox.sync.ACTION_SYNC_PROJECT_LIST";
    public static final String ACTION_SYNC_CLOUD_PATH = "com.grappbox.grappbox.sync.ACTION_SYNC_CLOUD_PATH";
    public static final String ACTION_CLOUD_ADD_DIRECTORY = "com.grappbox.grappbox.sync.ACTION_CLOUD_ADD_DIRECTORY";
    public static final String ACTION_CLOUD_IMPORT_FILE = "com.grappbox.grappbox.sync.ACTION_CLOUD_IMPORT_FILE";
    public static final String ACTION_CLOUD_DELETE = "com.grappbox.grappbox.sync.ACTION_CLOUD_DELETE";
    public static final String ACTION_CLOUD_DOWNLOAD = "com.grappbox.grappbox.sync.ACTION_CLOUD_DOWNLOAD";
    public static final String ACTION_DELETE_COMMENT = "com.grappbox.grappbox.sync.ACTION_DELETE_COMMENT";
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
                    handleUserDetailSync(this, intent.getStringExtra(EXTRA_USER_ID));
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
              else if (ACTION_LOGIN.equals(action)) {
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
            if (adminRole == null || !adminRole.moveToFirst())
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID);
            handleSetUserRole(adminRole.getLong(0
            ), id, responseObserver);
            handleUserDetailSync(this, data.getString("id"));
            adminRole.close();
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
                    handleUserDetailSync(this, creatorId);
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
            handleUserDetailSync(this, userGrappboxID.getString(0));
        } finally {
            userGrappboxID.close();
        }
    }

    public static void handleUserDetailSync(Service context, String apiUID) {
        String apiToken = Utils.Account.getAuthTokenService(context, null);

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
            context.getContentResolver().insert(UserEntry.CONTENT_URI, userValue);
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
