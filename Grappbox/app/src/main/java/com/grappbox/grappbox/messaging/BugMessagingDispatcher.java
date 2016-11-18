/*
 * Created by Marc Wieser on 16/11/2016
 * If you have any problem or question about this work
 * please contact the author at marc.wieser@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

/*
 * Created by Marc Wieser on 10/11/2016
 * If you have any problem or question about this work
 * please contact the author at marc.wieser@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

package com.grappbox.grappbox.messaging;


import android.accounts.NetworkErrorException;
import android.content.ContentValues;
import android.content.Context;
import android.content.OperationApplicationException;
import android.database.Cursor;
import android.database.SQLException;
import android.net.Uri;

import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.data.GrappboxContract.BugEntry;
import com.grappbox.grappbox.interfaces.MessagingDispatcher;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.text.ParseException;
import java.util.HashMap;
import java.util.Map;

class BugMessagingDispatcher implements MessagingDispatcher {
    private Map<String, MessagingDispatcher> mMessagingProcessing;
    private Context mContext;

    BugMessagingDispatcher(Context context){
        mMessagingProcessing = new HashMap<>();
        mMessagingProcessing.put("new bug", new HandleNew());
        mMessagingProcessing.put("update bug", new HandleUpdate());
        mMessagingProcessing.put("close bug", new HandleClose());
        mMessagingProcessing.put("reopen bug", new HandleReopen());
        mMessagingProcessing.put("delete bug", new HandleDelete());
        mMessagingProcessing.put("participants bug", new HandleParticipant());
        mMessagingProcessing.put("new comment bug", new HandleNewComment());
        mMessagingProcessing.put("edit comment bug", new HandleEditComment());
        mMessagingProcessing.put("delete comment bug", new HandleDeleteComment());
        mMessagingProcessing.put("new tag bug", new HandleNewTag());
        mMessagingProcessing.put("update tag bug", new HandleUpdateTag());
        mMessagingProcessing.put("delete tag bug", new HandleDeleteTag());
        mMessagingProcessing.put("assign tag bug", new HandleAssignTag());
        mMessagingProcessing.put("remove tag bug", new HandleUnassignTag());
        mContext = context;
    }

    private void processTagSync(long bugId, JSONObject body) throws JSONException {
        JSONArray tags = body.getJSONArray("tags");
        String tagDeletionSelection = "";
        for (int i = 0; i < tags.length(); ++i){
            JSONObject current = tags.getJSONObject(i);
            Cursor tag = mContext.getContentResolver().query(GrappboxContract.BugtrackerTagEntry.CONTENT_URI, new String[]{GrappboxContract.BugtrackerTagEntry.TABLE_NAME + "." + GrappboxContract.BugtrackerTagEntry._ID}, GrappboxContract.BugtrackerTagEntry.TABLE_NAME + "." + GrappboxContract.BugtrackerTagEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{current.getString("id")}, null);
            if (tag == null || !tag.moveToFirst())
                continue;
            if (tagDeletionSelection.isEmpty()){
                tagDeletionSelection += GrappboxContract.BugTagEntry.TABLE_NAME + "." + GrappboxContract.BugTagEntry.COLUMN_LOCAL_TAG_ID + " NOT IN (";
            } else {
                tagDeletionSelection += ", ";
            }
            tagDeletionSelection += String.valueOf(tag.getLong(0));
            tag.close();

            Cursor assignationExist = mContext.getContentResolver().query(GrappboxContract.BugTagEntry.CONTENT_URI, new String[]{GrappboxContract.BugTagEntry._ID}, GrappboxContract.BugTagEntry.COLUMN_LOCAL_BUG_ID + "=? AND " + GrappboxContract.BugTagEntry.COLUMN_LOCAL_TAG_ID + "=?", new String[]{String.valueOf(bugId), String.valueOf(tag.getLong(0))}, null);
            if (assignationExist == null || !assignationExist.moveToFirst()){
                ContentValues values = new ContentValues();
                values.put(GrappboxContract.BugTagEntry.COLUMN_LOCAL_BUG_ID, bugId);
                values.put(GrappboxContract.BugTagEntry.COLUMN_LOCAL_TAG_ID, tag.getLong(0));
                mContext.getContentResolver().insert(GrappboxContract.BugAssignationEntry.CONTENT_URI, values);
            } else{
                assignationExist.close();
            }
        }
        if (!tagDeletionSelection.isEmpty())
            tagDeletionSelection += ") AND ";
        tagDeletionSelection += GrappboxContract.BugTagEntry.TABLE_NAME + "." + GrappboxContract.BugTagEntry.COLUMN_LOCAL_BUG_ID + "=" + String.valueOf(bugId);
        mContext.getContentResolver().delete(GrappboxContract.BugTagEntry.CONTENT_URI, tagDeletionSelection, null);
    }

    private void processUserSync(long bugId, JSONObject body) throws JSONException{
        JSONArray users = body.getJSONArray("users");
        String usersDeletionSelection = "";
        for (int i = 0; i < users.length(); ++i){
            JSONObject current = users.getJSONObject(i);
            Cursor user = mContext.getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[]{GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry._ID}, GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{current.getString("id")}, null);
            if (user == null || !user.moveToFirst())
                continue;
            if (usersDeletionSelection.isEmpty()){
                usersDeletionSelection += GrappboxContract.BugAssignationEntry.TABLE_NAME + "." + GrappboxContract.BugAssignationEntry.COLUMN_LOCAL_USER_ID + " NOT IN (";
            } else {
                usersDeletionSelection += ", ";
            }
            usersDeletionSelection += String.valueOf(user.getLong(0));
            user.close();

            Cursor assignationExist = mContext.getContentResolver().query(GrappboxContract.BugAssignationEntry.CONTENT_URI, new String[]{GrappboxContract.BugAssignationEntry._ID}, GrappboxContract.BugAssignationEntry.COLUMN_LOCAL_BUG_ID + "=? AND " + GrappboxContract.BugAssignationEntry.COLUMN_LOCAL_USER_ID + "=?", new String[]{String.valueOf(bugId), String.valueOf(user.getLong(0))}, null);
            if (assignationExist == null || !assignationExist.moveToFirst()){
                ContentValues values = new ContentValues();
                values.put(GrappboxContract.BugAssignationEntry.COLUMN_LOCAL_BUG_ID, bugId);
                values.put(GrappboxContract.BugAssignationEntry.COLUMN_LOCAL_USER_ID, user.getLong(0));
                mContext.getContentResolver().insert(GrappboxContract.BugAssignationEntry.CONTENT_URI, values);
            } else{
                assignationExist.close();
            }
        }
        if (!usersDeletionSelection.isEmpty())
            usersDeletionSelection += ") AND ";
        usersDeletionSelection += GrappboxContract.BugAssignationEntry.TABLE_NAME + "." + GrappboxContract.BugAssignationEntry.COLUMN_LOCAL_BUG_ID + "=" + String.valueOf(bugId);
        mContext.getContentResolver().delete(GrappboxContract.BugAssignationEntry.CONTENT_URI, usersDeletionSelection, null);
    }

    @Override
    public void dispatch(String action, JSONObject body) {
        mMessagingProcessing.get(action).dispatch(action, body);
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
                creator = mContext.getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[]{GrappboxContract.UserEntry._ID}, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getJSONObject("creator").getString("id")}, null);
                project = mContext.getContentResolver().query(GrappboxContract.ProjectEntry.CONTENT_URI, new String[]{GrappboxContract.ProjectEntry._ID}, GrappboxContract.ProjectEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("projectId")}, null);
                if (creator == null || !creator.moveToFirst() || project == null || !project.moveToFirst()){
                    throw new SQLException(Utils.Errors.ERROR_INVALID_ID);
                }

                values.put(BugEntry.COLUMN_GRAPPBOX_ID, body.getString("id"));
                values.put(BugEntry.COLUMN_LOCAL_CREATOR_ID, creator.getLong(0));
                values.put(BugEntry.COLUMN_LOCAL_PROJECT_ID, project.getLong(0));
                values.put(BugEntry.COLUMN_TITLE, body.getString("title"));
                values.put(BugEntry.COLUMN_DESCRIPTION, body.getString("description"));
                values.put(BugEntry.COLUMN_DATE_LAST_EDITED_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(body.getString(body.isNull("editedAt") ? "createdAt" : "editedAt")));
                values.put(BugEntry.COLUMN_IS_CLIENT_ORIGIN, body.getBoolean("clientOrigin"));
                Uri lastInsertedBug = mContext.getContentResolver().insert(BugEntry.CONTENT_URI, values);
                if (lastInsertedBug == null)
                    throw new SQLException();
                long lastBugID = Long.parseLong(lastInsertedBug.getLastPathSegment());
                if (lastBugID < 0)
                    throw new SQLException(Utils.Errors.ERROR_SQL_INSERT_FAILED);

                processTagSync(lastBugID, body);
                processUserSync(lastBugID, body);
            } catch (JSONException | SQLException | ParseException e) {
                e.printStackTrace();
            } finally {
                if (creator != null)
                    creator.close();
                if (project != null)
                    project.close();
            }
        }
    }

    private class HandleClose implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            ContentValues values = new ContentValues();

            try {
                values.put(BugEntry.COLUMN_GRAPPBOX_ID, body.getString("id"));
                values.put(BugEntry.COLUMN_DATE_DELETED_UTC, Utils.Date.nowUTC());
                mContext.getContentResolver().insert(BugEntry.CONTENT_URI, values);
            } catch (JSONException | ParseException e) {
                e.printStackTrace();
            }
        }
    }

    private class HandleReopen implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            ContentValues values = new ContentValues();

            try {
                values.put(BugEntry.COLUMN_GRAPPBOX_ID, body.getString("id"));
                values.putNull(BugEntry.COLUMN_DATE_DELETED_UTC);
                mContext.getContentResolver().insert(BugEntry.CONTENT_URI, values);
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
    }

    private class HandleDelete implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            Cursor current = null;
            try {
                current = mContext.getContentResolver().query(BugEntry.CONTENT_URI, new String[]{BugEntry._ID}, BugEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("id")}, null);
                if (current == null || !current.moveToFirst()){
                    throw new OperationApplicationException(Utils.Errors.ERROR_INVALID_ID);
                }
                mContext.getContentResolver().delete(GrappboxContract.BugTagEntry.CONTENT_URI, GrappboxContract.BugTagEntry.COLUMN_LOCAL_BUG_ID+"=?", new String[]{String.valueOf(current.getLong(0))});
                mContext.getContentResolver().delete(GrappboxContract.BugAssignationEntry.CONTENT_URI, GrappboxContract.BugAssignationEntry.COLUMN_LOCAL_BUG_ID+"=?", new String[]{String.valueOf(current.getLong(0))});
                mContext.getContentResolver().delete(BugEntry.CONTENT_URI, BugEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("id")});
            } catch (JSONException | OperationApplicationException e) {
                e.printStackTrace();
            } finally {
                if (current != null)
                    current.close();
            }
        }
    }

    private class HandleParticipant implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            Cursor bug = null;

            try {
                bug = mContext.getContentResolver().query(BugEntry.CONTENT_URI, new String[]{BugEntry._ID}, BugEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("id")}, null);
                if (bug == null || !bug.moveToFirst())
                    throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID);
                processUserSync(bug.getLong(0), body);
            } catch (JSONException | NetworkErrorException e) {
                e.printStackTrace();
            } finally {
                if (bug != null)
                    bug.close();
            }
        }
    }

    private class HandleNewComment implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            new HandleEditComment().dispatch(action, body);
        }
    }

    private class HandleEditComment implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            Cursor parent = null, creator = null;
            try {
                ContentValues values = new ContentValues();
                parent = mContext.getContentResolver().query(BugEntry.CONTENT_URI, new String[]{BugEntry._ID}, BugEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{body.getString("parentId")}, null);
                creator = mContext.getContentResolver().query(GrappboxContract.UserEntry.CONTENT_URI, new String[]{GrappboxContract.UserEntry._ID}, GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getJSONObject("creator").getString("id")}, null);
                if (parent == null || !parent.moveToFirst() || creator == null || !creator.moveToFirst())
                    throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID);
                values.put(BugEntry.COLUMN_GRAPPBOX_ID, body.getString("id"));
                values.put(BugEntry.COLUMN_TITLE, body.getString("title"));
                values.put(BugEntry.COLUMN_LOCAL_PARENT_ID, parent.getLong(0));
                values.put(BugEntry.COLUMN_DESCRIPTION, body.getString("comment"));
                values.put(BugEntry.COLUMN_DATE_LAST_EDITED_UTC, Utils.Date.getDateFromGrappboxAPIToUTC(body.getString(body.isNull("editedAt") ? "createdAt" : "editedAt")));
                values.put(BugEntry.COLUMN_LOCAL_CREATOR_ID, creator.getLong(0));
                mContext.getContentResolver().insert(BugEntry.CONTENT_URI, values);
            } catch (JSONException | NetworkErrorException | ParseException e) {
                e.printStackTrace();
            } finally {
                if (parent != null)
                    parent.close();
                if (creator != null)
                    creator.close();
            }

        }
    }

    private class HandleDeleteComment implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            Cursor parent = null;
            try {
                parent = mContext.getContentResolver().query(BugEntry.CONTENT_URI, new String[]{BugEntry._ID}, BugEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("parentId")}, null);
                if (parent == null || !parent.moveToFirst())
                    throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID);
                mContext.getContentResolver().delete(BugEntry.CONTENT_URI, BugEntry.COLUMN_GRAPPBOX_ID+"=? AND " + BugEntry.COLUMN_LOCAL_PARENT_ID+"=?", new String[]{body.getString("id"), String.valueOf(parent.getLong(0))});
            } catch (JSONException | NetworkErrorException e) {
                e.printStackTrace();
            } finally {
                if (parent != null)
                    parent.close();
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
            Cursor project = null;
            ContentValues values = new ContentValues();

            try {
                project = mContext.getContentResolver().query(GrappboxContract.ProjectEntry.CONTENT_URI, new String[]{GrappboxContract.ProjectEntry._ID}, GrappboxContract.ProjectEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{body.getString("projectId")}, null);
                if (project == null || !project.moveToFirst())
                    throw new OperationApplicationException(Utils.Errors.ERROR_INVALID_ID);
                String color = body.getString("color");
                color = color.startsWith("#") ? color : "#" + color;
                values.put(GrappboxContract.BugtrackerTagEntry.COLUMN_LOCAL_PROJECT_ID, project.getLong(0));
                values.put(GrappboxContract.BugtrackerTagEntry.COLUMN_COLOR, color);
                values.put(GrappboxContract.BugtrackerTagEntry.COLUMN_GRAPPBOX_ID, body.getString("id"));
                values.put(GrappboxContract.BugtrackerTagEntry.COLUMN_NAME, body.getString("name"));
                mContext.getContentResolver().insert(GrappboxContract.BugtrackerTagEntry.CONTENT_URI, values);
            } catch (JSONException | OperationApplicationException e) {
                e.printStackTrace();
            } finally {
                if (project != null){
                    project.close();
                }
            }
        }
    }

    private class HandleDeleteTag implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            try {
                mContext.getContentResolver().delete(GrappboxContract.BugtrackerTagEntry.CONTENT_URI, GrappboxContract.BugtrackerTagEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("id")});
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
    }

    private class HandleAssignTag implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            Cursor bug = null, tag = null;

            try {
                bug = mContext.getContentResolver().query(BugEntry.CONTENT_URI, new String[]{BugEntry._ID}, BugEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("id")}, null);
                if (bug == null || !bug.moveToFirst())
                    throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID);
                JSONArray tags = body.getJSONArray("tags");
                JSONObject current;
                Cursor assign;
                int i;
                for (i = 0; i < tags.length(); ++i){
                    current = tags.getJSONObject(i);
                    tag = mContext.getContentResolver().query(GrappboxContract.BugtrackerTagEntry.CONTENT_URI, new String[]{GrappboxContract.BugtrackerTagEntry._ID}, GrappboxContract.BugtrackerTagEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{current.getString("id")}, null);
                    if (tag == null || !tag.moveToFirst())
                        continue;
                    assign = mContext.getContentResolver().query(GrappboxContract.BugTagEntry.CONTENT_URI, new String[]{GrappboxContract.BugTagEntry._ID}, GrappboxContract.BugTagEntry.COLUMN_LOCAL_BUG_ID+"=? AND "+ GrappboxContract.BugTagEntry.COLUMN_LOCAL_TAG_ID+"=?", new String[]{String.valueOf(bug.getLong(0)), String.valueOf(tag.getLong(0))}, null);
                    if (assign == null || !assign.moveToFirst())
                        break;
                    assign.close();
                    tag.close();
                }
                if (tag != null && !tag.isClosed() && i < tags.length()){
                    ContentValues values = new ContentValues();

                    values.put(GrappboxContract.BugTagEntry.COLUMN_LOCAL_TAG_ID, tag.getLong(0));
                    values.put(GrappboxContract.BugTagEntry.COLUMN_LOCAL_BUG_ID, bug.getLong(0));
                    mContext.getContentResolver().insert(GrappboxContract.BugTagEntry.CONTENT_URI, values);
                }
            } catch (JSONException | NetworkErrorException e) {
                e.printStackTrace();
            } finally {
                if (bug != null && !bug.isClosed())
                    bug.close();
                if (tag != null && !tag.isClosed())
                    tag.close();
            }
        }
    }

    private class HandleUnassignTag implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            Cursor bug = null;

            try {
                bug = mContext.getContentResolver().query(BugEntry.CONTENT_URI, new String[]{BugEntry._ID}, BugEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("id")}, null);
                if (bug == null || !bug.moveToFirst())
                    throw new NetworkErrorException(Utils.Errors.ERROR_INVALID_ID);
                processTagSync(bug.getLong(0), body);
            } catch (JSONException | NetworkErrorException e) {
                e.printStackTrace();
            } finally {
                if (bug != null)
                    bug.close();
            }
        }
    }
}
