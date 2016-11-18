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
import com.grappbox.grappbox.data.GrappboxContract.ProjectEntry;
import com.grappbox.grappbox.data.GrappboxContract.RolesAssignationEntry;
import com.grappbox.grappbox.data.GrappboxContract.RolesEntry;
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;
import com.grappbox.grappbox.interfaces.MessagingDispatcher;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Map;


class RoleMessagingDispatcher implements MessagingDispatcher {
    private Context mContext;
    private Map<String, MessagingDispatcher> mDispatcher;

    RoleMessagingDispatcher(Context context){
        mContext = context;
        mDispatcher = new HashMap<>();
        mDispatcher.put("new role", new HandleNew());
        mDispatcher.put("update role", new HandleUpdate());
        mDispatcher.put("delete role", new HandleDelete());
        mDispatcher.put("assign user role", new HandleAssignUser());
        mDispatcher.put("update user role", new HandleUpdateUser());
        mDispatcher.put("delete user role", new HandleDeleteUser());
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
            Cursor project = null;

            try {
                project = mContext.getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry._ID}, ProjectEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("projectId")}, null);
                if (project == null || !project.moveToFirst())
                    throw new OperationApplicationException(Utils.Errors.ERROR_INVALID_ID);
                values.put(RolesEntry.COLUMN_GRAPPBOX_ID, body.getString("roleId"));
                values.put(RolesEntry.COLUMN_LOCAL_PROJECT_ID, project.getLong(0));
                values.put(RolesEntry.COLUMN_NAME, body.getString("name"));
                values.put(RolesEntry.COLUMN_ACCESS_TEAM_TIMELINE, body.getInt("teamTimeline"));
                values.put(RolesEntry.COLUMN_ACCESS_CUSTOMER_TIMELINE, body.getInt("customerTimeline"));
                values.put(RolesEntry.COLUMN_ACCESS_GANTT, body.getInt("gantt"));
                values.put(RolesEntry.COLUMN_ACCESS_WHITEBOARD, body.getInt("whiteboard"));
                values.put(RolesEntry.COLUMN_ACCESS_BUGTRACKER, body.getInt("bugtracker"));
                values.put(RolesEntry.COLUMN_ACCESS_EVENT, body.getInt("event"));
                values.put(RolesEntry.COLUMN_ACCESS_TASK, body.getInt("task"));
                values.put(RolesEntry.COLUMN_ACCESS_PROJECT_SETTINGS, body.getInt("projectSettings"));
                values.put(RolesEntry.COLUMN_ACCESS_CLOUD, body.getInt("cloud"));
                mContext.getContentResolver().insert(RolesEntry.CONTENT_URI, values);
            } catch (JSONException | OperationApplicationException e) {
                e.printStackTrace();
            } finally {
                if (project != null)
                    project.close();
            }
        }
    }

    private class HandleDelete implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            try {
                mContext.getContentResolver().delete(RolesEntry.CONTENT_URI, RolesEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("roleId")});
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
    }

    private class HandleAssignUser implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            Cursor role = null, user = null;

            try {
                role = mContext.getContentResolver().query(RolesEntry.CONTENT_URI, new String[]{RolesEntry._ID}, RolesEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("role_id")}, null);
                user = mContext.getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("user_id")}, null);
                if (role == null || !role.moveToFirst() || user == null || !user.moveToFirst())
                    throw new OperationApplicationException(Utils.Errors.ERROR_INVALID_ID);
                ContentValues values = new ContentValues();
                values.put(RolesAssignationEntry.COLUMN_LOCAL_USER_ID, user.getLong(0));
                values.put(RolesAssignationEntry.COLUMN_LOCAL_ROLE_ID, role.getLong(0));
                mContext.getContentResolver().insert(RolesAssignationEntry.CONTENT_URI, values);
            } catch (JSONException | OperationApplicationException e) {
                e.printStackTrace();
            } finally {
                if (role != null)
                    role.close();
                if (user != null)
                    user.close();
            }
        }
    }

    private class HandleUpdateUser implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            Cursor role = null, user = null, oldRole = null, project = null;

            try {
                role = mContext.getContentResolver().query(RolesEntry.CONTENT_URI, new String[]{RolesEntry._ID}, RolesEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("role_id")}, null);
                user = mContext.getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("user_id")}, null);
                project = mContext.getContentResolver().query(ProjectEntry.CONTENT_URI, new String[]{ProjectEntry._ID}, ProjectEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("project_id")}, null);
                if (role == null || !role.moveToFirst() || user == null || !user.moveToFirst() || project == null || !project.moveToFirst())
                    throw new OperationApplicationException(Utils.Errors.ERROR_INVALID_ID);
                oldRole = mContext.getContentResolver().query(RolesEntry.CONTENT_URI, new String[]{RolesEntry._ID}, RolesEntry.COLUMN_LOCAL_PROJECT_ID+"=?", new String[]{body.getString("role_id")}, null);
                if (oldRole != null && oldRole.moveToFirst()){
                    String deleteSelection = RolesAssignationEntry.COLUMN_LOCAL_USER_ID+"="+user.getLong(0)+" AND " + RolesAssignationEntry.COLUMN_LOCAL_ROLE_ID + " IN (";
                    do{
                        if (!oldRole.isFirst())
                            deleteSelection += ", ";
                        deleteSelection += oldRole.getLong(0);
                    }while (oldRole.moveToNext());
                    deleteSelection += ")";
                    mContext.getContentResolver().delete(RolesAssignationEntry.CONTENT_URI, deleteSelection, null);
                }
                ContentValues values = new ContentValues();
                values.put(RolesAssignationEntry.COLUMN_LOCAL_USER_ID, user.getLong(0));
                values.put(RolesAssignationEntry.COLUMN_LOCAL_ROLE_ID, role.getLong(0));
                mContext.getContentResolver().insert(RolesAssignationEntry.CONTENT_URI, values);
            } catch (JSONException | OperationApplicationException e) {
                e.printStackTrace();
            } finally {
                if (role != null)
                    role.close();
                if (user != null)
                    user.close();
                if (project != null)
                    project.close();
                if (oldRole != null)
                    oldRole.close();
            }
        }
    }

    private class HandleDeleteUser implements MessagingDispatcher{

        @Override
        public void dispatch(String action, JSONObject body) {
            Cursor role = null, user = null;

            try {
                role = mContext.getContentResolver().query(RolesEntry.CONTENT_URI, new String[]{RolesEntry._ID}, RolesEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("role_id")}, null);
                user = mContext.getContentResolver().query(UserEntry.CONTENT_URI, new String[]{UserEntry._ID}, UserEntry.COLUMN_GRAPPBOX_ID+"=?", new String[]{body.getString("user_id")}, null);
                if (role == null || !role.moveToFirst() || user == null || !user.moveToFirst())
                    throw new OperationApplicationException(Utils.Errors.ERROR_INVALID_ID);
                mContext.getContentResolver().delete(RolesAssignationEntry.CONTENT_URI, RolesAssignationEntry.COLUMN_LOCAL_ROLE_ID+"=? AND" + RolesAssignationEntry.COLUMN_LOCAL_USER_ID+"=?", new String[]{String.valueOf(role.getLong(0)), String.valueOf(user.getLong(0))});
            } catch (JSONException | OperationApplicationException e) {
                e.printStackTrace();
            } finally {
                if (role != null)
                    role.close();
                if (user != null)
                    user.close();
            }
        }
    }
}
