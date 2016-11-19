/*
 * Created by Marc Wieser on 18/11/2016
 * If you have any problem or question about this work
 * please contact the author at marc.wieser@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

package com.grappbox.grappbox.messaging;

import android.content.ContentValues;
import android.content.Context;
import android.content.OperationApplicationException;
import android.database.Cursor;
import android.database.SQLException;
import android.net.Uri;

import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.data.GrappboxContract.ProjectEntry;
import com.grappbox.grappbox.data.GrappboxContract.TaskEntry;
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;
import com.grappbox.grappbox.interfaces.MessagingDispatcher;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.text.ParseException;
import java.util.HashMap;
import java.util.Map;


class TaskMessagingDispatcher implements MessagingDispatcher {
    private Context mContext;
    private Map<String, MessagingDispatcher> mDispatcher;

    TaskMessagingDispatcher(Context context){
        mContext = context;
        mDispatcher = new HashMap<>();
        mDispatcher.put("new task", new HandleNew());
        mDispatcher.put("update task", new HandleUpdate());
        mDispatcher.put("archive task", new HandleArchive());
        mDispatcher.put("delete task", new HandleDelete());
        mDispatcher.put("new tag task", new HandleNewTag());
        mDispatcher.put("update tag task", new HandleUpdateTag());
        mDispatcher.put("delete tag task", new HandleDeleteTag());
    }

    @Override
    public void dispatch(String action, JSONObject body) {
        mDispatcher.get(action).dispatch(action, body);
    }

    private void processTagSync(long taskId, JSONObject body) throws JSONException {
        JSONArray tags = body.getJSONArray("tags");
        String tagDeletionSelection = "";
        for (int i = 0; i < tags.length(); ++i){
            JSONObject current = tags.getJSONObject(i);
            Cursor tag = mContext.getContentResolver().query(GrappboxContract.TaskTagEntry.CONTENT_URI, new String[]{GrappboxContract.TaskTagEntry.TABLE_NAME + "." + GrappboxContract.TaskTagEntry._ID}, GrappboxContract.TaskTagEntry.TABLE_NAME + "." + GrappboxContract.TaskTagEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{current.getString("id")}, null);
            if (tag == null || !tag.moveToFirst())
                continue;
            if (tagDeletionSelection.isEmpty()){
                tagDeletionSelection += GrappboxContract.TaskTagAssignationEntry.TABLE_NAME + "." + GrappboxContract.TaskTagAssignationEntry.COLUMN_LOCAL_TAG + " NOT IN (";
            } else {
                tagDeletionSelection += ", ";
            }
            tagDeletionSelection += String.valueOf(tag.getLong(0));
            tag.close();

            Cursor assignationExist = mContext.getContentResolver().query(GrappboxContract.TaskTagAssignationEntry.CONTENT_URI, new String[]{GrappboxContract.TaskTagAssignationEntry._ID}, GrappboxContract.TaskTagAssignationEntry.COLUMN_LOCAL_TASK + "=? AND " + GrappboxContract.TaskTagAssignationEntry.COLUMN_LOCAL_TAG + "=?", new String[]{String.valueOf(taskId), String.valueOf(tag.getLong(0))}, null);
            if (assignationExist == null || !assignationExist.moveToFirst()){
                ContentValues values = new ContentValues();
                values.put(GrappboxContract.TaskTagAssignationEntry.COLUMN_LOCAL_TASK, taskId);
                values.put(GrappboxContract.TaskTagAssignationEntry.COLUMN_LOCAL_TAG, tag.getLong(0));
                mContext.getContentResolver().insert(GrappboxContract.BugAssignationEntry.CONTENT_URI, values);
            } else{
                assignationExist.close();
            }
        }
        if (!tagDeletionSelection.isEmpty())
            tagDeletionSelection += ") AND ";
        tagDeletionSelection += GrappboxContract.TaskTagAssignationEntry.TABLE_NAME + "." + GrappboxContract.TaskTagAssignationEntry.COLUMN_LOCAL_TASK + "=" + String.valueOf(taskId);
        mContext.getContentResolver().delete(GrappboxContract.TaskTagAssignationEntry.CONTENT_URI, tagDeletionSelection, null);
    }

    private void processUserSync(long taskId, JSONObject body) throws JSONException{
        JSONArray users = body.getJSONArray("users");
        String usersDeletionSelection = "";
        for (int i = 0; i < users.length(); ++i){
            JSONObject current = users.getJSONObject(i);
            Cursor user = mContext.getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[]{GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry._ID}, GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{current.getString("id")}, null);
            if (user == null || !user.moveToFirst())
                continue;
            if (usersDeletionSelection.isEmpty()){
                usersDeletionSelection += GrappboxContract.TaskAssignationEntry.TABLE_NAME + "." + GrappboxContract.TaskAssignationEntry.COLUMN_LOCAL_USER_ID + " NOT IN (";
            } else {
                usersDeletionSelection += ", ";
            }
            usersDeletionSelection += String.valueOf(user.getLong(0));
            user.close();

            Cursor assignationExist = mContext.getContentResolver().query(GrappboxContract.TaskAssignationEntry.CONTENT_URI, new String[]{GrappboxContract.TaskAssignationEntry._ID}, GrappboxContract.TaskAssignationEntry.COLUMN_LOCAL_TASK + "=? AND " + GrappboxContract.TaskAssignationEntry.COLUMN_LOCAL_USER_ID + "=?", new String[]{String.valueOf(taskId), String.valueOf(user.getLong(0))}, null);
            if (assignationExist == null || !assignationExist.moveToFirst()){
                ContentValues values = new ContentValues();
                values.put(GrappboxContract.TaskAssignationEntry.COLUMN_LOCAL_TASK, taskId);
                values.put(GrappboxContract.TaskAssignationEntry.COLUMN_LOCAL_USER_ID, user.getLong(0));
                mContext.getContentResolver().insert(GrappboxContract.TaskAssignationEntry.CONTENT_URI, values);
            } else{
                assignationExist.close();
            }
        }
        if (!usersDeletionSelection.isEmpty())
            usersDeletionSelection += ") AND ";
        usersDeletionSelection += GrappboxContract.TaskAssignationEntry.TABLE_NAME + "." + GrappboxContract.TaskAssignationEntry.COLUMN_LOCAL_TASK + "=" + String.valueOf(taskId);
        mContext.getContentResolver().delete(GrappboxContract.TaskAssignationEntry.CONTENT_URI, usersDeletionSelection, null);
    }

    private void processDependencySync(long taskId, JSONObject body) throws JSONException{
        JSONArray users = body.getJSONArray("dependencies");
        String tasksDeletionSelection = "";
        for (int i = 0; i < users.length(); ++i){
            JSONObject current = users.getJSONObject(i);
            Cursor task = mContext.getContentResolver().query(GrappboxContract.TaskEntry.CONTENT_URI, new String[]{GrappboxContract.TaskEntry.TABLE_NAME + "." + GrappboxContract.TaskEntry._ID}, GrappboxContract.TaskEntry.TABLE_NAME + "." + GrappboxContract.TaskEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{current.getJSONObject("task").getString("id")}, null);
            if (task == null || !task.moveToFirst())
                continue;
            if (tasksDeletionSelection.isEmpty()){
                tasksDeletionSelection += GrappboxContract.TaskDependenciesEntry.TABLE_NAME + "." + GrappboxContract.TaskDependenciesEntry.COLUMN_GRAPPBOX_ID + " NOT IN (";
            } else {
                tasksDeletionSelection += ", ";
            }
            tasksDeletionSelection += String.valueOf(current.getString("id"));

            ContentValues values = new ContentValues();
            values.put(GrappboxContract.TaskDependenciesEntry.COLUMN_LOCAL_TASK_FROM, taskId);
            values.put(GrappboxContract.TaskDependenciesEntry.COLUMN_LOCAL_TASK_TO, task.getLong(0));
            values.put(GrappboxContract.TaskDependenciesEntry.COLUMN_GRAPPBOX_ID, body.getString("id"));
            values.put(GrappboxContract.TaskDependenciesEntry.COLUMN_TYPE, body.getString("name"));
            mContext.getContentResolver().insert(GrappboxContract.TaskDependenciesEntry.CONTENT_URI, values);
            task.close();
        }
        if (!tasksDeletionSelection.isEmpty())
            tasksDeletionSelection += ") AND ";
        tasksDeletionSelection += GrappboxContract.TaskDependenciesEntry.TABLE_NAME + "." + GrappboxContract.TaskDependenciesEntry.COLUMN_LOCAL_TASK_FROM + "=" + String.valueOf(taskId);
        mContext.getContentResolver().delete(GrappboxContract.TaskDependenciesEntry.CONTENT_URI, tasksDeletionSelection, null);
    }

    private void processContainingSync(long taskId, JSONObject body) throws JSONException{
        JSONArray tasks = body.getJSONArray("tasks");
        Cursor currentContainingTasks = mContext.getContentResolver().query(TaskEntry.CONTENT_URI, new String[]{TaskEntry._ID, TaskEntry.COLUMN_GRAPPBOX_ID}, TaskEntry.COLUMN_PARENT_ID+"=?", new String[]{String.valueOf(taskId)}, null);
        if (currentContainingTasks == null || !currentContainingTasks.moveToFirst())
            return;
        boolean firstPass = true;
        do{
            boolean needDestroy = false;
            for (int i = 0; i < tasks.length(); ++i){
                JSONObject current = tasks.getJSONObject(i);
                if (firstPass){
                    ContentValues values = new ContentValues();
                    values.put(TaskEntry.COLUMN_GRAPPBOX_ID, current.getString("id"));
                    values.put(TaskEntry.COLUMN_PARENT_ID, taskId);
                    mContext.getContentResolver().insert(TaskEntry.CONTENT_URI, values);
                }
                if (!needDestroy && current.getString("id").equals(currentContainingTasks.getString(1))){
                    needDestroy = true;
                    if (!firstPass)
                        break;
                }
            }
            if (needDestroy){
                ContentValues values = new ContentValues();
                values.put(TaskEntry.COLUMN_GRAPPBOX_ID, currentContainingTasks.getString(1));
                values.putNull(TaskEntry.COLUMN_PARENT_ID);
                mContext.getContentResolver().insert(TaskEntry.CONTENT_URI, values);
            }
            firstPass = false;
        } while (currentContainingTasks.moveToNext());
        currentContainingTasks.close();
    }

    private class HandleNew implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            new HandleUpdate().dispatch(action, body);
        }
    }

    private class HandleUpdate implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            ContentValues values = new ContentValues();
            Cursor creator = null, project = null;

            try {
                creator = mContext.getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getJSONObject("creator").getString("id")}, null);
                project = mContext.getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry._ID}, ProjectEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("projectId")}, null);
                if (creator == null || !creator.moveToFirst() || project == null || !project.moveToFirst())
                    throw new OperationApplicationException(Utils.Errors.ERROR_INVALID_ID);
                values.put(TaskEntry.COLUMN_TITLE, body.getString("title"));
                values.put(TaskEntry.COLUMN_DESCRIPTION, body.getString("description"));
                values.put(TaskEntry.COLUMN_LOCAL_PROJECT, project.getLong(0));
                values.put(TaskEntry.COLUMN_LOCAL_CREATOR, creator.getLong(0));
                values.put(TaskEntry.COLUMN_DUE_DATE_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(body.getString("due_date")));
                values.put(TaskEntry.COLUMN_START_DATE_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(body.getString("started_at")));
                values.put(TaskEntry.COLUMN_FINISHED_DATE_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(body.getString("finished_at")));
                values.put(TaskEntry.COLUMN_CREATED_AT_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(body.getString("created_at")));
                values.put(TaskEntry.COLUMN_IS_CONTAINER, body.getBoolean("is_container"));
                values.put(TaskEntry.COLUMN_IS_MILESTONE, body.getBoolean("is_milestone"));
                values.put(TaskEntry.COLUMN_ADVANCE, body.getInt("advance"));
                values.put(TaskEntry.COLUMN_GRAPPBOX_ID, body.getString("id"));
                Uri insertedId = mContext.getContentResolver().insert(TaskEntry.CONTENT_URI, values);
                if (insertedId == null)
                    throw new SQLException(Utils.Errors.ERROR_SQL_INSERT_FAILED);
                long id = Long.parseLong(insertedId.getLastPathSegment());
                processTagSync(id, body);
                processUserSync(id, body);
                processDependencySync(id, body);
                processContainingSync(id, body);
            } catch (JSONException | OperationApplicationException | ParseException e) {
                e.printStackTrace();
            } finally {
                if (creator != null)
                    creator.close();
                if (project != null)
                    project.close();
            }
        }
    }

    private class HandleArchive implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            new HandleUpdate().dispatch(action, body);
        }
    }

    private class HandleDelete implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            Cursor task = null;
            try {
                task = mContext.getContentResolver().query(TaskEntry.CONTENT_URI, new String[]{TaskEntry._ID}, TaskEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("id")}, null);
                if (task == null || !task.moveToFirst())
                    return;
                mContext.getContentResolver().delete(TaskEntry.CONTENT_URI, TaskEntry._ID+"=?", new String[]{String.valueOf(task.getLong(0))});
            } catch (JSONException e) {
                e.printStackTrace();
            } finally {
                if (task != null)
                    task.close();
            }
        }
    }

    private class HandleNewTag implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            new HandleUpdateTag().dispatch(action, body);
        }
    }

    private class HandleUpdateTag implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            ContentValues values = new ContentValues();
            Cursor project = null;

            try {
                project = mContext.getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry._ID}, ProjectEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{body.getString("projectId")}, null);
                if (project == null || !project.moveToFirst())
                    throw new OperationApplicationException(Utils.Errors.ERROR_INVALID_ID);
                values.put(GrappboxContract.TaskTagEntry.COLUMN_GRAPPBOX_ID, body.getString("id"));
                values.put(GrappboxContract.TaskTagEntry.COLUMN_PROJECT_ID, project.getLong(0));
                String color = body.getString("color");
                color = color.startsWith("#") ? color : "#" + color;
                values.put(GrappboxContract.TaskTagEntry.COLUMN_COLOR, color);
                values.put(GrappboxContract.TaskTagEntry.COLUMN_NAME, body.getString("name"));
                mContext.getContentResolver().insert(GrappboxContract.TaskTagEntry.CONTENT_URI, values);
            } catch (JSONException | OperationApplicationException e) {
                e.printStackTrace();
            } finally {
                if (project != null)
                    project.close();
            }
        }
    }

    private class HandleDeleteTag implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            try {
                mContext.getContentResolver().delete(GrappboxContract.TaskTagEntry.CONTENT_URI, GrappboxContract.TaskTagEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("id")});
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
    }
}
