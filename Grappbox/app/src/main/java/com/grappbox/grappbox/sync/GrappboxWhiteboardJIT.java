package com.grappbox.grappbox.sync;

import android.accounts.NetworkErrorException;
import android.app.Activity;
import android.app.IntentService;
import android.content.Intent;
import android.content.OperationApplicationException;
import android.database.Cursor;
import android.os.Bundle;
import android.support.v4.os.ResultReceiver;
import android.util.Log;

import com.grappbox.grappbox.BuildConfig;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.model.WhiteboardModel;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.ArrayList;

import static com.grappbox.grappbox.data.GrappboxContract.ProjectEntry;
import static com.grappbox.grappbox.sync.GrappboxJustInTimeService.BUNDLE_KEY_ERROR_MSG;
import static com.grappbox.grappbox.sync.GrappboxJustInTimeService.EXTRA_PROJECT_ID;
import static com.grappbox.grappbox.sync.GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER;


public class GrappboxWhiteboardJIT extends IntentService {
    public static final String ACTION_GET_LIST = "com.grappbox.action.get_list";
    public static final String ACTION_OPEN = "com.grappbox.action.open";
    public static final String ACTION_CLOSE = "com.grappbox.action.close";
    public static final String ACTION_PUSH = "com.grappbox.action.push";
    public static final String ACTION_DELETE_OBJECT = "com.grappbox.action.delete_object";
    public static final String ACTION_NEW = "com.grappbox.action.new";
    public static final String ACTION_DELETE = "com.grappbox.action.delete";

    public static final String EXTRA_WHITEBOARD_ID = "com.grappbox.extra.whiteboard_id";
    public static final String EXTRA_NAME = "com.grappbox.extra.name";
    public static final String EXTRA_JSON = "com.grappbox.extra.json";

    public static final String BUNDLE_PARCELABLE_ARRAY = "com.grappbox.bundle.parcelable_array";
    public static final String BUNDLE_JSON_OBJS = "com.grappbox.bundle.json_objs";

    /**
     * Creates an IntentService.  Invoked by your subclass's constructor.
     */
    public GrappboxWhiteboardJIT() {
        super("GrappboxWhiteboardJIT");
    }

    /**
     * Dispatch all requests to the right function
     * @param intent The request to dispatch
     */
    @Override
    protected void onHandleIntent(Intent intent) {
        Log.e("Test", "onHandleIntent");
        switch (intent.getAction()){
            case ACTION_GET_LIST:
                processGetList(intent.getLongExtra(EXTRA_PROJECT_ID, -1), (ResultReceiver) intent.getParcelableExtra(EXTRA_RESPONSE_RECEIVER));
                break;
            case ACTION_OPEN:
                processOpen(intent.getStringExtra(EXTRA_WHITEBOARD_ID), (ResultReceiver) intent.getParcelableExtra(EXTRA_RESPONSE_RECEIVER));
                break;
            case ACTION_CLOSE:
                processClose(intent.getStringExtra(EXTRA_WHITEBOARD_ID));
                break;
            case ACTION_DELETE_OBJECT:
                try {
                    processDeleteObject(intent.getStringExtra(EXTRA_WHITEBOARD_ID), new JSONObject(intent.getStringExtra(EXTRA_JSON)));
                } catch (JSONException e) {
                    e.printStackTrace();
                }
                break;
            case ACTION_PUSH:
                try {
                    processPush(intent.getStringExtra(EXTRA_WHITEBOARD_ID), new JSONObject(intent.getStringExtra(EXTRA_JSON)));
                } catch (JSONException e) {
                    e.printStackTrace();
                }
                break;
            case ACTION_NEW:
                processNew(intent.getLongExtra(EXTRA_PROJECT_ID, -1), intent.getStringExtra(EXTRA_NAME));
                break;
            case ACTION_DELETE:
                processDelete(intent.getStringExtra(EXTRA_WHITEBOARD_ID));
                break;
            default:
                throw new UnsupportedOperationException("Invalid action");
        }
    }

    /**
     * Fetch the whiteboard list from GrappBox API and send it to responseObserver
     * @param projectId The local DB project ID currently selected
     * @param responseObserver If set, it will receive the API processed answer
     */
    private void processGetList(long projectId, ResultReceiver responseObserver){
        if (responseObserver == null)
            return;
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null;

        try{
            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID+"=?", new String[]{String.valueOf(projectId)}, null);
            if (project == null || !project.moveToFirst())
                throw new OperationApplicationException(Utils.Errors.ERROR_INVALID_ID);
            URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/whiteboards/"+project.getString(0));
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json)){
                Bundle answer = new Bundle();
                answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                responseObserver.send(Activity.RESULT_CANCELED, answer);
                throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
            }
            JSONArray data = json.getJSONObject("data").getJSONArray("array");
            ArrayList<WhiteboardModel> models = new ArrayList<>();

            for (int i = 0; i < data.length(); ++i){
                JSONObject current = data.getJSONObject(i);
                Cursor user = getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, null, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{current.getJSONObject("user").getString("id")}, null);
                if (user != null && user.moveToFirst()){
                    models.add(new WhiteboardModel(current, user));
                    user.close();
                }
            }
            Bundle answer = new Bundle();
            answer.putParcelableArrayList(BUNDLE_PARCELABLE_ARRAY, models);
            responseObserver.send(Activity.RESULT_OK, answer);
        } catch (OperationApplicationException | IOException | JSONException | NetworkErrorException e) {
            e.printStackTrace();
        } finally {
            if (project != null && !project.isClosed())
                project.close();
            if (connection != null)
                connection.disconnect();
        }


    }

    private void processOpen(String whiteboardId, ResultReceiver responseObserver){
        if (responseObserver == null)
            return;
        HttpURLConnection connection = null;
        String apiToken = Utils.Account.getAuthTokenService(this, null);

        try{
            URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/whiteboard/"+whiteboardId);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            String returnedJson = Utils.JSON.readDataFromConnection(connection);
            JSONObject json = new JSONObject(returnedJson);
            if (Utils.Errors.checkAPIError(json)){
                Bundle answer = new Bundle();
                answer.putString(BUNDLE_KEY_ERROR_MSG, Utils.Errors.getClientMessageFromErrorCode(this, json.getJSONObject("info").getString("return_code")));
                responseObserver.send(Activity.RESULT_CANCELED, answer);
                throw new NetworkErrorException(Utils.Errors.ERROR_API_GENERIC);
            }
            JSONArray content = json.getJSONObject("data").getJSONArray("content");
            JSONArray objects = new JSONArray();
            for (int i = 0; i < content.length(); ++i){
                JSONObject current = content.getJSONObject(i);
                JSONObject object = current.getJSONObject("object");
                object.put("id", current.getString("id"));
                objects.put(object);
            }
            Bundle answer = new Bundle();
            answer.putString(BUNDLE_JSON_OBJS, objects.toString());
            responseObserver.send(Activity.RESULT_OK, answer);
        } catch (IOException | JSONException | NetworkErrorException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }

    private void processClose(String whiteboardId){
        HttpURLConnection connection = null;
        String apiToken = Utils.Account.getAuthTokenService(this, null);

        try{
            URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/whiteboard/"+whiteboardId);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("PUT");
            connection.connect();
            String returnedJson = Utils.JSON.readDataFromConnection(connection);
        } catch (IOException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }

    private void processPush(String whiteboardId, JSONObject json){
        HttpURLConnection connection = null;
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        JSONObject data = new JSONObject();
        JSONObject send = new JSONObject();
        try{
            URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/whiteboard/draw/"+whiteboardId);
            data.put("object", json);
            send.put("data", data);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("PUT");
            Utils.JSON.sendJsonOverConnection(connection, send);
            connection.connect();
            String returnedJson = Utils.JSON.readDataFromConnection(connection);
        } catch (IOException | JSONException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }

    private void processDeleteObject(String whiteboardId, JSONObject json){
        HttpURLConnection connection = null;
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        JSONObject send = new JSONObject();
        try{
            URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/whiteboard/object/"+whiteboardId);
            send.put("data", json);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("DELETE");
            Utils.JSON.sendJsonOverConnection(connection, send);
            connection.connect();
            String returnedJson = Utils.JSON.readDataFromConnection(connection);
        } catch (IOException | JSONException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }

    private void processNew(long projectId, String name){

        HttpURLConnection connection = null;
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        JSONObject send = new JSONObject();
        JSONObject json = new JSONObject();
        Cursor project = null;
        try{
            project = getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry.COLUMN_GRAPPBOX_ID}, ProjectEntry._ID+"=?", new String[]{String.valueOf(projectId)}, null);
            if (project == null || !project.moveToFirst())
                throw new OperationApplicationException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/whiteboard");
            json.put("projectId", project.getString(0));
            json.put("whiteboardName", name);
            send.put("data", json);
            Log.e("TEST", "processNew");
            Log.e("TEST", send.toString());
            Log.e("TEST", apiToken);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("POST");
            connection.setRequestProperty("Authorization", apiToken);
            Utils.JSON.sendJsonOverConnection(connection, send);
            connection.connect();
            String returnedJson = Utils.JSON.readDataFromConnection(connection);
        } catch (IOException | JSONException | OperationApplicationException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
            if (project != null && !project.isClosed())
                project.close();
        }
    }

    private void processDelete(String whiteboardId){
        HttpURLConnection connection = null;
        String apiToken = Utils.Account.getAuthTokenService(this, null);
        try{
            URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/whiteboard/"+whiteboardId);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("DELETE");
            connection.connect();
            String returnedJson = Utils.JSON.readDataFromConnection(connection);
        } catch (IOException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }
}
