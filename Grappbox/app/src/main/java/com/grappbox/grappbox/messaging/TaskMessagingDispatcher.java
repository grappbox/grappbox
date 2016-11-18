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

import com.google.android.gms.tasks.Task;
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
                //TODO : Tag sync ; User sync ; dependency sync
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

        }
    }

    private class HandleDelete implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {

        }
    }

    private class HandleNewTag implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {

        }
    }

    private class HandleUpdateTag implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {

        }
    }

    private class HandleDeleteTag implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {

        }
    }
}
