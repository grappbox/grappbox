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

    public static final String EXTRA_WHITEBOARD_ID = "com.grappbox.extra.whiteboard_id";

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
                objects.put(current.getJSONObject("object"));
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
        } catch (IOException e) {
            e.printStackTrace();
        } finally {
            if (connection != null)
                connection.disconnect();
        }
    }
}
