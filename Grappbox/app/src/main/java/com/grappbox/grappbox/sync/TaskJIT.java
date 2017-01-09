package com.grappbox.grappbox.sync;

import android.accounts.NetworkErrorException;
import android.app.IntentService;
import android.content.ContentValues;
import android.content.Intent;
import android.content.OperationApplicationException;
import android.database.Cursor;
import android.net.Uri;
import android.util.Log;

import com.grappbox.grappbox.BuildConfig;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.data.GrappboxContract.TaskAssignationEntry;
import com.grappbox.grappbox.data.GrappboxContract.TaskDependenciesEntry;
import com.grappbox.grappbox.data.GrappboxContract.TaskEntry;
import com.grappbox.grappbox.data.GrappboxContract.TaskTagAssignationEntry;
import com.grappbox.grappbox.data.GrappboxContract.TaskTagEntry;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.ProtocolException;
import java.net.URL;
import java.text.ParseException;


/**
 * An {@link IntentService} subclass for handling asynchronous task requests in
 * a service on a separate handler thread.
 * <p>
 */
public class TaskJIT extends IntentService {

    private static final String ACTION_SYNC = "com.grappbox.action.sync";

    public TaskJIT() {
        super("TaskJIT");
    }

    @Override
    protected void onHandleIntent(Intent intent) {
        if (intent == null)
            return;
        switch (intent.getAction()){
            case ACTION_SYNC:
                processSync(intent.getLongExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, -1));
                break;
            default:
                throw new UnsupportedOperationException();
        }
    }

    private long handleTaskBase(JSONObject task, long parentId) throws JSONException, ParseException {
        ContentValues updateChild = new ContentValues();
        updateChild.put(TaskEntry.COLUMN_GRAPPBOX_ID, task.getString("id"));
        updateChild.put(TaskEntry.COLUMN_TITLE, task.getString("title"));
        updateChild.put(TaskEntry.COLUMN_START_DATE_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(task.getString("started_at")));
        updateChild.put(TaskEntry.COLUMN_DUE_DATE_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(task.getString("due_date")));
        if (parentId >= 0)
            updateChild.put(TaskEntry.COLUMN_PARENT_ID, parentId);
        Uri insert = getContentResolver().insert(TaskEntry.CONTENT_URI, updateChild);
        if (insert == null)
            return -1;
        return Long.parseLong(insert.getLastPathSegment());
    }

    private void processSync(long projectId){
        HttpURLConnection connection = null;
        String returnedJson;
        Cursor project = null;
        String apiToken = Utils.Account.getAuthTokenService(this, null);

        try{
            project = getContentResolver().query(GrappboxContract.ProjectEntry.CONTENT_URI, new String[]{GrappboxContract.ProjectEntry.COLUMN_GRAPPBOX_ID}, GrappboxContract.ProjectEntry._ID + "=?", new String[]{String.valueOf(projectId)}, null);
            if (project == null || !project.moveToNext())
                throw new OperationApplicationException(Utils.Errors.ERROR_INVALID_ID);
            final URL url = new URL(BuildConfig.GRAPPBOX_API_URL + BuildConfig.GRAPPBOX_API_VERSION + "/tasks/project/" + project.getLong(0));
            connection = (HttpURLConnection)url.openConnection();
            connection.setRequestProperty("Authorization", apiToken);
            connection.setRequestMethod("GET");
            connection.connect();
            Log.v("TESTJIT", "url : " + url.toString() + ", apiToken : " + apiToken);
            returnedJson = Utils.JSON.readDataFromConnection(connection);
            if (returnedJson == null || returnedJson.isEmpty())
                throw new NetworkErrorException(Utils.Errors.ERROR_API_ANSWER_EMPTY);
            Log.v("TESTJIT", "returnedJSON : " + returnedJson);
            JSONArray tasks = (new JSONObject(returnedJson)).getJSONArray("array");
            for (int i = 0; i < tasks.length(); ++i){
                JSONObject current = tasks.getJSONObject(i);

                ContentValues newTask = new ContentValues();
                newTask.put(TaskEntry.COLUMN_LOCAL_PROJECT, projectId);
                newTask.put(TaskEntry.COLUMN_GRAPPBOX_ID, current.getString("id"));
                newTask.put(TaskEntry.COLUMN_ADVANCE, current.getInt("advance"));
                newTask.put(TaskEntry.COLUMN_TITLE, current.getString("title"));
                newTask.put(TaskEntry.COLUMN_DESCRIPTION, current.getString("description"));
                newTask.put(TaskEntry.COLUMN_DUE_DATE_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(current.getString("due_date")));
                newTask.put(TaskEntry.COLUMN_START_DATE_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(current.getString("started_date")));
                newTask.put(TaskEntry.COLUMN_FINISHED_DATE_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(current.getString("finished_at")));
                newTask.put(TaskEntry.COLUMN_CREATED_AT_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(current.getString("created_at")));
                newTask.put(TaskEntry.COLUMN_IS_MILESTONE, current.getBoolean("is_milestone"));
                newTask.put(TaskEntry.COLUMN_IS_CONTAINER, current.getBoolean("is_container"));

                //Creator check
                Cursor creator = getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[]{
                        GrappboxContract.UserEntry._ID
                }, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{
                        current.getJSONObject("creator").getString("id")
                }, null);

                if (creator == null || !creator.moveToFirst()){
                    GrappboxJustInTimeService.handleUserDetailSync(this, current.getJSONObject("creator").getString("id"));
                    creator = getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[]{
                            GrappboxContract.UserEntry._ID
                    }, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{
                            current.getJSONObject("creator").getString("id")
                    }, null);
                    if (creator == null || !creator.moveToFirst()){
                        continue;
                    }
                }
                newTask.put(TaskEntry.COLUMN_LOCAL_CREATOR, creator.getLong(0));
                creator.close();

                Uri uri = getContentResolver().insert(TaskEntry.CONTENT_URI, newTask);
                if (uri == null)
                    continue;
                long taskLocalId = Long.parseLong(uri.getLastPathSegment());
                if (taskLocalId < 0)
                    continue;

                //Containing tasks
                JSONArray containingTasks = current.getJSONArray("tasks");
                for (int j = 0; j < containingTasks.length(); ++j){
                    JSONObject curContaining = containingTasks.getJSONObject(j);

                    handleTaskBase(curContaining, taskLocalId);
                }

                //Dependencies
                JSONArray dependencies = current.getJSONArray("dependencies");
                for (int j = 0; j < dependencies.length(); ++j){
                    JSONObject curDep = dependencies.getJSONObject(j);
                    long taskId = handleTaskBase(curDep.getJSONObject("task"), -1);
                    ContentValues values = new ContentValues();

                    values.put(TaskDependenciesEntry.COLUMN_GRAPPBOX_ID, curDep.getString("id"));
                    values.put(TaskDependenciesEntry.COLUMN_TYPE, curDep.getString("name"));
                    values.put(TaskDependenciesEntry.COLUMN_LOCAL_TASK_FROM, taskLocalId);
                    values.put(TaskDependenciesEntry.COLUMN_LOCAL_TASK_TO, taskId);
                    getContentResolver().insert(TaskDependenciesEntry.CONTENT_URI, values);
                }

                //Assignations
                JSONArray users = current.getJSONArray("users");
                for (int j = 0; j < users.length(); ++j){
                    JSONObject user = users.getJSONObject(j);

                    Cursor curUser = getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[]{
                            GrappboxContract.UserEntry._ID
                    }, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{
                            user.getString("id")
                    }, null);

                    if (curUser == null || !curUser.moveToFirst()){
                        GrappboxJustInTimeService.handleUserDetailSync(this, current.getJSONObject("creator").getString("id"));
                        curUser = getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[]{
                                GrappboxContract.UserEntry._ID
                        }, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{
                                user.getString("id")
                        }, null);
                        if (curUser == null || !curUser.moveToFirst()){
                            continue;
                        }
                    }
                    Cursor assign = getContentResolver().query(TaskAssignationEntry.CONTENT_URI, new String[]{TaskAssignationEntry._ID}, TaskAssignationEntry.COLUMN_LOCAL_TASK+"=? AND " + TaskAssignationEntry.COLUMN_LOCAL_USER_ID + "=?", new String[]{String.valueOf(taskLocalId), String.valueOf(curUser.getLong(0))}, null);
                    ContentValues values = new ContentValues();
                    values.put(TaskAssignationEntry.COLUMN_LOCAL_TASK, taskLocalId);
                    values.put(TaskAssignationEntry.COLUMN_LOCAL_USER_ID, curUser.getLong(0));
                    values.put(TaskAssignationEntry.COLUMN_PERCENTAGE, user.getInt("percent"));
                    if (assign == null || !assign.moveToFirst()){
                        getContentResolver().insert(TaskAssignationEntry.CONTENT_URI, values);
                    } else {
                        getContentResolver().update(TaskAssignationEntry.CONTENT_URI, values, TaskAssignationEntry.COLUMN_LOCAL_TASK+"=? AND " + TaskAssignationEntry.COLUMN_LOCAL_USER_ID + "=?", new String[]{String.valueOf(taskLocalId), String.valueOf(curUser.getLong(0))});
                        assign.close();
                    }
                    curUser.close();
                }

                //tags
                JSONArray tags = current.getJSONArray("tags");
                for (int j = 0; j < tags.length(); ++j){
                    JSONObject curtag = tags.getJSONObject(j);
                    ContentValues values = new ContentValues();

                    values.put(TaskTagEntry.COLUMN_PROJECT_ID, projectId);
                    values.put(TaskTagEntry.COLUMN_GRAPPBOX_ID, curtag.getString("id"));
                    values.put(TaskTagEntry.COLUMN_COLOR, curtag.getString("color"));
                    values.put(TaskTagEntry.COLUMN_NAME, curtag.getString("name"));
                    Uri inserted = getContentResolver().insert(TaskTagEntry.CONTENT_URI, values);
                    long insId = Long.parseLong(inserted.getLastPathSegment());

                    Cursor assign = getContentResolver().query(TaskTagAssignationEntry.CONTENT_URI, new String[]{TaskTagAssignationEntry._ID}, TaskTagAssignationEntry.COLUMN_LOCAL_TASK+"=? AND " + TaskTagAssignationEntry.COLUMN_LOCAL_TAG+"=?", new String[]{String.valueOf(taskLocalId), String.valueOf(insId)}, null);
                    ContentValues assignContent = new ContentValues();
                    assignContent.put(TaskTagAssignationEntry.COLUMN_LOCAL_TASK, taskLocalId);
                    assignContent.put(TaskTagAssignationEntry.COLUMN_LOCAL_TAG, insId);
                    if (assign == null || !assign.moveToFirst()){
                        getContentResolver().insert(TaskTagAssignationEntry.CONTENT_URI, assignContent);
                    } else {
                        getContentResolver().update(TaskTagAssignationEntry.CONTENT_URI, assignContent, TaskTagAssignationEntry._ID+"=?", new String[]{String.valueOf(assign.getLong(0))});
                    }
                }
            }
        } catch (OperationApplicationException | IOException | NetworkErrorException | JSONException | ParseException e) {
            e.printStackTrace();
        } finally {
            if (project != null && !project.isClosed())
                project.close();
            if (connection != null)
                connection.disconnect();
        }
    }


}
