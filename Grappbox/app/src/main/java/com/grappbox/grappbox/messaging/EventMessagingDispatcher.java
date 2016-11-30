/*
 * Created by Marc Wieser the 16/11/2016
 * If you have any problem or question about this work
 * please contact the author at marc.wieser@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

/*
 * Created by Marc Wieser the 11/11/2016
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
import android.net.Uri;

import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.data.GrappboxContract.EventEntry;
import com.grappbox.grappbox.interfaces.MessagingDispatcher;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.text.ParseException;
import java.util.HashMap;
import java.util.Map;

class EventMessagingDispatcher implements MessagingDispatcher {

    private Map<String, MessagingDispatcher> mDispatcher;
    private Context mContext;

    EventMessagingDispatcher(Context context){
        mDispatcher = new HashMap<>();
        mDispatcher.put("new event", new HandleNew());
        mDispatcher.put("update event", new HandleUpdate());
        mDispatcher.put("delete event", new HandleDelete());
        mDispatcher.put("participants event", new HandleParticipants());
        mContext = context;
    }

    @Override
    public void dispatch(String action, JSONObject body) {
        mDispatcher.get(action).dispatch(action, body);
    }

    private void processUserSync(long eventId, JSONObject body) throws JSONException{
        JSONArray users = body.getJSONArray("users");
        String usersDeletionSelection = "";
        for (int i = 0; i < users.length(); ++i){
            JSONObject current = users.getJSONObject(i);
            Cursor user = mContext.getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[]{GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry._ID}, GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{current.getString("id")}, null);
            if (user == null || !user.moveToFirst())
                continue;
            if (usersDeletionSelection.isEmpty()){
                usersDeletionSelection += GrappboxContract.EventParticipantEntry.TABLE_NAME + "." + GrappboxContract.EventParticipantEntry.COLUMN_LOCAL_USER_ID + " NOT IN (";
            } else {
                usersDeletionSelection += ", ";
            }
            usersDeletionSelection += String.valueOf(user.getLong(0));
            user.close();

            Cursor assignationExist = mContext.getContentResolver().query(GrappboxContract.EventParticipantEntry.CONTENT_URI, new String[]{GrappboxContract.EventParticipantEntry._ID}, GrappboxContract.EventParticipantEntry.COLUMN_LOCAL_EVENT_ID + "=? AND " + GrappboxContract.EventParticipantEntry.COLUMN_LOCAL_USER_ID + "=?", new String[]{String.valueOf(eventId), String.valueOf(user.getLong(0))}, null);
            if (assignationExist == null || !assignationExist.moveToFirst()){
                ContentValues values = new ContentValues();
                values.put(GrappboxContract.EventParticipantEntry.COLUMN_LOCAL_EVENT_ID, eventId);
                values.put(GrappboxContract.EventParticipantEntry.COLUMN_LOCAL_USER_ID, user.getLong(0));
                mContext.getContentResolver().insert(GrappboxContract.BugAssignationEntry.CONTENT_URI, values);
            } else{
                assignationExist.close();
            }
        }
        if (!usersDeletionSelection.isEmpty())
            usersDeletionSelection += ") AND ";
        usersDeletionSelection += GrappboxContract.EventParticipantEntry.TABLE_NAME + "." + GrappboxContract.EventParticipantEntry.COLUMN_LOCAL_EVENT_ID + "=" + String.valueOf(eventId);
        mContext.getContentResolver().delete(GrappboxContract.BugAssignationEntry.CONTENT_URI, usersDeletionSelection, null);
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
            Cursor project = null, creator = null;

            try {
                creator = mContext.getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[]{GrappboxContract.UserEntry._ID}, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getJSONObject("creator").getString("id")}, null);
                project = mContext.getContentResolver().query(GrappboxContract.ProjectEntry.CONTENT_URI, new String[]{GrappboxContract.ProjectEntry._ID}, GrappboxContract.ProjectEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("projectId")}, null);
                if (creator == null || !creator.moveToFirst() || project == null || !project.moveToFirst())
                    throw new OperationApplicationException(Utils.Errors.ERROR_INVALID_ID);
                values.put(EventEntry.COLUMN_LOCAL_PROJECT_ID, project.getLong(0));
                values.put(EventEntry.COLUMN_LOCAL_CREATOR_ID, creator.getLong(0));
                values.put(EventEntry.COLUMN_DATE_BEGIN_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(body.getString("beginDate")));
                values.put(EventEntry.COLUMN_DATE_END_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(body.getString("endDate")));
                values.put(EventEntry.COLUMN_EVENT_DESCRIPTION, body.getString("description"));
                values.put(EventEntry.COLUMN_EVENT_TITLE, body.getString("title"));
                values.put(EventEntry.COLUMN_GRAPPBOX_ID, body.getString("id"));
                Uri lastIDUri = mContext.getContentResolver().insert(EventEntry.CONTENT_URI, values);
                if (lastIDUri != null){
                    long lastID = Long.parseLong(lastIDUri.getLastPathSegment());
                    processUserSync(lastID, body);
                }
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

    private class HandleDelete implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            Cursor current = null;
            try {
                current = mContext.getContentResolver().query(EventEntry.CONTENT_URI, new String[]{EventEntry._ID}, EventEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("id")}, null);
                if (current == null || !current.moveToFirst()){
                    throw new OperationApplicationException(Utils.Errors.ERROR_INVALID_ID);
                }
                mContext.getContentResolver().delete(GrappboxContract.EventParticipantEntry.CONTENT_URI, GrappboxContract.EventParticipantEntry.COLUMN_LOCAL_EVENT_ID+"=?", new String[]{String.valueOf(current.getLong(0))});
                mContext.getContentResolver().delete(EventEntry.CONTENT_URI, EventEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("id")});
            } catch (JSONException | OperationApplicationException e) {
                e.printStackTrace();
            } finally {
                if (current != null)
                    current.close();
            }
        }
    }

    private class HandleParticipants implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            Cursor current = null;
            try {
                current = mContext.getContentResolver().query(EventEntry.CONTENT_URI, new String[]{EventEntry._ID}, EventEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("id")}, null);
                if (current == null || !current.moveToFirst()){
                    throw new OperationApplicationException(Utils.Errors.ERROR_INVALID_ID);
                }
                processUserSync(current.getLong(0), body);
            } catch (JSONException | OperationApplicationException e) {
                e.printStackTrace();
            } finally {
                if (current != null)
                    current.close();
            }
        }
    }
}
