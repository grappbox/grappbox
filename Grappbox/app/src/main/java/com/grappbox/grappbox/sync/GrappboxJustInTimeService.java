package com.grappbox.grappbox.sync;

import android.accounts.AccountManager;
import android.accounts.NetworkErrorException;
import android.app.Activity;
import android.app.DownloadManager;
import android.app.IntentService;
import android.app.Notification;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.ContentValues;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.database.Cursor;
import android.graphics.BitmapFactory;
import android.net.Uri;
import android.os.Bundle;
import android.os.Environment;
import android.provider.OpenableColumns;
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
import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.URISyntaxException;
import java.net.URL;
import java.text.ParseException;
import java.util.ArrayList;
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
    public static final String ACTION_CLOUD_DELETE = "com.grappbox.grappbox.sync.ACTION_CLOUD_DELETE";
    public static final String ACTION_CLOUD_DOWNLOAD = "com.grappbox.grappbox.sync.ACTION_CLOUD_DOWNLOAD";
    public static final String ACTION_SYNC_BUG_COMMENT = "com.grappbox.grappbox.sync.ACTION_SYNC_BUG_COMMENT";
    public static final String ACTION_TIMELINE_ADD_MESSAGE = "com.grappbox.grappbox.sync.ACTION_TIMELINE_ADD_MESSAGE";
    public static final String ACTION_TIMELINE_EDIT_MESSAGE = "com.grappbox.grappbox.sync.ACTION_TIMELINE_EDIT_MESSAGE";
    public static final String ACTION_TIMELINE_DELETE_MESSAGE = "com.grappbox.grappbox.sync.ACTION_TIMELINE_DELETE_MESSAGE";

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

    public static final String CATEGORY_GRAPPBOX_ID = "com.grappbox.grappbox.sync.CATEGORY_GRAPPBOX_ID";
    public static final String CATEGORY_LOCAL_ID = "com.grappbox.grappbox.sync.CATEGORY_LOCAL_ID";
    public static final String CATEGORY_CLOSED = "com.grappbox.grappbox.sync.CATEGORY_CLOSED";
    public static final String CATEGORY_OPENED = "com.grappbox.grappbox.sync.CATEGORY_OPENED";


    public static final String BUNDLE_KEY_JSON = "com.grappbox.grappbox.sync.BUNDLE_KEY_JSON";
    public static final String BUNDLE_KEY_ERROR_MSG = "com.grappbox.grappbox.sync.BUNDLE_KEY_ERROR_MSG";
    public static final String BUNDLE_KEY_ERROR_TYPE = "com.grappbox.grappbox.sync.BUNDLE_KEY_ERROR_TYPE";

    public static final int NOTIF_CLOUD_FILE_UPLOAD = 2000;

    public static final int CLOUD_DATA_BYTE_READ = 5242880; //5 megabytes are upload in a chunk

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
            else if (ACTION_SYNC_BUGS.equals(action)){
                boolean isClosedSyncing = intent.getCategories() != null && intent.getCategories().contains(CATEGORY_CLOSED);
                handleBugsSync(intent.getLongExtra(EXTRA_USER_ID, -1), intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getIntExtra(EXTRA_OFFSET, 0), intent.getIntExtra(EXTRA_LIMIT, 50), isClosedSyncing, responseObserver);
            }
            else if (ACTION_SYNC_TIMELINE_MESSAGES.equals(action)) {
                handleTimelineMessagesSync(intent.getLongExtra(EXTRA_TIMELINE_ID, -1), intent.getIntExtra(EXTRA_OFFSET, 0), intent.getIntExtra(EXTRA_LIMIT, 50));
            }
            else if (ACTION_TIMELINE_ADD_MESSAGE.equals(action)) {
                handleTimelineAddMessage(intent.getLongExtra(EXTRA_TIMELINE_ID, -1), intent.getStringExtra(EXTRA_TIMELINE_TITLE), intent.getStringExtra(EXTRA_TIMELINE_MESSAGE), intent.getIntExtra(EXTRA_TIMELINE_PARENT_ID, -1), responseObserver);
            }
            else if (ACTION_TIMELINE_EDIT_MESSAGE.equals(action)) {
                handleTimelineMessageEdit(intent.getLongExtra(EXTRA_TIMELINE_ID, -1), intent.getLongExtra(EXTRA_TIMELINE_MESSAGE_ID, -1), intent.getStringExtra(EXTRA_TIMELINE_TITLE), intent.getStringExtra(EXTRA_TIMELINE_MESSAGE),  responseObserver);
            }
            else if (ACTION_TIMELINE_DELETE_MESSAGE.equals(action)) {
                handleTimelineMessageDelete(intent.getLongExtra(EXTRA_TIMELINE_ID, -1), intent.getLongExtra(EXTRA_TIMELINE_MESSAGE_ID, -1),  responseObserver);
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
            String urlBuilder = "/cloud/" + (isSecured ? "filesecured" : "file") + "/" + path + "/" + apiToken + "/" + project.getString(0);
            urlBuilder += (isSecured ? "/" + passwordFile : "");
            urlBuilder += (cloudPath.contains(",Safe") ? "/" + passwordSafe : "");
            URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + urlBuilder);
            Log.d(LOG_TAG, url.toString());
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.setInstanceFollowRedirects(false);
            String resultString = Utils.JSON.readDataFromConnection(connection);
            if (resultString == null || resultString.startsWith("{")){
                if (responseObserver != null)
                {
                    Bundle error = new Bundle();
                    error.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, "0.0.9"));
                    responseObserver.send(Activity.RESULT_CANCELED, error);
                }
            }
            else{
                url = connection.getURL();
                DownloadManager.Request request = new DownloadManager.Request(Uri.parse(url.toURI().toString()));
                request.allowScanningByMediaScanner();
                request.setTitle(filename);
                request.setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE);
                request.setDestinationInExternalPublicDir(Environment.DIRECTORY_DOWNLOADS, filename);
                DownloadManager manager = (DownloadManager) getSystemService(Context.DOWNLOAD_SERVICE);
                manager.enqueue(request);
            }
        } catch (IOException | URISyntaxException e) {
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
            String urlBuilder = "/cloud/"+(isSecured ? "filesecured" : "file")+"/"+apiToken+"/"+project.getString(0)+"/"+path;
            urlBuilder += (isSecured ? "/" + passwordFile : "");
            urlBuilder += (passwordSafe == null ? "" : "/" + passwordSafe);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + urlBuilder);

            Log.d(LOG_TAG, "Start connection : " + url);
            JSONObject json;

            connection = (HttpURLConnection) url.openConnection();
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
                } else {
                    handleCloudPathSync(cloudPath, projectId, passwordSafe, responseObserver);
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

    private String handleCloudOpenStream(long projectId, String filename, String path, String passwordSafe, String password) throws NetworkErrorException {
        String apiToken = Utils.Account.getAuthTokenService(this, null);

        if (projectId == -1 || apiToken == null)
            throw new NetworkErrorException("Invalid local project ID");

        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null;
        try {
            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID + "=?", new String[]{String.valueOf(projectId)}, null);
            if (project == null || !project.moveToFirst())
                throw new NetworkErrorException("Invalid local project ID");
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/cloud/stream/"+apiToken+"/"+project.getString(0)+(passwordSafe == null ? "" : "/" + passwordSafe));
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();

            data.put("filename", filename);
            data.put("path", path);
            if (password != null)
                data.put("password", password);
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("POST");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException("Returned JSON is empty");
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    throw new NetworkErrorException("Api returned an error : " + json.getJSONObject("info").getString("return_code"));
                } else {
                    return json.getJSONObject("data").getString("stream_id");
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

    private void handleCloudCloseStream(long projectId, String streamId) throws NetworkErrorException {
        String apiToken = Utils.Account.getAuthTokenService(this, null);

        if (projectId == -1 || apiToken == null)
            throw new NetworkErrorException("Invalid local project ID");

        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null;
        try {
            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID + "=?", new String[]{String.valueOf(projectId)}, null);
            if (project == null || !project.moveToFirst())
                throw new NetworkErrorException("Invalid local project ID");
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/cloud/stream/"+apiToken+"/"+project.getString(0)+"/"+streamId);
            JSONObject json = new JSONObject();

            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("DELETE");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException("Returned JSON is empty");
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    throw new NetworkErrorException("Api returned an error : " + json.getJSONObject("info").getString("return_code"));
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
            throw new NetworkErrorException("Invalid local project ID");

        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null;
        try {
            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID + "=?", new String[]{String.valueOf(projectId)}, null);
            if (project == null || !project.moveToFirst())
                throw new NetworkErrorException("Invalid local project ID");
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/cloud/file");
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            String chunkData = Base64.encodeToString(chunk, Base64.DEFAULT);
            Log.d(LOG_TAG, chunkData);

            data.put("token", apiToken);
            data.put("stream_id", streamId);
            data.put("projectId", project.getString(0));
            data.put("chunk_numbers", chunkNumbers);
            data.put("current_chunk", currentChunk);
            data.put("file_chunk", chunkData);
            json.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("PUT");
            Utils.JSON.sendJsonOverConnection(connection, json);
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty()){
                throw new NetworkErrorException("Returned JSON is empty");
            } else {
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    throw new NetworkErrorException("Api returned an error : " + json.getJSONObject("info").getString("return_code"));
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
                                        .setContentText("Upload in progress");
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
                                .setContentText("Upload ended")
                                .setContentIntent(stackBuilder.getPendingIntent(0, PendingIntent.FLAG_UPDATE_CURRENT));

                    mNotifManager.notify(NOTIF_CLOUD_FILE_UPLOAD, notifbuilder.build());
                }
                file.close();
                handleCloudPathSync(cloudPath, projectId, passwordSafe, null);
            }
        } catch (IOException | NetworkErrorException e) {
            e.printStackTrace();
        } finally {
            fileData.close();
        }
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
        } catch (IOException | JSONException | ParseException e) {
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

            Log.v(LOG_TAG, returnedJson.toString());
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
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/getcomments/" + apiToken+"/"+grappboxProjectId.getString(0)+"/"+grappboxBugId.getString(0));
            Log.d(LOG_TAG, "Connect bug API : " + url.toString());
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson != null && !returnedJson.isEmpty()){
                JSONObject json = new JSONObject(returnedJson);
                Log.d(LOG_TAG, json.toString());
                if (Utils.Errors.checkAPIError(json)){
                    Log.d(LOG_TAG, "error detected : " + Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                    if (responseObserver != null)
                    {
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                }
                else {
                    JSONArray bugsData = json.getJSONObject("data").getJSONArray("array");
                    Log.d(LOG_TAG, "BugData length : " + bugsData.length());
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
                                    Log.e(LOG_TAG, "creator id not exist");
                                    continue;
                                }
                            }
                            currentValue.put(BugEntry.COLUMN_GRAPPBOX_ID, bug.getString("id"));
                            currentValue.put(BugEntry.COLUMN_LOCAL_CREATOR_ID, creatorId.getLong(0));
                            creatorId.close();
                            currentValue.put(BugEntry.COLUMN_LOCAL_PROJECT_ID, localPID);
                            currentValue.put(BugEntry.COLUMN_TITLE, bug.getString("title"));
                            currentValue.put(BugEntry.COLUMN_DESCRIPTION, bug.getString("description"));
                            currentValue.put(BugEntry.COLUMN_LOCAL_PARENT_ID, bugId);
                            String last_edited = Utils.Date.getDateFromGrappboxAPIToUTC(bug.getJSONObject(bug.isNull("editedAt") ? "createdAt" : "editedAt").getString("date"));
                            currentValue.put(BugEntry.COLUMN_DATE_LAST_EDITED_UTC, last_edited);
                            if (!bug.isNull("deletedAt")){
                                currentValue.put(BugEntry.COLUMN_DATE_DELETED_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(bug.getJSONObject("deletedAt").getString("date")));
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
        } catch (IOException e) {
            Log.e(LOG_TAG, "IOException : ", e);
        } catch (JSONException | ParseException e) {
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
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/bugtracker/"+ (isSyncingClosedBugs ? "getlastclosedtickets/" : "getlasttickets/") + apiToken+"/"+grappboxProjectId.getString(0)+"/"+String.valueOf(offset)+"/" + String.valueOf(limit));
            Log.d(LOG_TAG, "Connect bug API : " + url.toString());
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson != null && !returnedJson.isEmpty()){
                JSONObject json = new JSONObject(returnedJson);
                Log.d(LOG_TAG, json.toString());
                if (Utils.Errors.checkAPIError(json)){
                    Log.d(LOG_TAG, "error detected : " + Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                    if (responseObserver != null)
                    {
                        Bundle answer = new Bundle();
                        answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                        responseObserver.send(Activity.RESULT_CANCELED, answer);
                    }
                }
                else {
                    JSONArray bugsData = json.getJSONObject("data").getJSONArray("array");
                    Log.d(LOG_TAG, "BugData length : " + bugsData.length());

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
                                    Log.e(LOG_TAG, "creator id not exist");
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
                            String last_edited = Utils.Date.getDateFromGrappboxAPIToUTC(bug.getJSONObject(bug.isNull("editedAt") ? "createdAt" : "editedAt").getString("date"));
                            currentValue.put(BugEntry.COLUMN_DATE_LAST_EDITED_UTC, last_edited);
                            if (!bug.isNull("deletedAt")){
                                currentValue.put(BugEntry.COLUMN_DATE_DELETED_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(bug.getJSONObject("deletedAt").getString("date")));
                            }
                            Uri bugUri = getContentResolver().insert(BugEntry.CONTENT_URI, currentValue);
                            if (bugUri == null)
                                continue;
                            long bugId = Long.parseLong(bugUri.getLastPathSegment());
                            Intent syncComments = new Intent(this, GrappboxJustInTimeService.class);
                            syncComments.setAction(ACTION_SYNC_BUG_COMMENT);
                            syncComments.putExtra(EXTRA_PROJECT_ID, localPID);
                            syncComments.putExtra(EXTRA_USER_ID, localUID);
                            syncComments.putExtra(EXTRA_RESPONSE_RECEIVER, responseObserver);
                            syncComments.putExtra(EXTRA_BUG_ID, bugId);
                            startService(syncComments);
                            Log.d(LOG_TAG, "Insert tag : " + bugTag.length());
                            //Insert tags
                            ArrayList<Long> existingTags = new ArrayList<>();
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
                            Log.d(LOG_TAG, "Insert users : " +  bugUser.length());
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
                                        Log.e(LOG_TAG, "Abort inserting user");
                                        continue;
                                    }
                                }
                                currentUserAssignation.put(BugAssignationEntry.COLUMN_LOCAL_BUG_ID, bugId);
                                Log.d(LOG_TAG, "User inserted : " + userCursor.getLong(0));
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
        } catch (JSONException | ParseException e) {
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

    private void handleTimelineAddMessage(long localTimelineId, String title, String message, int parentId, ResultReceiver resultReceiver) {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        Cursor timelineGrappbox = getContentResolver().query(TimelineEntry.buildTimelineWithLocalIdUri(localTimelineId), new String[]{TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_GRAPPBOX_ID}, null, null, null);

        if (timelineGrappbox == null || !timelineGrappbox.moveToFirst())
            return;
        HttpURLConnection connection = null;
        String returnedJson;
        String apiTimelineID = timelineGrappbox.getString(0);
        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/timeline/postmessage/" + apiTimelineID);
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            data.put("token", apiToken);
            data.put("title", title);
            data.put("message", message);
            if (parentId != -1)
                data.put("commentedId", parentId);
            json.put("data", data);
            Log.d(LOG_TAG, "Connect timelineMessage API : " + url.toString());
            Log.d(LOG_TAG, "Data value : " + data.toString());
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("POST");
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
                    } else {
                        handleTimelineMessagesSync(localTimelineId, 0, 100);
                    }
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
        Log.v(LOG_TAG, "add finish");
    }

    private void handleTimelineMessageEdit(long localTimelineId, long messageId, String title, String message, ResultReceiver resultReceiver)
    {
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        Cursor timelineGrappbox = getContentResolver().query(TimelineEntry.buildTimelineWithLocalIdUri(localTimelineId), new String[]{TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_GRAPPBOX_ID}, null, null, null);

        Log.v(LOG_TAG, "edit message start");
        if (timelineGrappbox == null || !timelineGrappbox.moveToFirst())
            return;
        HttpURLConnection connection = null;
        String returnedJson;
        String apiTimelineID = timelineGrappbox.getString(0);
        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/timeline/editmessage/" + apiTimelineID);
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            data.put("token", apiToken);
            data.put("title", title);
            data.put("message", message);
            data.put("messageId", messageId);
            json.put("data", data);
            Log.d(LOG_TAG, "Connect timelineMessage API : " + url.toString());
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("PUT");
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
                    } else {
                        handleTimelineMessagesSync(localTimelineId, 0, 100);
                    }
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

    private void handleTimelineMessageDelete(long localTimelineId, long messageId, ResultReceiver resultReceiver){
        String apiToken = Utils.Account.getAuthTokenService(this, null);

        if (apiToken == null)
            return;
        Cursor timelineGrappbox = getContentResolver().query(TimelineEntry.buildTimelineWithLocalIdUri(localTimelineId), new String[]{TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_GRAPPBOX_ID}, null, null, null);
        if (timelineGrappbox == null || !timelineGrappbox.moveToFirst())
            return;
        HttpURLConnection connection = null;
        String returnedJson;

        try {
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/timeline/archivemessage/" + apiToken + timelineGrappbox.getString(timelineGrappbox.getColumnIndex(TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_GRAPPBOX_ID)) + messageId);

            Log.d(LOG_TAG, "Start connection : " + url);
            JSONObject json;

            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("DELETE");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson != null && !returnedJson.isEmpty()){
                json = new JSONObject(returnedJson);
                if (Utils.Errors.checkAPIError(json)){
                    if (resultReceiver != null){
                        Bundle ans = new Bundle();
                        String errorCode =  json.getJSONObject("info").getString("return_code");
                        ans.putInt(BUNDLE_KEY_ERROR_TYPE, Integer.valueOf(errorCode.split("\\.")[2]));
                        ans.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, errorCode));
                        resultReceiver.send(Activity.RESULT_CANCELED, ans);
                    }
                } else {
                    handleTimelineMessagesSync(localTimelineId, 0, 100);
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
    }

    private void handleTimelineMessagesSync(long localTimelineId, int offset, int limit) {
        Cursor timelineGrappbox = getContentResolver().query(TimelineEntry.buildTimelineWithLocalIdUri(localTimelineId), new String[]{TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_GRAPPBOX_ID}, null, null, null);
        String apiToken = Utils.Account.getAuthTokenService(this, null);

        Log.v(LOG_TAG, String.valueOf(localTimelineId));
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
            Log.v(LOG_TAG, "URL : " + url);
            Log.v(LOG_TAG, "JSON : " + returnedJson);
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
                Cursor creator = getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry.COLUMN_GRAPPBOX_ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{creatorId}, null);
//                Cursor creator = getContentResolver().query(UserEntry.buildUserWithGrappboxIdUri(creatorId), new String[]{UserEntry._ID}, null, null, null);
                if (creator == null || !creator.moveToFirst()){
                    handleUserDetailSync(creatorId);
                    creator = getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry.COLUMN_GRAPPBOX_ID}, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{creatorId}, null);
//                    creator = getContentResolver().query(UserEntry.buildUserWithGrappboxIdUri(creatorId), new String[]{UserEntry._ID}, null, null, null);
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

        Log.v(LOG_TAG, "Handle finish");

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
                nextMeeting.put(EventEntry.COLUMN_DATE_BEGIN_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(current.getJSONObject("begin_date").getString("date")));
                nextMeeting.put(EventEntry.COLUMN_DATE_END_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(current.getJSONObject("end_date").getString("date")));
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
                    projectValue.put(ProjectEntry.COLUMN_DATE_LOGO_LAST_EDITED_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(logoExpiration.getString("date")));
                else
                    projectValue.putNull(ProjectEntry.COLUMN_DATE_LOGO_LAST_EDITED_UTC);

                projectValue.put(ProjectEntry.COLUMN_CONTACT_EMAIL, project.getString("contact_mail"));
                projectValue.put(ProjectEntry.COLUMN_SOCIAL_FACEBOOK, project.getString("facebook"));
                projectValue.put(ProjectEntry.COLUMN_SOCIAL_TWITTER, project.getString("twitter"));
                JSONObject dateDeletion = project.isNull("deleted_at") ? null : project.getJSONObject("deleted_at");
                if (dateDeletion == null)
                    projectValue.putNull(ProjectEntry.COLUMN_DATE_DELETED_UTC);
                else
                    projectValue.put(ProjectEntry.COLUMN_DATE_DELETED_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(dateDeletion.getString("date")));
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
