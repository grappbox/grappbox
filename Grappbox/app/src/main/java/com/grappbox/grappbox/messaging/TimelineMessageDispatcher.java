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

import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.data.GrappboxContract.TimelineMessageEntry;
import com.grappbox.grappbox.interfaces.MessagingDispatcher;

import org.json.JSONException;
import org.json.JSONObject;

import java.text.ParseException;
import java.util.HashMap;
import java.util.Map;


class TimelineMessageDispatcher implements MessagingDispatcher {

    private Context mContext;
    private Map<String, MessagingDispatcher> mDispatcher;

    TimelineMessageDispatcher(Context context){
        mContext = context;
        mDispatcher = new HashMap<>();
        mDispatcher.put("new message", new HandleNew());
        mDispatcher.put("update message", new HandleUpdate());
        mDispatcher.put("delete message", new HandleDelete());
        mDispatcher.put("new comment message", new HandleNewComment());
        mDispatcher.put("update comment message", new HandleUpdateComment());
        mDispatcher.put("delete comment message", new HandleDeleteComment());
    }

    @Override
    public void dispatch(String action, JSONObject body) {
        mDispatcher.get(action).dispatch(action, body);
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
            Cursor timeline = null, creator = null;

            try {
                timeline = mContext.getContentResolver().query(GrappboxContract.TimelineEntry.CONTENT_URI, new String[]{GrappboxContract.TimelineEntry._ID}, GrappboxContract.TimelineEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("timelineId")}, null);
                creator = mContext.getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[]{GrappboxContract.UserEntry._ID}, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getJSONObject("creator").getString("id")}, null);
                if (timeline == null || !timeline.moveToFirst() || creator == null || !creator.moveToFirst())
                    throw new OperationApplicationException(Utils.Errors.ERROR_INVALID_ID);
                values.put(TimelineMessageEntry.COLUMN_GRAPPBOX_ID, body.getString("id"));
                values.put(TimelineMessageEntry.COLUMN_LOCAL_CREATOR_ID, creator.getLong(0));
                values.put(TimelineMessageEntry.COLUMN_LOCAL_TIMELINE_ID, timeline.getLong(0));
                values.put(TimelineMessageEntry.COLUMN_MESSAGE, body.getString("message"));
                values.put(TimelineMessageEntry.COLUMN_TITLE, body.getString("title"));
                values.putNull(TimelineMessageEntry.COLUMN_PARENT_ID);
                String editedAt = Utils.Date.getDateFromGrappboxAPIToUTC(body.getString(body.isNull("edited_at") ? "created_at" : "edited_at"));
                values.put(TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC, editedAt);
                mContext.getContentResolver().insert(TimelineMessageEntry.CONTENT_URI, values);
            } catch (JSONException | OperationApplicationException | ParseException e) {
                e.printStackTrace();
            } finally {
                if (timeline != null)
                    timeline.close();
                if (creator != null)
                    creator.close();
            }
        }
    }

    private class HandleDelete implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            try {
                mContext.getContentResolver().delete(TimelineMessageEntry.CONTENT_URI, TimelineMessageEntry.COLUMN_GRAPPBOX_ID+"=? OR " + TimelineMessageEntry.COLUMN_PARENT_ID + "=?", new String[]{body.getString("id"), body.getString("id")});
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
    }

    private class HandleNewComment implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            new HandleUpdateComment().dispatch(action, body);
        }
    }

    private class HandleUpdateComment implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            ContentValues values = new ContentValues();
            Cursor timeline = null, creator = null, parent = null;

            try {
                timeline = mContext.getContentResolver().query(GrappboxContract.TimelineEntry.CONTENT_URI, new String[]{GrappboxContract.TimelineEntry._ID}, GrappboxContract.TimelineEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("timelineId")}, null);
                creator = mContext.getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[]{GrappboxContract.UserEntry._ID}, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getJSONObject("creator").getString("id")}, null);
                parent = mContext.getContentResolver().query(TimelineMessageEntry.CONTENT_URI, new String[]{TimelineMessageEntry._ID}, TimelineMessageEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("parentId")}, null);
                if (parent == null || timeline == null || !timeline.moveToFirst() || creator == null || !creator.moveToFirst())
                    throw new OperationApplicationException(Utils.Errors.ERROR_INVALID_ID);
                values.put(TimelineMessageEntry.COLUMN_GRAPPBOX_ID, body.getString("id"));
                values.put(TimelineMessageEntry.COLUMN_LOCAL_CREATOR_ID, creator.getLong(0));
                values.put(TimelineMessageEntry.COLUMN_LOCAL_TIMELINE_ID, timeline.getLong(0));
                values.put(TimelineMessageEntry.COLUMN_MESSAGE, body.getString("message"));
                values.putNull(TimelineMessageEntry.COLUMN_TITLE);
                values.put(TimelineMessageEntry.COLUMN_PARENT_ID, parent.getLong(0));
                String editedAt = Utils.Date.getDateFromGrappboxAPIToUTC(body.getString(body.isNull("edited_at") ? "created_at" : "edited_at"));
                values.put(TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC, editedAt);
                mContext.getContentResolver().insert(TimelineMessageEntry.CONTENT_URI, values);
            } catch (JSONException | OperationApplicationException | ParseException e) {
                e.printStackTrace();
            } finally {
                if (timeline != null)
                    timeline.close();
                if (creator != null)
                    creator.close();
                if (parent != null)
                    parent.close();
            }
        }
    }

    private class HandleDeleteComment implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            try {
                mContext.getContentResolver().delete(TimelineMessageEntry.CONTENT_URI, TimelineMessageEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("id")});
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
    }
}
