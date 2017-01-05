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

import com.grappbox.grappbox.messaging.FirebaseCloudMessagingService;
import com.grappbox.grappbox.project_fragments.CloudFragment;

import com.grappbox.grappbox.model.BugModel;
import com.grappbox.grappbox.model.CalendarEventModel;
import com.grappbox.grappbox.project_fragments.CloudFragment;
import com.grappbox.grappbox.receiver.BugReceiver;
import com.grappbox.grappbox.receiver.CalendarEventReceiver;

import com.grappbox.grappbox.singleton.Session;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.io.InputStream;
import java.net.HttpURLConnection;

import java.net.MalformedURLException;
import java.net.URISyntaxException;

import java.net.URL;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;

import java.util.Date;
import java.util.List;
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
    public static final String ACTION_SYNC_TIMELINE_COMMENTS = "com.grappbox.grappbox.sync.ACTION_SYNC_TIMELINE_COMMENTS";
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

    public static final String ACTION_SYNC_BUG_COMMENT = "com.grappbox.grappbox.sync.ACTION_SYNC_BUG_COMMENT";
    public static final String ACTION_TIMELINE_ADD_MESSAGE = "com.grappbox.grappbox.sync.ACTION_TIMELINE_ADD_MESSAGE";
    public static final String ACTION_TIMELINE_ADD_COMMENT = "com.grappbox.grappbox.sync.ACTION_TIMELINE_ADD_COMMENT";
    public static final String ACTION_TIMELINE_EDIT_MESSAGE = "com.grappbox.grappbox.sync.ACTION_TIMELINE_EDIT_MESSAGE";
    public static final String ACTION_TIMELINE_EDIT_COMMENT = "com.grappbox.grappbox.sync.ACTION_TIMELINE_EDIT_COMMENT";
    public static final String ACTION_TIMELINE_DELETE_MESSAGE = "com.grappbox.grappbox.sync.ACTION_TIMELINE_DELETE_MESSAGE";
    public static final String ACTION_TIMELINE_DELETE_COMMENT = "com.grappbox.grappbox.sync.ACTION_TIMELINE_DELETE_COMMENT";

    public static final String ACTION_SYNC_EVENT = "com.grappbox.grappbox.sync.ACTION_SYNC_EVENT";
    public static final String ACTION_CREATE_EVENT = "com.grappbox.grappbox.sync.ACTION_POST_EVENT";
    public static final String ACTION_GET_EVENT = "com.grappbox.grappbox.sync.ACTION_GET_EVENT";
    public static final String ACTION_EDIT_EVENT = "com.grappbox.grappbox.sync.ACTION_EDIT_EVENT";
    public static final String ACTION_DELETE_EVENT = "com.grappbox.grappbox.sync.ACTION_DELETE_EVENT";
    public static final String ACTION_SET_PARTICIPANT_EVENT = "com.grappbox.grappbox.sync.ACTION_SET_PARTICIPANT_EVENT";
    public static final String ACTION_GET_MONTH_PLANNING = "com.grappbox.grappbox.sync.ACTION_GET_MONTH_PLANNING";
    public static final String ACTION_SYNC_ALL_STATS = "com.grappbox.grappbox.sync.ACTION_SYNC_ALL_STATS";

    public static final String EXTRA_API_TOKEN = "api_token";
    public static final String EXTRA_USER_ID = "uid";
    public static final String EXTRA_PROJECT_ID = "pid";
    public static final String EXTRA_OFFSET = "offset";
    public static final String EXTRA_LIMIT = "limit";
    public static final String EXTRA_TIMELINE_ID = "tid";
    public static final String EXTRA_TIMELINE_TITLE = "title";
    public static final String EXTRA_TIMELINE_MESSAGE = "message";
    public static final String EXTRA_TIMELINE_MESSAGE_ID = "messageId";
    public static final String EXTRA_TIMELINE_PARENT_ID = "commentedId";
    public static final String EXTRA_TIMELINE_IS_COMMENT = "isComment";
    public static final String EXTRA_RESPONSE_RECEIVER = "response_receiver";
    public static final String EXTRA_MAIL = "mail";
    public static final String EXTRA_CRYPTED_PASSWORD = "password";
    public static final String EXTRA_ACCOUNT_NAME = "account_name";
    public static final String EXTRA_CLOUD_PATH = "cloud_path";
    public static final String EXTRA_CLOUD_PASSWORD = "cloud_password";
    public static final String EXTRA_CLOUD_FILE_PASSWORD = "cloud_file_password";
    public static final String EXTRA_DIRECTORY_NAME = "dir_name";
    public static final String EXTRA_BUG_ID = "bug_id";
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
    public static final String EXTRA_EVENT_ID = "eventId";
    public static final String EXTRA_CALENDAR_FIRST_DAY = "firstDay";
    public static final String EXTRA_CALENDAR_EVENT_BEGIN = "begin";
    public static final String EXTRA_CALENDAR_EVENT_END = "end";
    public static final String EXTRA_CALENDAR_MONTH_OFFSET = "montOffset";


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
                    handleUserDetailSync(this, intent.getLongExtra(EXTRA_USER_ID, -1));
                else
                    handleUserDetailSync(this, intent.getStringExtra(EXTRA_USER_ID));
            }
            else if (ACTION_SYNC_TIMELINE_MESSAGES.equals(action)) {
                handleTimelineMessagesSync(intent.getLongExtra(EXTRA_TIMELINE_ID, -1), intent.getIntExtra(EXTRA_OFFSET, 0), intent.getIntExtra(EXTRA_LIMIT, 50));
            }
            else if (ACTION_SYNC_TIMELINE_COMMENTS.equals(action)) {
                handleTimelineMessagesCommentSync(intent.getLongExtra(EXTRA_TIMELINE_ID, -1), intent.getLongExtra(EXTRA_TIMELINE_PARENT_ID, -1));
            }
            else if (ACTION_TIMELINE_ADD_MESSAGE.equals(action)) {
                handleTimelineAddMessage(intent.getLongExtra(EXTRA_TIMELINE_ID, -1), intent.getStringExtra(EXTRA_TIMELINE_TITLE), intent.getStringExtra(EXTRA_TIMELINE_MESSAGE), responseObserver);
            }
            else if (ACTION_TIMELINE_ADD_COMMENT.equals(action)) {
                handleTimelineAddComment(intent.getLongExtra(EXTRA_TIMELINE_ID, -1), intent.getStringExtra(EXTRA_TIMELINE_TITLE), intent.getStringExtra(EXTRA_TIMELINE_MESSAGE), intent.getIntExtra(EXTRA_TIMELINE_PARENT_ID, -1), responseObserver);
            }
            else if (ACTION_TIMELINE_EDIT_MESSAGE.equals(action)) {
                handleTimelineMessageEdit(intent.getLongExtra(EXTRA_TIMELINE_ID, -1), intent.getLongExtra(EXTRA_TIMELINE_MESSAGE_ID, -1), intent.getStringExtra(EXTRA_TIMELINE_TITLE), intent.getStringExtra(EXTRA_TIMELINE_MESSAGE), responseObserver, intent.getLongExtra(EXTRA_TIMELINE_PARENT_ID, -1));
            }
            else if (ACTION_TIMELINE_EDIT_COMMENT.equals(action)) {
                handleTimelineCommentEdit(intent.getLongExtra(EXTRA_TIMELINE_ID, -1), intent.getLongExtra(EXTRA_TIMELINE_MESSAGE_ID, -1), intent.getStringExtra(EXTRA_TIMELINE_MESSAGE),  responseObserver, intent.getLongExtra(EXTRA_TIMELINE_PARENT_ID, -1));
            }
            else if (ACTION_TIMELINE_DELETE_MESSAGE.equals(action)) {
                handleTimelineMessageDelete(intent.getLongExtra(EXTRA_TIMELINE_ID, -1), intent.getLongExtra(EXTRA_TIMELINE_MESSAGE_ID, -1), responseObserver);
            }
            else if (ACTION_TIMELINE_DELETE_COMMENT.equals(action)) {
                handleTimelineCommentDelete(intent.getLongExtra(EXTRA_TIMELINE_ID, -1), intent.getLongExtra(EXTRA_TIMELINE_MESSAGE_ID, -1),  responseObserver);
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
            } else if (ACTION_GET_MONTH_PLANNING.equals(action)){
                handleCalendarMonthSync(intent.getStringExtra(EXTRA_CALENDAR_FIRST_DAY));
            } else if (ACTION_SYNC_EVENT.equals(action)) {
                handleSyncEvent(intent.getIntExtra(EXTRA_CALENDAR_MONTH_OFFSET, 0));
            } else if (ACTION_CREATE_EVENT.equals(action)) {
                Bundle arg = intent.getBundleExtra(EXTRA_BUNDLE);
                handleEventCreate(intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getStringExtra(EXTRA_TITLE), intent.getStringExtra(EXTRA_DESCRIPTION), intent.getStringExtra(EXTRA_CALENDAR_EVENT_BEGIN), intent.getStringExtra(EXTRA_CALENDAR_EVENT_END), (List<Long>) arg.getSerializable(EXTRA_ADD_PARTICIPANT), responseObserver);
            } else if (ACTION_EDIT_EVENT.equals(action)) {
                Bundle arg = intent.getBundleExtra(EXTRA_BUNDLE);
                handleEventEdit(intent.getLongExtra(EXTRA_EVENT_ID, -1) ,intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getStringExtra(EXTRA_TITLE), intent.getStringExtra(EXTRA_DESCRIPTION), intent.getStringExtra(EXTRA_CALENDAR_EVENT_BEGIN), intent.getStringExtra(EXTRA_CALENDAR_EVENT_END),(List<Long>) arg.getSerializable(EXTRA_ADD_PARTICIPANT), (List<Long>) arg.getSerializable(EXTRA_DEL_PARTICIPANT), responseObserver);
            } else if (ACTION_GET_EVENT.equals(action)) {
                handleEventGet(intent.getLongExtra(EXTRA_EVENT_ID, -1));
            } else if (ACTION_DELETE_EVENT.equals(action)) {
                handleEventDelete(intent.getLongExtra(EXTRA_EVENT_ID, -1), responseObserver);
            }  else if (ACTION_SET_PARTICIPANT_EVENT.equals(action)) {
                Bundle arg = intent.getBundleExtra(EXTRA_BUNDLE);
                handleEventSetParticipant(intent.getLongExtra(EXTRA_EVENT_ID, -1), (List<Long>) arg.getSerializable(EXTRA_ADD_PARTICIPANT), (List<Long>) arg.getSerializable(EXTRA_DEL_PARTICIPANT), responseObserver);
            } else if (ACTION_SYNC_ALL_STATS.equals(action)) {
                handleAllStatGet(intent.getStringExtra(EXTRA_API_TOKEN), intent.getLongExtra(EXTRA_PROJECT_ID, -1));
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
            role = getContentResolver().query(GrappboxContract.RolesEntry.CONTENT_URI, new String[]{GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry._ID + "=?", new String[]{String.valueOf(roleId)}, null);
            if (role == null || !role.moveToFirst())
                throw new OperationApplicationException();
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/role/" + role.getString(0));
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("DELETE");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()) {
                if (responseObserver != null) {
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.9"));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            }
            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json)) {
                if (responseObserver != null) {
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                    responseObserver.send(Activity.RESULT_CANCELED, answer);
                }
                throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
            }
            getContentResolver().delete(GrappboxContract.RolesEntry.CONTENT_URI, GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry._ID + "=?", new String[]{String.valueOf(roleId)});
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


    public void handleSyncCustomerAccess(long projectId, ResultReceiver responseObserver){}

    private void handleCreateTag(long projectId, long bugId, String tagname, String color, ResultReceiver responseObserver) {
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
        //boolean isBug = keepDB != null && keepDB.length > 0 && keepDB[0];
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

    private void handleLogin(String mail, String password, @Nullable ResultReceiver responseObserver) {
        HttpURLConnection connection = null;
        String returnedJson;
        AccountManager am = AccountManager.get(this);
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
                                handleUserDetailSync(this, grappboxCID);
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
                                handleUserDetailSync(this, grappboxCID);
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
                                    handleUserDetailSync(this, currentUser.getString("id"));
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
            if (responseObserver != null)
                responseObserver.send(Activity.RESULT_OK, null);
        }
    }

    private Cursor TimelineGetCreatorId(String creatorId)
    {
        Cursor creator = getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry.COLUMN_GRAPPBOX_ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{creatorId}, null);
        if (creator == null || !creator.moveToFirst()){
            handleUserDetailSync(this, creatorId);
            creator = getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry.COLUMN_GRAPPBOX_ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{creatorId}, null);
            if (creator == null || !creator.moveToFirst())
                return null;
        }
        return creator;
    }

    private void handleTimelineAddComment(long localTimelineId, String title, String message, int parentId, ResultReceiver resultReceiver) {
        Cursor timelineGrappbox = getContentResolver().query(TimelineEntry.buildTimelineWithLocalIdUri(localTimelineId), new String[]{TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_GRAPPBOX_ID}, null, null, null);
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        if (timelineGrappbox == null || !timelineGrappbox.moveToFirst())
            return;

        Log.v(LOG_TAG, "Add message");
        HttpURLConnection connection = null;
        String returnedJson;
        String apiTimelineID = timelineGrappbox.getString(0);
        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/timeline/comment/" + apiTimelineID);
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            data.put("comment", message);
            data.put("commentedId", parentId);
            json.put("data", data);
            Log.d(LOG_TAG, "Connect timelineMessage API : " + url.toString());
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("POST");
            connection.setRequestProperty("Authorization", apiToken);
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson.isEmpty()){
                if (resultReceiver != null) {
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.0"));
                    resultReceiver.send(Activity.RESULT_CANCELED, answer);
                }
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)) {
                    if (resultReceiver != null) {
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.0"));
                        resultReceiver.send(Activity.RESULT_CANCELED, answer);
                    }
                } else {
                    Log.d(LOG_TAG, "json returned : " + json);
                    JSONObject current = json.getJSONObject("data");
                    ContentValues commentValues = new ContentValues();
                    String creatorId = current.getJSONObject("creator").getString("id");
                    Cursor creator = TimelineGetCreatorId(creatorId);
                    if (creator == null || !creator.moveToFirst())
                        return;
                    commentValues.put(GrappboxContract.TimelineCommentEntry.COLUMN_GRAPPBOX_ID, current.getString("id"));
                    commentValues.put(GrappboxContract.TimelineCommentEntry.COLUMN_LOCAL_TIMELINE_ID, localTimelineId);
                    commentValues.put(GrappboxContract.TimelineCommentEntry.COLUMN_LOCAL_CREATOR_ID, creator.getLong(0));
                    commentValues.put(GrappboxContract.TimelineCommentEntry.COLUMN_PARENT_ID, parentId);
                    commentValues.put(GrappboxContract.TimelineCommentEntry.COLUMN_MESSAGE, current.getString("comment"));
                    String lastEditedMsg = current.isNull("editedAt") ? current.getString("createdAt") : current.getString("editedAt");
                    commentValues.put(GrappboxContract.TimelineCommentEntry.COLUMN_DATE_LAST_EDITED_AT_UTC, lastEditedMsg);
                    getContentResolver().insert(GrappboxContract.TimelineCommentEntry.CONTENT_URI, commentValues);
                    creator.close();
                }
            }
        } catch (IOException | JSONException e) {
            e.printStackTrace();
        } finally {
            timelineGrappbox.close();
            if (connection != null)
                connection.disconnect();
            if (resultReceiver != null){
                resultReceiver.send(Activity.RESULT_OK, null);
            }
        }
        Log.v(LOG_TAG, "add finish");
    }

    private void handleTimelineAddMessage(long localTimelineId, String title, String message, ResultReceiver resultReceiver) {
        Cursor timelineGrappbox = getContentResolver().query(TimelineEntry.buildTimelineWithLocalIdUri(localTimelineId), new String[]{TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_GRAPPBOX_ID}, null, null, null);
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        if (timelineGrappbox == null || !timelineGrappbox.moveToFirst())
            return;

        Log.v(LOG_TAG, "Add message");
        HttpURLConnection connection = null;
        String returnedJson;
        String apiTimelineID = timelineGrappbox.getString(0);
        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/timeline/message/" + apiTimelineID);
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            data.put("title", title);
            data.put("message", message);
            json.put("data", data);
            Log.d(LOG_TAG, "Connect timelineMessage API : " + url.toString());
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("POST");
            connection.setRequestProperty("Authorization", apiToken);
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            Log.d(LOG_TAG, "json returned : " + returnedJson);
            if (returnedJson == null || returnedJson.isEmpty()){
                if (resultReceiver != null) {
                    Bundle answer = new Bundle();
                    answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.0"));
                    resultReceiver.send(Activity.RESULT_CANCELED, answer);
                }
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)) {
                    if (resultReceiver != null) {
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.0"));
                        resultReceiver.send(Activity.RESULT_CANCELED, answer);
                    }
                } else {
                    JSONObject current = json.getJSONObject("data");
                    ContentValues messagesValues = new ContentValues();
                    String creatorId = current.getJSONObject("creator").getString("id");
                    Cursor creator = TimelineGetCreatorId(creatorId);
                    if (creator == null || !creator.moveToFirst())
                        return;
                    messagesValues.put(TimelineMessageEntry.COLUMN_GRAPPBOX_ID, current.getString("id"));
                    messagesValues.put(TimelineMessageEntry.COLUMN_LOCAL_CREATOR_ID, creator.getLong(0));
                    messagesValues.put(TimelineMessageEntry.COLUMN_LOCAL_TIMELINE_ID, localTimelineId);
                    messagesValues.put(TimelineMessageEntry.COLUMN_TITLE, current.getString("title"));
                    messagesValues.put(TimelineMessageEntry.COLUMN_MESSAGE, current.getString("message"));
                    String lastEditedMsg = current.isNull("editedAt") ? current.getString("createdAt") : current.getString("editedAt");
                    messagesValues.put(TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC, lastEditedMsg);
                    messagesValues.put(TimelineMessageEntry.COLUMN_COUNT_ANSWER, 0);
                    getContentResolver().insert(GrappboxContract.TimelineMessageEntry.CONTENT_URI, messagesValues);
                    creator.close();
                }
            }
        } catch (IOException | JSONException e) {
            e.printStackTrace();
        } finally {
            timelineGrappbox.close();
            if (connection != null)
                connection.disconnect();
            if (resultReceiver != null){
                resultReceiver.send(Activity.RESULT_OK, null);
            }
        }
        Log.v(LOG_TAG, "add finish");
    }

    private void handleNextMeetingsSync(long localPID) {}

    private void handleTimelineCommentEdit(long localTimelineId, long messageId, String message, ResultReceiver resultReceiver, long parentId)
    {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        Cursor timelineGrappbox = getContentResolver().query(TimelineEntry.buildTimelineWithLocalIdUri(localTimelineId), new String[]{TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_GRAPPBOX_ID}, null, null, null);

        Log.v(LOG_TAG, "edit message start");
        if (timelineGrappbox == null || !timelineGrappbox.moveToFirst())
            return;
        HttpURLConnection connection = null;
        String returnedJson;
        String apiTimelineID = timelineGrappbox.getString(0);
        timelineGrappbox.close();
        try {
            final URL url  = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/timeline/comment/" + apiTimelineID);
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            data.put("commentId", messageId);
            data.put("comment", message);
            json.put("data", data);
            Log.d(LOG_TAG, "Connect timelineMessage API : " + url.toString());
            Log.d(LOG_TAG, "JSON data : " + json.toString());
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("PUT");
            connection.setRequestProperty("Authorization", apiToken );
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                Bundle answer = new Bundle();
                answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.0"));
                resultReceiver.send(Activity.RESULT_CANCELED, answer);
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)) {
                    if (resultReceiver != null) {
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.0"));
                        resultReceiver.send(Activity.RESULT_CANCELED, answer);
                    }
                } else {
                    Log.d(LOG_TAG, "json returned : " + json);
                    JSONObject current = json.getJSONObject("data");
                    ContentValues commentValues = new ContentValues();
                    String creatorId = current.getJSONObject("creator").getString("id");
                    Cursor creator = TimelineGetCreatorId(creatorId);
                    if (creator == null || !creator.moveToFirst())
                        return;
                    commentValues.put(GrappboxContract.TimelineCommentEntry.COLUMN_GRAPPBOX_ID, current.getString("id"));
                    commentValues.put(GrappboxContract.TimelineCommentEntry.COLUMN_LOCAL_TIMELINE_ID, localTimelineId);
                    commentValues.put(GrappboxContract.TimelineCommentEntry.COLUMN_LOCAL_CREATOR_ID, creator.getLong(0));
                    commentValues.put(GrappboxContract.TimelineCommentEntry.COLUMN_PARENT_ID, parentId);
                    commentValues.put(GrappboxContract.TimelineCommentEntry.COLUMN_MESSAGE, current.getString("comment"));
                    String lastEditedMsg = current.isNull("editedAt") ? current.getString("createdAt") : current.getString("editedAt");
                    commentValues.put(GrappboxContract.TimelineCommentEntry.COLUMN_DATE_LAST_EDITED_AT_UTC, lastEditedMsg);
                    getContentResolver().insert(GrappboxContract.TimelineCommentEntry.CONTENT_URI, commentValues);
                    getContentResolver().insert(GrappboxContract.TimelineCommentEntry.CONTENT_URI, commentValues);
                    creator.close();
                }
            }
        } catch (IOException | JSONException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (resultReceiver != null)
                resultReceiver.send(Activity.RESULT_OK, null);
        }
        Log.v(LOG_TAG, "edit message end");
    }

    private void handleTimelineMessageEdit(long localTimelineId, long messageId, String title, String message, ResultReceiver resultReceiver, long parentId)
    {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        Cursor timelineGrappbox = getContentResolver().query(TimelineEntry.buildTimelineWithLocalIdUri(localTimelineId), new String[]{TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_GRAPPBOX_ID}, null, null, null);

        if (timelineGrappbox == null || !timelineGrappbox.moveToFirst())
            return;
        HttpURLConnection connection = null;
        String returnedJson;
        String apiTimelineID = timelineGrappbox.getString(0);
        timelineGrappbox.close();
        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/timeline/message/" + apiTimelineID + "/" + messageId);
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            data.put("title", title);
            data.put("message", message);
            json.put("data", data);
            Log.d(LOG_TAG, "Connect timelineMessage API : " + url.toString());
            Log.d(LOG_TAG, "JSON data : " + json.toString());
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("PUT");
            connection.setRequestProperty("Authorization", apiToken );
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                Bundle answer = new Bundle();
                answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.0"));
                resultReceiver.send(Activity.RESULT_CANCELED, answer);
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)) {
                    if (resultReceiver != null) {
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.0"));
                        resultReceiver.send(Activity.RESULT_CANCELED, answer);
                    }
                } else {
                    JSONObject current = json.getJSONObject("data");
                    ContentValues messagesValues = new ContentValues();
                    String creatorId = current.getJSONObject("creator").getString("id");
                    Cursor creator = TimelineGetCreatorId(creatorId);
                    if (creator == null || !creator.moveToFirst())
                        return;
                    messagesValues.put(TimelineMessageEntry.COLUMN_GRAPPBOX_ID, current.getString("id"));
                    messagesValues.put(TimelineMessageEntry.COLUMN_LOCAL_CREATOR_ID, creator.getLong(0));
                    messagesValues.put(TimelineMessageEntry.COLUMN_LOCAL_TIMELINE_ID, localTimelineId);
                    messagesValues.put(TimelineMessageEntry.COLUMN_TITLE, current.getString("title"));
                    messagesValues.put(TimelineMessageEntry.COLUMN_MESSAGE, current.getString("message"));
                    String lastEditedMsg = current.isNull("editedAt") ? current.getString("createdAt") : current.getString("editedAt");
                    messagesValues.put(TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC, lastEditedMsg);
                    messagesValues.put(TimelineMessageEntry.COLUMN_COUNT_ANSWER, 0);
                    getContentResolver().insert(GrappboxContract.TimelineMessageEntry.CONTENT_URI, messagesValues);
                    creator.close();
                }
            }
        } catch (IOException | JSONException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (resultReceiver != null)
                resultReceiver.send(Activity.RESULT_OK, null);
        }
        Log.v(LOG_TAG, "edit message end");
    }

    private void handleTimelineCommentDelete(long localTimelineId, long messageId, ResultReceiver resultReceiver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        Cursor timelineGrappbox = getContentResolver().query(TimelineEntry.buildTimelineWithLocalIdUri(localTimelineId), new String[]{TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_GRAPPBOX_ID}, null, null, null);

        Log.v(LOG_TAG, "delete message start");
        if (apiToken == null)
            return;
        if (timelineGrappbox == null || !timelineGrappbox.moveToFirst())
            return;
        HttpURLConnection connection = null;
        String returnedJson;

        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/timeline/comment/" + messageId);

            Log.d(LOG_TAG, "Start delete connection : " + url);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("DELETE");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            Log.d(LOG_TAG, "delete returnedJson : " + returnedJson);
            if (returnedJson != null && !returnedJson.isEmpty()){
                JSONObject json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (resultReceiver != null){
                        Bundle ans = new Bundle();
                        String errorCode =  json.getJSONObject("info").getString("return_code");
                        ans.putInt(BUNDLE_KEY_ERROR_TYPE, Integer.valueOf(errorCode.split("\\.")[2]));
                        ans.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, errorCode));
                        resultReceiver.send(Activity.RESULT_CANCELED, ans);
                    }
                } else {
                    getContentResolver().delete(GrappboxContract.TimelineCommentEntry.CONTENT_URI,
                            GrappboxContract.TimelineCommentEntry.COLUMN_GRAPPBOX_ID + "=?",
                            new String[]{String.valueOf(messageId)});
                }
            }

        } catch (IOException | JSONException e) {
            e.printStackTrace();
        } finally {
            timelineGrappbox.close();
            if (connection != null)
                connection.disconnect();
            if (resultReceiver != null) {
                resultReceiver.send(Activity.RESULT_OK, null);
            }
        }
        Log.v(LOG_TAG, "delete end");
    }

    private void handleTimelineMessageDelete(long localTimelineId, long messageId, ResultReceiver resultReceiver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        Cursor timelineGrappbox = getContentResolver().query(TimelineEntry.buildTimelineWithLocalIdUri(localTimelineId), new String[]{TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_GRAPPBOX_ID}, null, null, null);

        Log.v(LOG_TAG, "delete message start");
        if (apiToken == null)
            return;
        if (timelineGrappbox == null || !timelineGrappbox.moveToFirst())
            return;
        HttpURLConnection connection = null;
        String returnedJson;

        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/timeline/message/" + timelineGrappbox.getString(0) + "/" + messageId);

            Log.d(LOG_TAG, "Start delete connection : " + url);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("DELETE");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            Log.d(LOG_TAG, "delete returnedJson : " + returnedJson);
            if (returnedJson != null && !returnedJson.isEmpty()){
                JSONObject json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (resultReceiver != null){
                        Bundle ans = new Bundle();
                        String errorCode =  json.getJSONObject("info").getString("return_code");
                        ans.putInt(BUNDLE_KEY_ERROR_TYPE, Integer.valueOf(errorCode.split("\\.")[2]));
                        ans.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, errorCode));
                        resultReceiver.send(Activity.RESULT_CANCELED, ans);
                    }
                } else {
                    getContentResolver().delete(TimelineMessageEntry.CONTENT_URI,
                            TimelineMessageEntry.COLUMN_GRAPPBOX_ID + "=?",
                            new String[]{String.valueOf(messageId)});
                }
            }

        } catch (IOException | JSONException e) {
            e.printStackTrace();
        } finally {
            timelineGrappbox.close();
            if (connection != null)
                connection.disconnect();
            if (resultReceiver != null) {
                resultReceiver.send(Activity.RESULT_OK, null);
            }
        }
        Log.v(LOG_TAG, "delete end");
    }

    private void handleTimelineMessagesCommentSync(long localTimelineId, long localTimelineParentId){
        Cursor timelineGrappbox = getContentResolver().query(TimelineEntry.buildTimelineWithLocalIdUri(localTimelineId),
                new String[]{TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_GRAPPBOX_ID},
                null, null, null);
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        if (timelineGrappbox == null || !timelineGrappbox.moveToFirst())
            return;
        HttpURLConnection connection = null;
        String returnedJson;
        String apiTimelineID = timelineGrappbox.getString(0);
        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/timeline/message/comments/" + apiTimelineID + "/" + localTimelineParentId);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            Log.v(LOG_TAG, "URL : " + url);
            Log.v(LOG_TAG, "JSON : " + returnedJson);

            if (returnedJson == null || returnedJson.isEmpty())
                return;
            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json))
                return;
            JSONArray msgs = json.getJSONObject("data").getJSONArray("array");
            ArrayList<Long> existingMessage = new ArrayList<>();
            if (msgs.length() == 0)
                return;
            ContentValues[] messagesValues = new ContentValues[msgs.length()];
            for (int i = 0; i < msgs.length(); ++i) {
                JSONObject current = msgs.getJSONObject(i);
                ContentValues message = new ContentValues();

                String creatorId = current.getJSONObject("creator").getString("id");
                Cursor creator = TimelineGetCreatorId(creatorId);
                if (creator == null || !creator.moveToFirst())
                    return;
                message.put(GrappboxContract.TimelineCommentEntry.COLUMN_GRAPPBOX_ID, current.getString("id"));
                message.put(GrappboxContract.TimelineCommentEntry.COLUMN_PARENT_ID, current.getString("parentId"));
                message.put(GrappboxContract.TimelineCommentEntry.COLUMN_LOCAL_CREATOR_ID, creator.getLong(0));
                message.put(GrappboxContract.TimelineCommentEntry.COLUMN_LOCAL_TIMELINE_ID, localTimelineId);
                message.put(GrappboxContract.TimelineCommentEntry.COLUMN_MESSAGE, current.getString("comment"));
                String lastEditedMsg = current.isNull("editedAt") ? current.getString("createdAt") : current.getString("editedAt");
                message.put(GrappboxContract.TimelineCommentEntry.COLUMN_DATE_LAST_EDITED_AT_UTC, lastEditedMsg);
                String checkSelection = GrappboxContract.TimelineCommentEntry.TABLE_NAME + "." + GrappboxContract.TimelineCommentEntry.COLUMN_MESSAGE + "=? AND "
                        + GrappboxContract.TimelineCommentEntry.TABLE_NAME + "." + GrappboxContract.TimelineCommentEntry.COLUMN_GRAPPBOX_ID + "=? AND "
                        + GrappboxContract.TimelineCommentEntry.TABLE_NAME + "." + GrappboxContract.TimelineCommentEntry.COLUMN_PARENT_ID + "=?";
                String[] checkArgs = new String[] {
                        current.getString("comment"),
                        current.getString("id"),
                        current.getString("parentId")
                };
                Cursor check = getContentResolver().query(GrappboxContract.TimelineCommentEntry.CONTENT_URI, null, checkSelection, checkArgs, null);
                if (check != null && check.moveToFirst()) {
                    existingMessage.add(check.getLong(check.getColumnIndex(GrappboxContract.TimelineCommentEntry._ID)));
                    check.close();
                }

                messagesValues[i] = message;
                creator.close();
            }
            if (existingMessage.size() == 0){
                getContentResolver().delete(TimelineMessageEntry.CONTENT_URI, TimelineMessageEntry.COLUMN_LOCAL_TIMELINE_ID + "=?", new String[] {String.valueOf(localTimelineId)});
            } else {
                String deleteIds = "";
                for (Long id : existingMessage) {
                    deleteIds += deleteIds.isEmpty() ? id.toString() : "," + id.toString();
                }
                String deleteSelection = GrappboxContract.TimelineCommentEntry.COLUMN_LOCAL_TIMELINE_ID + "=? AND " + GrappboxContract.TimelineCommentEntry.COLUMN_PARENT_ID + "=? AND " + GrappboxContract.TimelineCommentEntry._ID + " NOT IN (" + deleteIds + ")";
                getContentResolver().delete(GrappboxContract.TimelineCommentEntry.CONTENT_URI, deleteSelection, new String[]{String.valueOf(localTimelineId), String.valueOf(localTimelineParentId)});
            }
            getContentResolver().bulkInsert(GrappboxContract.TimelineCommentEntry.CONTENT_URI, messagesValues);
        } catch (IOException | JSONException e) {
            e.printStackTrace();
        } finally {
            timelineGrappbox.close();
            if (connection != null)
                connection.disconnect();
        }
    }

    private void handleTimelineMessagesSync(long localTimelineId, int offset, int limit) {
        Cursor timelineGrappbox = getContentResolver().query(TimelineEntry.buildTimelineWithLocalIdUri(localTimelineId), new String[]{TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_GRAPPBOX_ID}, null, null, null);
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        Log.v(LOG_TAG, "Timeline sync start");
        if (timelineGrappbox == null || !timelineGrappbox.moveToFirst()) {
            return;
        }
        HttpURLConnection connection = null;
        String returnedJson;
        String apiTimelineID = timelineGrappbox.getString(0);
        try{
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/timeline/messages/" + apiTimelineID + "/" + String.valueOf(offset) + "/" + String.valueOf(limit));

            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            Log.v(LOG_TAG, "timeline sync url : " + url.toString());
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty())
                return;
            Log.v(LOG_TAG, "timeline json : " + returnedJson);
            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json))
                return;
            JSONArray msgs = json.getJSONObject("data").getJSONArray("array");
            ArrayList<Long> existingMessage = new ArrayList<>();
            if (msgs.length() == 0)
                return;
            ContentValues[] messagesValues = new ContentValues[msgs.length()];
            for (int i = 0; i < msgs.length(); ++i) {
                JSONObject current = msgs.getJSONObject(i);
                ContentValues message = new ContentValues();

                String creatorId = current.getJSONObject("creator").getString("id");
                Cursor creator = TimelineGetCreatorId(creatorId);
                if (creator == null || !creator.moveToFirst())
                    return;
                message.put(TimelineMessageEntry.COLUMN_GRAPPBOX_ID, current.getString("id"));
                message.put(TimelineMessageEntry.COLUMN_LOCAL_CREATOR_ID, creator.getLong(0));
                message.put(TimelineMessageEntry.COLUMN_LOCAL_TIMELINE_ID, localTimelineId);
                message.put(TimelineMessageEntry.COLUMN_TITLE, current.getString("title"));
                message.put(TimelineMessageEntry.COLUMN_MESSAGE, current.getString("message"));
                String lastEditedMsg = current.isNull("editedAt") ? current.getString("createdAt") : current.getString("editedAt");
                //String lastEditedMsg = Utils.Date.getDateFromGrappboxAPIToUTC(current.isNull("editedAt") ? current.getJSONObject("createdAt").getString("date") : current.getJSONObject("editedAt").getString("date"));

                message.put(TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC, lastEditedMsg);
                message.put(TimelineMessageEntry.COLUMN_COUNT_ANSWER, Integer.valueOf(current.getString("nbComment")));
                messagesValues[i] = message;

                String checkSelection = TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_TITLE + "=? AND "
                        + TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_MESSAGE + "=? AND "
                        + TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_GRAPPBOX_ID + "=?";
                String[] checkArgs = new String[] {
                        current.getString("title"),
                        current.getString("message"),
                        current.getString("id")
                };

                Cursor check = getContentResolver().query(TimelineMessageEntry.CONTENT_URI, null, checkSelection, checkArgs, null);
                if (check != null && check.moveToFirst()) {
                    existingMessage.add(check.getLong(check.getColumnIndex(TimelineMessageEntry._ID)));
                    check.close();
                }
                messagesValues[i] = message;

                creator.close();
            }
            if (existingMessage.size() == 0){
                getContentResolver().delete(TimelineMessageEntry.CONTENT_URI, TimelineMessageEntry.COLUMN_LOCAL_TIMELINE_ID + "=?", new String[] {String.valueOf(localTimelineId)});
            } else {
                String deleteIds = "";
                for (Long id : existingMessage){
                    deleteIds += deleteIds.isEmpty() ? id.toString() : "," + id.toString();
                }
                String deleteSelection = TimelineMessageEntry.COLUMN_LOCAL_TIMELINE_ID + "=? AND " + TimelineMessageEntry._ID + " NOT IN (" + deleteIds + ")";
                getContentResolver().delete(TimelineMessageEntry.CONTENT_URI, deleteSelection, new String[]{String.valueOf(localTimelineId)});
            }
            getContentResolver().bulkInsert(TimelineMessageEntry.CONTENT_URI, messagesValues);
        } catch (IOException | JSONException  e) {
            e.printStackTrace();
        } finally {
            timelineGrappbox.close();
            if (connection != null)
                connection.disconnect();
        }
        Log.v(LOG_TAG, "Timeline sync end");
    }

    public static void handleUserDetailSync(Context context, long localUID) {
        Cursor userGrappboxID = context.getContentResolver().query(UserEntry.buildUserWithLocalIdUri(localUID), new String[]{UserEntry.COLUMN_GRAPPBOX_ID}, null, null, null);
        String apiToken = Utils.Account.getAuthTokenService(context, null);

        if (userGrappboxID == null || !userGrappboxID.moveToFirst() || apiToken == null)
            return;
        try {
            handleUserDetailSync(context, userGrappboxID.getString(0));
        } finally {
            userGrappboxID.close();
        }
    }

    public static void handleUserDetailSync(Context context, String apiUID) {
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

    private void handleCalendarMonthSync(String firstDay)
    {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/planning/month/" + firstDay);
            connection = (HttpURLConnection)url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty())
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json))
                return;
            JSONArray events = json.getJSONObject("data").getJSONArray("events");
            if (events.length() == 0)
                return;
            ContentValues[] eventValues = new ContentValues[events.length()];
            for (int i = 0; i < events.length(); ++i) {
                ContentValues event = new ContentValues();
                JSONObject current = events.getJSONObject(i);
                JSONArray participants = current.getJSONArray("users");

                event.put(EventEntry.COLUMN_GRAPPBOX_ID, current.getString("id"));
                Cursor grappboxProjectId = getContentResolver().query(ProjectEntry.CONTENT_URI,
                        new String[] {ProjectEntry.TABLE_NAME + "." + ProjectEntry._ID},
                        ProjectEntry.TABLE_NAME + "." + ProjectEntry.COLUMN_GRAPPBOX_ID + "=?",
                        new String[] {current.getString("id")},
                        null);
                if (grappboxProjectId == null || !grappboxProjectId.moveToFirst())
                    return;
                event.put(EventEntry.COLUMN_LOCAL_PROJECT_ID, grappboxProjectId.getLong(0));
                Pair<String, Long> additionalData = syncProjectInfos(apiToken, current.getString("id"));
                if (additionalData != null)
                    event.put(EventEntry.COLUMN_LOCAL_CREATOR_ID, additionalData.second);
                event.put(EventEntry.COLUMN_EVENT_TITLE, current.getString("title"));
                event.put(EventEntry.COLUMN_EVENT_DESCRIPTION, current.getString("description"));
                event.put(EventEntry.COLUMN_DATE_BEGIN_UTC, current.getString("beginDate"));
                event.put(EventEntry.COLUMN_DATE_END_UTC, current.getString("endDate"));
                eventValues[i] = event;
                grappboxProjectId.close();
            }
            getContentResolver().bulkInsert(EventEntry.CONTENT_URI, eventValues);
        } catch (IOException | NetworkErrorException | JSONException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }

    private void handleEventCreate(Long localProjectId, String title, String description, String begin, String end, List<Long> participantId, ResultReceiver responseObserver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Log.v(LOG_TAG, "title : " + title + ", desc : " + description + ", begin date : " + begin + ", end date : " + end);
        Cursor project = null;
        Cursor addUser = null;
        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);
            String participant = "";
            for (long add : participantId){
                participant += participant.isEmpty() ? "(" + add : "," + add;
            }
            if (!participant.isEmpty()) {
                participant += ")";
                addUser = getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry.COLUMN_GRAPPBOX_ID}, UserEntry._ID+" IN " + participant, null, null);
            }
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/event");
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            JSONArray users = new JSONArray();
            data.put("title", title);
            data.put("description", description);
            data.put("begin", begin);
            data.put("end", end);
            if (localProjectId != -1) {
                project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID + "=?", new String[]{String.valueOf(localProjectId)}, null);
                if (project == null || !project.moveToFirst())
                    throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
                data.put("projectId", project.getString(0));
            }
            if (addUser != null && addUser.moveToFirst()) {
                do {
                    users.put(addUser.getString(0));
                } while (addUser.moveToNext());
            }
            data.put("users", users);
            json.put("data", data);
            connection = (HttpURLConnection)url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("POST");
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
                    Log.v(LOG_TAG, data.toString());
                    ContentValues eventValues = new ContentValues();
                    eventValues.put(EventEntry.COLUMN_GRAPPBOX_ID, data.getString("id"));
                    eventValues.put(EventEntry.COLUMN_EVENT_TITLE, data.getString("title"));
                    if (localProjectId != -1)
                        eventValues.put(EventEntry.COLUMN_LOCAL_PROJECT_ID, localProjectId);
                    eventValues.put(EventEntry.COLUMN_EVENT_DESCRIPTION, data.getString("description"));
                    eventValues.put(EventEntry.COLUMN_DATE_BEGIN_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(data.getString("beginDate")));
                    eventValues.put(EventEntry.COLUMN_DATE_END_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(data.getString("endDate")));
                    String grappboxCID = data.getJSONObject("creator").getString("id");
                    Cursor creatorId = getContentResolver().query(UserEntry.CONTENT_URI, new String[] {UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);
                    if (localProjectId != -1)
                        eventValues.put(EventEntry.COLUMN_LOCAL_PROJECT_ID, localProjectId);
                    if (creatorId == null || !creatorId.moveToFirst()){
                        handleUserDetailSync(this, grappboxCID);
                        creatorId = getContentResolver().query(UserEntry.CONTENT_URI, new String[] {UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);
                        if (creatorId == null || !creatorId.moveToFirst()){
                            throw new UnknownError();
                        }
                    }
                    eventValues.put(EventEntry.COLUMN_LOCAL_CREATOR_ID, creatorId.getLong(0));
                    Uri res = getContentResolver().insert(EventEntry.CONTENT_URI, eventValues);
                    if (res == null)
                        throw new SQLException(Utils.Errors.ERROR_SQL_INSERT_FAILED);
                    creatorId.close();
                    long eventId = Long.parseLong(res.getLastPathSegment());
                    JSONArray usersArray = data.getJSONArray("users");
                    ArrayList<Long> existingUser = new ArrayList<>();
                    for (int j = 0; j < usersArray.length(); ++j){
                        JSONObject currentUser = usersArray.getJSONObject(j);
                        Cursor userCursor = getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{currentUser.getString("id")}, null);
                        ContentValues currentUserParticipant = new ContentValues();
                        if (userCursor == null || !userCursor.moveToFirst()){
                            handleUserDetailSync(this, currentUser.getString("id"));
                            userCursor = getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{currentUser.getString("id")}, null);
                            if (userCursor == null || !userCursor.moveToFirst()) {
                                continue;
                            }
                        }
                        currentUserParticipant.put(GrappboxContract.EventParticipantEntry.COLUMN_LOCAL_USER_ID, userCursor.getLong(0));
                        currentUserParticipant.put(GrappboxContract.EventParticipantEntry.COLUMN_LOCAL_EVENT_ID, eventId);
                        Uri userEntryURI = getContentResolver().insert(GrappboxContract.EventParticipantEntry.CONTENT_URI, currentUserParticipant);
                        if (userEntryURI == null)
                            continue;
                        long userEntryId = Long.parseLong(userEntryURI.getLastPathSegment());
                        existingUser.add(userEntryId);
                        userCursor.close();
                    }
                    String selection = GrappboxContract.EventParticipantEntry.COLUMN_LOCAL_EVENT_ID + "=?" + (existingUser.size() > 0 ? " AND " + GrappboxContract.EventParticipantEntry._ID + " NOT IN (" : "");
                    if (existingUser.size() > 0) {
                        boolean isFirst = true;
                        for (Long idUser : existingUser) {
                            selection += isFirst ? idUser.toString() : "," + idUser.toString();
                            isFirst = false;
                        }
                        selection += ")";
                    }
                    getContentResolver().delete(GrappboxContract.EventParticipantEntry.CONTENT_URI, selection, new String[]{String.valueOf(eventId)});
                    Cursor event = getContentResolver().query(EventEntry.CONTENT_URI, null, EventEntry._ID + "=?", new String[]{String.valueOf(eventId)}, null);
                    if (event != null) {
                        if (event.moveToFirst() && responseObserver != null) {
                            Bundle answer = new Bundle();
                            CalendarEventModel model = new CalendarEventModel(event);
                            answer.putParcelable(CalendarEventReceiver.EXTRA_CALENDAR_EVENT_MODEL, model);
                            responseObserver.send(Activity.RESULT_OK, answer);
                        }
                    }
                }
            }
        } catch (IOException | JSONException | NetworkErrorException | ParseException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null)
                project.close();
            if (addUser != null)
                addUser.close();

        }
    }

    private void handleEventEdit(Long localEventId, Long localProjectId, String title, String description, String begin, String end, List<Long> toAdd, List<Long> toRemove, ResultReceiver resultReceiver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        Cursor eventGrappbox = getContentResolver().query(EventEntry.buildEventWithLocalIdUri(localEventId), new String[]{EventEntry.TABLE_NAME + "." + EventEntry.COLUMN_GRAPPBOX_ID}, null, null, null);
        Cursor project = null;
        Cursor addUser = null;
        Cursor removeUser = null;
        if (eventGrappbox == null || !eventGrappbox.moveToFirst())
            return;
        HttpURLConnection connection = null;
        String returnedJson;
        String apiEventId = eventGrappbox.getString(0);
        eventGrappbox.close();
        try {
            String addParticipant = "";
            String delParticipant = "";
            for (long addList : toAdd) {
                addParticipant += addParticipant.isEmpty() ? "(" + addList : "," + addList;
            }
            if (!addParticipant.isEmpty()){
                addParticipant += ")";
                addUser = getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry.COLUMN_GRAPPBOX_ID}, UserEntry._ID + " IN " + addParticipant, null, null);
            }
            for (long delList : toRemove) {
                delParticipant += delParticipant.isEmpty() ? "(" + delList : "," + delList;
            }
            if (!delParticipant.isEmpty()) {
                delParticipant += ")";
                removeUser = getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry.COLUMN_GRAPPBOX_ID}, UserEntry._ID + " IN " + delParticipant, null, null);
            }
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/event/" + apiEventId);
            connection = (HttpURLConnection)url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("PUT");
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            JSONArray add = new JSONArray();
            JSONArray remove = new JSONArray();
            if (localProjectId != -1) {
                project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID + "=?", new String[]{String.valueOf(localProjectId)}, null);
                if (project == null || !project.moveToFirst())
                    throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
                data.put("projectId", project.getString(0));
            }
            data.put("title", title);
            data.put("description", description);
            data.put("begin", begin);
            data.put("end", end);
            if (addUser != null && addUser.moveToFirst()) {
                do {
                    add.put(addUser.getString(0));
                } while (addUser.moveToNext());
            }
            data.put("toAddUsers", add);
            if (removeUser != null && removeUser.moveToFirst()) {
                do {
                    remove.put(removeUser.getString(0));
                } while (removeUser.moveToNext());
            }
            data.put("toRemoveUsers", remove);
            json.put("data", data);
            Log.v(LOG_TAG, "json edit : " + data);
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()) {
                throw new NetworkErrorException("Returned JSON is empty");
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)) {

                } else {
                    data = json.getJSONObject("data");
                    Log.v(LOG_TAG, data.toString());
                    ContentValues eventValues = new ContentValues();
                    eventValues.put(EventEntry.COLUMN_GRAPPBOX_ID, data.getString("id"));
                    eventValues.put(EventEntry.COLUMN_EVENT_TITLE, data.getString("title"));
                    if (localProjectId != -1)
                        eventValues.put(EventEntry.COLUMN_LOCAL_PROJECT_ID, localProjectId);
                    eventValues.put(EventEntry.COLUMN_EVENT_DESCRIPTION, data.getString("description"));
                    eventValues.put(EventEntry.COLUMN_DATE_BEGIN_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(data.getString("beginDate")));
                    eventValues.put(EventEntry.COLUMN_DATE_END_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(data.getString("endDate")));
                    String grappboxCID = data.getJSONObject("creator").getString("id");
                    Cursor creatorId = getContentResolver().query(UserEntry.CONTENT_URI, new String[] {UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);
                    if (localProjectId != -1)
                        eventValues.put(EventEntry.COLUMN_LOCAL_PROJECT_ID, localProjectId);
                    if (creatorId == null || !creatorId.moveToFirst()){
                        handleUserDetailSync(this, grappboxCID);
                        creatorId = getContentResolver().query(UserEntry.CONTENT_URI, new String[] {UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);
                        if (creatorId == null || !creatorId.moveToFirst()){
                            throw new UnknownError();
                        }
                    }
                    eventValues.put(EventEntry.COLUMN_LOCAL_CREATOR_ID, creatorId.getLong(0));
                    Uri res = getContentResolver().insert(EventEntry.CONTENT_URI, eventValues);
                    if (res == null)
                        throw new SQLException(Utils.Errors.ERROR_SQL_INSERT_FAILED);
                    creatorId.close();
                    long eventId = Long.parseLong(res.getLastPathSegment());
                    JSONArray usersArray = data.getJSONArray("users");
                    ArrayList<Long> existingUser = new ArrayList<>();
                    for (int j = 0; j < usersArray.length(); ++j){
                        JSONObject currentUser = usersArray.getJSONObject(j);
                        Cursor userCursor = getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{currentUser.getString("id")}, null);
                        ContentValues currentUserParticipant = new ContentValues();
                        if (userCursor == null || !userCursor.moveToFirst()){
                            handleUserDetailSync(this, currentUser.getString("id"));
                            userCursor = getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{currentUser.getString("id")}, null);
                            if (userCursor == null || !userCursor.moveToFirst()) {
                                continue;
                            }
                        }
                        currentUserParticipant.put(GrappboxContract.EventParticipantEntry.COLUMN_LOCAL_USER_ID, userCursor.getLong(0));
                        currentUserParticipant.put(GrappboxContract.EventParticipantEntry.COLUMN_LOCAL_EVENT_ID, eventId);
                        Uri userEntryURI = getContentResolver().insert(GrappboxContract.EventParticipantEntry.CONTENT_URI, currentUserParticipant);
                        if (userEntryURI == null)
                            continue;
                        long userEntryId = Long.parseLong(userEntryURI.getLastPathSegment());
                        existingUser.add(userEntryId);
                        userCursor.close();
                    }
                    String selection = GrappboxContract.EventParticipantEntry.COLUMN_LOCAL_EVENT_ID + "=?" + (existingUser.size() > 0 ? " AND " + GrappboxContract.EventParticipantEntry._ID + " NOT IN (" : "");
                    if (existingUser.size() > 0) {
                        boolean isFirst = true;
                        for (Long idUser : existingUser) {
                            selection += isFirst ? idUser.toString() : "," + idUser.toString();
                            isFirst = false;
                        }
                        selection += ")";
                    }
                    getContentResolver().delete(GrappboxContract.EventParticipantEntry.CONTENT_URI, selection, new String[]{String.valueOf(eventId)});
                }
            }
        } catch (JSONException | IOException | NetworkErrorException | ParseException e) {
            e.printStackTrace();
        } finally {
            if (project != null)
                project.close();
            if (addUser != null)
                addUser.close();
            if (removeUser != null)
                removeUser.close();
        }
    }

    private void handleEventGet(Long localEventId) {

    }

    private void handleEventDelete(Long localEventId, ResultReceiver responseObserver) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor event = null;

        try {
            if (apiToken == null)
                throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_TOKEN);
            event = getContentResolver().query(EventEntry.CONTENT_URI, new String[]{EventEntry.TABLE_NAME + "." + EventEntry.COLUMN_GRAPPBOX_ID}, EventEntry.TABLE_NAME + "." + EventEntry._ID + "=?", new String[]{String.valueOf(localEventId)}, null);
            if (event == null || !event.moveToFirst())
                throw new IllegalArgumentException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/event/" + event.getString(0));
            JSONObject json;

            connection = (HttpURLConnection)url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("DELETE");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()) {
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            } else {
                Log.v(LOG_TAG, "returnedJSON : " + returnedJson);
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)) {
                    if (responseObserver != null) {
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                } else {
                    getContentResolver().delete(EventEntry.CONTENT_URI, EventEntry._ID + "=?", new String[]{String.valueOf(localEventId)});
                    if (responseObserver != null)
                        responseObserver.send(Activity.RESULT_OK, null);
                }
            }
        } catch (NetworkErrorException | IOException | JSONException e) {
            e.printStackTrace();
        } finally {
            if (event != null)
                event.close();
        }
    }

    private void handleEventSetParticipant(Long localEventId, List<Long> toAdd, List<Long> toDelete, ResultReceiver responseObserver){

    }

    private void handleSyncEvent(int monthOffset) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        SimpleDateFormat format = new SimpleDateFormat("yyyy-MM-dd");
        Calendar calendar = Calendar.getInstance();
        calendar.set(Calendar.DAY_OF_MONTH, 1);
        calendar.add(Calendar.MONTH, Integer.valueOf(monthOffset));
        String firstDayOfTheMonth = format.format(calendar.getTime());
        HttpURLConnection connection = null;
        String returnedJson;
        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/planning/month/" + firstDayOfTheMonth);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            Log.v(LOG_TAG, "url : " + url.toString() + ", apiToken : " + apiToken);
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty())
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json))
                throw new OperationApplicationException(Utils.Errors.ERROR_API_GENERIC);
            Log.v(LOG_TAG, "getPlanningMonth : " + json.toString());
            JSONObject array = json.getJSONObject("data").getJSONObject("array");
            if (!array.has("events"))
                return ;
            JSONArray arrayEvent = array.getJSONArray("events");
            for (int i = 0; i < arrayEvent.length(); ++i) {
                JSONObject currentEvent = arrayEvent.getJSONObject(i);
                ContentValues event = new ContentValues();

                event.put(GrappboxContract.EventEntry.COLUMN_GRAPPBOX_ID, currentEvent.getString("id"));
                event.put(GrappboxContract.EventEntry.COLUMN_EVENT_TITLE, currentEvent.getString("title"));
                event.put(GrappboxContract.EventEntry.COLUMN_EVENT_TITLE, currentEvent.getString("title"));
                event.put(GrappboxContract.EventEntry.COLUMN_EVENT_DESCRIPTION, currentEvent.getString("description"));
                event.put(GrappboxContract.EventEntry.COLUMN_DATE_BEGIN_UTC, currentEvent.getString("beginDate"));
                event.put(GrappboxContract.EventEntry.COLUMN_DATE_END_UTC, currentEvent.getString("endDate"));
                String grappboxCID = currentEvent.getJSONObject("creator").getString("id");
                Cursor creatorId = getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{grappboxCID}, null);
                if (creatorId == null || !creatorId.moveToFirst()) {
                    throw new UnknownError();
                }
                event.put(GrappboxContract.EventEntry.COLUMN_LOCAL_CREATOR_ID, creatorId.getLong(0));
                creatorId.close();
                if (!currentEvent.getString("projectId").equals("null")) {
                    Cursor projectID = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry._ID}, ProjectEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{currentEvent.getString("projectId")}, null);
                    if (projectID == null || !projectID.moveToFirst()) {
                        throw new UnknownError();
                    }
                    event.put(EventEntry.COLUMN_LOCAL_PROJECT_ID, projectID.getLong(0));
                    projectID.close();
                }
                Uri res = getContentResolver().insert(GrappboxContract.EventEntry.CONTENT_URI, event);
                if (res == null)
                    throw new SQLException(Utils.Errors.ERROR_SQL_INSERT_FAILED);
                long eventId = Long.parseLong(res.getLastPathSegment());
                JSONArray usersArray = currentEvent.getJSONArray("users");
                ArrayList<Long> existingUser = new ArrayList<>();
                for (int j = 0; j < usersArray.length(); ++j){
                    JSONObject currentUser = usersArray.getJSONObject(j);
                    Cursor userCursor = getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{currentUser.getString("id")}, null);
                    ContentValues currentUserParticipant = new ContentValues();
                    if (userCursor == null || !userCursor.moveToFirst()){
                        handleUserDetailSync(this, currentUser.getString("id"));
                        userCursor = getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{currentUser.getString("id")}, null);
                        if (userCursor == null || !userCursor.moveToFirst()) {
                            continue;
                        }
                    }
                    currentUserParticipant.put(GrappboxContract.EventParticipantEntry.COLUMN_LOCAL_USER_ID, userCursor.getLong(0));
                    currentUserParticipant.put(GrappboxContract.EventParticipantEntry.COLUMN_LOCAL_EVENT_ID, eventId);
                    Uri userEntryURI = getContentResolver().insert(GrappboxContract.EventParticipantEntry.CONTENT_URI, currentUserParticipant);
                    if (userEntryURI == null)
                        continue;
                    long userEntryId = Long.parseLong(userEntryURI.getLastPathSegment());
                    existingUser.add(userEntryId);
                    userCursor.close();
                }
                String selection = GrappboxContract.EventParticipantEntry.COLUMN_LOCAL_EVENT_ID + "=?" + (existingUser.size() > 0 ? " AND " + GrappboxContract.EventParticipantEntry._ID + " NOT IN (" : "");
                if (existingUser.size() > 0) {
                    boolean isFirst = true;
                    for (Long idUser : existingUser) {
                        selection += isFirst ? idUser.toString() : "," + idUser.toString();
                        isFirst = false;
                    }
                    selection += ")";
                }
                getContentResolver().delete(GrappboxContract.EventParticipantEntry.CONTENT_URI, selection, new String[]{String.valueOf(eventId)});
            }
        } catch (IOException | NetworkErrorException | JSONException | OperationApplicationException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }

    private void handleAllStatGet(String apiToken, long projectId)
    {
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null;

        try {
            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID + "=?", new String[]{String.valueOf(projectId)}, null);
            if (project == null || !project.moveToFirst())
                return;
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/statistics/" + project.getLong(0));
            connection = (HttpURLConnection)url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            Log.v(LOG_TAG, "url : " + url.toString() + ", apiToken : " + apiToken);
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty())
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            Log.v(LOG_TAG, "returnedJSON : " + returnedJson);
            JSONObject json = new JSONObject(returnedJson).getJSONObject("data");
            ContentValues projectStat = new ContentValues();
            projectStat.put(GrappboxContract.StatEntry.COLUMN_GRAPPBOX_ID, String.valueOf(projectId));
            projectStat.put(GrappboxContract.StatEntry.COLUMN_TIMELINE_TEAM_MESSAGE, Integer.valueOf(json.getJSONObject("timelinesMessageNumber").getString("team")));
            projectStat.put(GrappboxContract.StatEntry.COLUMN_TIMELINE_CUSTOMER_MESSAGE, Integer.valueOf(json.getJSONObject("timelinesMessageNumber").getString("customer")));
            projectStat.put(GrappboxContract.StatEntry.COLUMN_CUSTOMER_ACCESS_ACTUAL, Integer.valueOf(json.getJSONObject("customerAccessNumber").getString("actual")));
            projectStat.put(GrappboxContract.StatEntry.COLUMN_CUSTOMER_ACCESS_MAX, Integer.valueOf(json.getJSONObject("customerAccessNumber").getString("maximum")));
            projectStat.put(GrappboxContract.StatEntry.COLUMN_BUG_OPEN, Integer.valueOf(json.getJSONObject("openCloseBug").getString("open")));
            projectStat.put(GrappboxContract.StatEntry.COLUMN_BUG_CLOSE, Integer.valueOf(json.getJSONObject("openCloseBug").getString("closed")));
            projectStat.put(GrappboxContract.StatEntry.COLUMN_TASK_DONE, Integer.valueOf(json.getJSONObject("taskStatus").getString("done")));
            projectStat.put(GrappboxContract.StatEntry.COLUMN_TASK_DOING, Integer.valueOf(json.getJSONObject("taskStatus").getString("doing")));
            projectStat.put(GrappboxContract.StatEntry.COLUMN_TASK_TODO, Integer.valueOf(json.getJSONObject("taskStatus").getString("toDo")));
            projectStat.put(GrappboxContract.StatEntry.COLUMN_TASK_LATE, Integer.valueOf(json.getJSONObject("taskStatus").getString("late")));
            projectStat.put(GrappboxContract.StatEntry.COLUMN_TASK_TOTAL, Integer.valueOf(json.getString("totalTasks")));
            projectStat.put(GrappboxContract.StatEntry.COLUMN_CLIENT_BUGTRACKER, Integer.valueOf(json.getString("clientBugTracker")));
            projectStat.put(GrappboxContract.StatEntry.COLUMN_BUGTRACKER_ASSIGN, Integer.valueOf(json.getJSONObject("bugAssignationTracker").getString("assigned")));
            projectStat.put(GrappboxContract.StatEntry.COLUMN_BUGTRACKER_UNASSIGN, Integer.valueOf(json.getJSONObject("bugAssignationTracker").getString("unassigned")));
            projectStat.put(GrappboxContract.StatEntry.COLUMN_STORAGE_OCCUPIED, Integer.valueOf(json.getJSONObject("storageSize").getString("occupied")));
            projectStat.put(GrappboxContract.StatEntry.COLUMN_STORAGE_TOTAL, Integer.valueOf(json.getJSONObject("storageSize").getString("total")));
            projectStat.put(GrappboxContract.StatEntry.COLUMN_LOCAL_PROJECT_ID, projectId);
            Uri res = getContentResolver().insert(GrappboxContract.StatEntry.CONTENT_URI, projectStat);
            if (res == null)
                throw new SQLException(Utils.Errors.ERROR_SQL_INSERT_FAILED);
            long statId = Long.parseLong(res.getLastPathSegment());

            //Project Advancement Stat
            JSONArray contentArray = json.getJSONArray("projectAdvancement");
            ArrayList<Long> existingEntry = new ArrayList<>();
            for (int i = 0; i  < contentArray.length(); ++i) {
                JSONObject advancement = contentArray.getJSONObject(i);
                ContentValues ad = new ContentValues();
                ad.put(GrappboxContract.AdvancementEntry.COLUMN_ADVANCEMENT_DATE, advancement.getJSONObject("date").getString("date"));
                ad.put(GrappboxContract.AdvancementEntry.COLUMN_PERCENTAGE, advancement.getInt("percentage"));
                ad.put(GrappboxContract.AdvancementEntry.COLUMN_PROGRESS, advancement.getInt("progress"));
                ad.put(GrappboxContract.AdvancementEntry.COLUMN_TOTAL_TASK, advancement.getInt("totalTasks"));
                ad.put(GrappboxContract.AdvancementEntry.COLUMN_FINISHED_TASk, advancement.getInt("finishedTasks"));
                ad.put(GrappboxContract.AdvancementEntry.COLUMN_LOCAL_STATS_ID, statId);
                Uri adEntryURI =  getContentResolver().insert(GrappboxContract.AdvancementEntry.CONTENT_URI, ad);
                if (adEntryURI == null)
                    continue;
                Long adEntryId = Long.parseLong(adEntryURI.getLastPathSegment());
                existingEntry.add(adEntryId);
            }
            String selection = GrappboxContract.AdvancementEntry.COLUMN_LOCAL_STATS_ID + "=?" + (existingEntry.size() > 0 ? " AND " + GrappboxContract.AdvancementEntry._ID + " NOT IN (" : "");
            if (existingEntry.size() > 0) {
                boolean isFirst = true;
                for (Long idAd : existingEntry) {
                    selection += isFirst ? idAd.toString() : "," + idAd.toString();
                    isFirst = false;
                }
                selection += ")";
            }
            getContentResolver().delete(GrappboxContract.AdvancementEntry.CONTENT_URI, selection, new String[]{String.valueOf(statId)});

            //User Task Advancement Stats
            contentArray = json.getJSONArray("userTasksAdvancement");
            existingEntry.clear();
            for (int i = 0; i < contentArray.length(); ++i) {
                JSONObject userTaskAd = contentArray.getJSONObject(i);
                ContentValues userTask = new ContentValues();
                userTask.put(GrappboxContract.UserAdvancementTaskEntry.COLUMN_LOCAL_USER_ID, userTaskAd.getJSONObject("user").getLong("id"));
                userTask.put(GrappboxContract.UserAdvancementTaskEntry.COLUMN_LOCAL_STAT_ID, statId);
                userTask.put(GrappboxContract.UserAdvancementTaskEntry.COLUMN_TASK_TODO, userTaskAd.getLong("tasksToDo"));
                userTask.put(GrappboxContract.UserAdvancementTaskEntry.COLUMN_TASK_DOING, userTaskAd.getLong("tasksDoing"));
                userTask.put(GrappboxContract.UserAdvancementTaskEntry.COLUMN_TASK_DONE, userTaskAd.getLong("tasksDone"));
                userTask.put(GrappboxContract.UserAdvancementTaskEntry.COLUMN_TASK_LATE, userTaskAd.getLong("tasksLate"));
                Uri userTaskAdEntry = getContentResolver().insert(GrappboxContract.UserAdvancementTaskEntry.CONTENT_URI, userTask);
                if (userTaskAdEntry == null)
                    continue;
                long userTaskEntryId = Long.parseLong(userTaskAdEntry.getLastPathSegment());
                existingEntry.add(userTaskEntryId);
            }
            selection = GrappboxContract.UserAdvancementTaskEntry.COLUMN_LOCAL_STAT_ID + "=?" + (existingEntry.size() > 0 ? " AND " + GrappboxContract.AdvancementEntry._ID + " NOT IN (" : "");
            if (existingEntry.size() > 0) {
                boolean isFirst = true;
                for (Long idAd : existingEntry) {
                    selection += isFirst ? idAd.toString() : "," + idAd.toString();
                    isFirst = false;
                }
                selection += ")";
            }
            getContentResolver().delete(GrappboxContract.UserAdvancementTaskEntry.CONTENT_URI, selection, new String[]{String.valueOf(statId)});

            //Late Task Stats
            contentArray = json.getJSONArray("lateTask");
            existingEntry.clear();
            for (int i = 0; i < contentArray.length(); ++i) {
                JSONObject userTaskAd = contentArray.getJSONObject(i);
                ContentValues userTask = new ContentValues();
                userTask.put(GrappboxContract.LateTaskEntry.COLUMN_LOCAL_USER_ID, userTaskAd.getJSONObject("user").getLong("id"));
                userTask.put(GrappboxContract.LateTaskEntry.COLUMN_LOCAL_STAT_ID, statId);
                userTask.put(GrappboxContract.LateTaskEntry.COLUMN_ROLE, userTaskAd.getString("role"));
                userTask.put(GrappboxContract.LateTaskEntry.COLUMN_DATE, userTaskAd.getJSONObject("date").getString("date"));
                userTask.put(GrappboxContract.LateTaskEntry.COLUMN_ON_TIME_TASK, userTaskAd.getLong("ontimeTasks"));
                userTask.put(GrappboxContract.LateTaskEntry.COLUMN_LATE_TASK, userTaskAd.getLong("lateTasks"));
                Uri userTaskAdEntry = getContentResolver().insert(GrappboxContract.LateTaskEntry.CONTENT_URI, userTask);
                if (userTaskAdEntry == null)
                    continue;
                long userTaskEntryId = Long.parseLong(userTaskAdEntry.getLastPathSegment());
                existingEntry.add(userTaskEntryId);
            }
            selection = GrappboxContract.LateTaskEntry.COLUMN_LOCAL_STAT_ID + "=?" + (existingEntry.size() > 0 ? " AND " + GrappboxContract.LateTaskEntry._ID + " NOT IN (" : "");
            if (existingEntry.size() > 0) {
                boolean isFirst = true;
                for (Long idAd : existingEntry) {
                    selection += isFirst ? idAd.toString() : "," + idAd.toString();
                    isFirst = false;
                }
                selection += ")";
            }
            getContentResolver().delete(GrappboxContract.LateTaskEntry.CONTENT_URI, selection, new String[]{String.valueOf(statId)});

            //Bug Users Repartitions Stats
            contentArray = json.getJSONArray("bugsUsersRepartition");
            existingEntry.clear();
            for (int i = 0; i < contentArray.length(); ++i) {
                JSONObject userTaskAd = contentArray.getJSONObject(i);
                ContentValues userTask = new ContentValues();
                userTask.put(GrappboxContract.BugUserRepartitionEntry.COLUMN_LOCAL_USER_ID, userTaskAd.getJSONObject("user").getLong("id"));
                userTask.put(GrappboxContract.BugUserRepartitionEntry.COLUMN_LOCAL_STAT_ID, statId);
                userTask.put(GrappboxContract.BugUserRepartitionEntry.COLUMN_VALUE, userTaskAd.getLong("value"));
                userTask.put(GrappboxContract.BugUserRepartitionEntry.COLUMN_PERCENTAGE, userTaskAd.getLong("percentage"));
                Uri userTaskAdEntry = getContentResolver().insert(GrappboxContract.BugUserRepartitionEntry.CONTENT_URI, userTask);
                if (userTaskAdEntry == null)
                    continue;
                long userTaskEntryId = Long.parseLong(userTaskAdEntry.getLastPathSegment());
                existingEntry.add(userTaskEntryId);
            }
            selection = GrappboxContract.BugUserRepartitionEntry.COLUMN_LOCAL_STAT_ID + "=?" + (existingEntry.size() > 0 ? " AND " + GrappboxContract.BugUserRepartitionEntry._ID + " NOT IN (" : "");
            if (existingEntry.size() > 0) {
                boolean isFirst = true;
                for (Long idAd : existingEntry) {
                    selection += isFirst ? idAd.toString() : "," + idAd.toString();
                    isFirst = false;
                }
                selection += ")";
            }
            getContentResolver().delete(GrappboxContract.BugUserRepartitionEntry.CONTENT_URI, selection, new String[]{String.valueOf(statId)});

            //Task Repartitions Stats
            contentArray = json.getJSONArray("tasksRepartition");
            existingEntry.clear();
            for (int i = 0; i < contentArray.length(); ++i) {
                JSONObject userTaskAd = contentArray.getJSONObject(i);
                ContentValues userTask = new ContentValues();
                userTask.put(GrappboxContract.TaskRepartitionEntry.COLUMN_LOCAL_USER_ID, userTaskAd.getJSONObject("user").getLong("id"));
                userTask.put(GrappboxContract.TaskRepartitionEntry.COLUMN_LOCAL_STAT_ID, statId);
                userTask.put(GrappboxContract.TaskRepartitionEntry.COLUMN_VALUE, userTaskAd.getLong("value"));
                userTask.put(GrappboxContract.TaskRepartitionEntry.COLUMN_PERCENTAGE, userTaskAd.getLong("percentage"));
                Uri userTaskAdEntry = getContentResolver().insert(GrappboxContract.TaskRepartitionEntry.CONTENT_URI, userTask);
                if (userTaskAdEntry == null)
                    continue;
                long userTaskEntryId = Long.parseLong(userTaskAdEntry.getLastPathSegment());
                existingEntry.add(userTaskEntryId);
            }
            selection = GrappboxContract.TaskRepartitionEntry.COLUMN_LOCAL_STAT_ID + "=?" + (existingEntry.size() > 0 ? " AND " + GrappboxContract.TaskRepartitionEntry._ID + " NOT IN (" : "");
            if (existingEntry.size() > 0) {
                boolean isFirst = true;
                for (Long idAd : existingEntry) {
                    selection += isFirst ? idAd.toString() : "," + idAd.toString();
                    isFirst = false;
                }
                selection += ")";
            }
            getContentResolver().delete(GrappboxContract.TaskRepartitionEntry.CONTENT_URI, selection, new String[]{String.valueOf(statId)});

            //User Working charge Stats
            contentArray = json.getJSONArray("userWorkingCharge");
            existingEntry.clear();
            for (int i = 0; i < contentArray.length(); ++i) {
                JSONObject userTaskAd = contentArray.getJSONObject(i);
                ContentValues userTask = new ContentValues();
                userTask.put(GrappboxContract.UserWorkingChargeEntry.COLUMN_LOCAL_USER_ID, userTaskAd.getJSONObject("user").getLong("id"));
                userTask.put(GrappboxContract.UserWorkingChargeEntry.COLUMN_LOCAL_STAT_ID, statId);
                userTask.put(GrappboxContract.UserWorkingChargeEntry.COLUMN_CHARGE, userTaskAd.getLong("charge"));
                Uri userTaskAdEntry = getContentResolver().insert(GrappboxContract.UserWorkingChargeEntry.CONTENT_URI, userTask);
                if (userTaskAdEntry == null)
                    continue;
                long userTaskEntryId = Long.parseLong(userTaskAdEntry.getLastPathSegment());
                existingEntry.add(userTaskEntryId);
            }
            selection = GrappboxContract.UserWorkingChargeEntry.COLUMN_LOCAL_STAT_ID + "=?" + (existingEntry.size() > 0 ? " AND " + GrappboxContract.UserWorkingChargeEntry._ID + " NOT IN (" : "");
            if (existingEntry.size() > 0) {
                boolean isFirst = true;
                for (Long idAd : existingEntry) {
                    selection += isFirst ? idAd.toString() : "," + idAd.toString();
                    isFirst = false;
                }
                selection += ")";
            }
            getContentResolver().delete(GrappboxContract.UserWorkingChargeEntry.CONTENT_URI, selection, new String[]{String.valueOf(statId)});

        } catch (IOException | NetworkErrorException | JSONException e) {
            e.printStackTrace();
        } finally {
            if (project != null)
                project.close();
            if (connection != null)
                connection.disconnect();
        }
    }
}